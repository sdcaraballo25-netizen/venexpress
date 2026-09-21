<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RateMatrix;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Puente entre un Pedido del marketplace y una guía de envío real.
 *
 * No hay recolección a domicilio: el Emprendedor ya entregó la
 * mercancía en su agencia aliada de retiro (Emprendedor::pickupAlly),
 * así que confirmar un pedido es, en la práctica, registrar una guía
 * normal con esa agencia como origen y el cliente como destino —
 * reutiliza PackageService::createPackage() tal cual, en vez de
 * duplicar el cálculo de tarifa/comisión/historial.
 */
class PedidoService
{
    public function __construct(
        protected PackageService $packageService,
    ) {
    }

    public function confirmarPedido(Pedido $pedido, ?int $registeredByUserId = null): Package
    {
        return DB::transaction(function () use ($pedido, $registeredByUserId) {
            $locked = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Pedido::STATUS_PENDIENTE) {
                throw new RuntimeException('Este pedido ya fue procesado.');
            }

            $producto = Producto::whereKey($locked->producto_id)->lockForUpdate()->firstOrFail();

            if ($producto->stock < $locked->cantidad) {
                throw new RuntimeException('No hay stock suficiente para este pedido.');
            }

            $emprendedor = $locked->emprendedor;
            $pickupAlly = $emprendedor->pickupAlly;

            if (! $pickupAlly) {
                throw new RuntimeException(
                    'El emprendedor no tiene una agencia aliada de retiro configurada.'
                );
            }

            // Mismo porcentaje para todos los emprendedores, decidido
            // por el admin junto con el resto de la tarifa vigente.
            $discountPercentage = (float) (
                RateMatrix::current()?->emprendedor_discount_percentage ?? 0.0
            );

            $package = $this->packageService->createPackage([
                'ally_id' => $pickupAlly->id,

                'sender_name' => $emprendedor->business_name,
                'sender_id_doc' => $emprendedor->document_id,
                'sender_phone' => $emprendedor->user?->phone ?? '',

                'recipient_name' => $locked->cliente_nombre,
                'recipient_id_doc' => $locked->cliente_id_doc,
                'recipient_phone' => $locked->cliente_telefono,

                'origin_city' => $pickupAlly->city,
                'origin_state' => $pickupAlly->state,
                'destination_city' => $locked->destino_ciudad,
                'destination_state' => $locked->destino_estado,

                'package_type' => Package::TYPE_PAQUETE,
                'physical_weight_kg' => (float) $producto->peso_kg * $locked->cantidad,

                'requires_delivery' => true,
                'discount_percentage' => $discountPercentage,
            ], $registeredByUserId);

            $producto->decrement('stock', $locked->cantidad);

            $locked->update([
                'package_id' => $package->id,
                'status' => Pedido::STATUS_CONFIRMADO,
            ]);

            return $package;
        });
    }
}
