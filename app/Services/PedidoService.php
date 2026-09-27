<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RateMatrix;
use App\Notifications\PedidoEstadoActualizado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

/**
 * Puente entre un Pedido del marketplace y una guía de envío real.
 *
 * No hay recolección a domicilio: el Emprendedor lleva la mercancía a
 * su agencia aliada de retiro (Emprendedor::pickupAlly). El flujo
 * queda en dos pasos, no uno:
 *
 *   1. marcarComoPagado(): el emprendedor confirma que el cliente ya
 *      le pagó (fuera de la plataforma). Reserva el stock, pero NO
 *      genera guía todavía — el peso que el emprendedor declaró en su
 *      catálogo (Producto::peso_kg) es una estimación para mostrar en
 *      la tienda, no algo verificado físicamente.
 *   2. registrarGuia(): taquilla, cuando el emprendedor lleva el
 *      paquete a la agencia, pesa/mide de verdad y genera la guía real
 *      (Ally\EmprendedorPedidos) — recién ahí se llama a
 *      PackageService::createPackage(), reutilizando el mismo cálculo
 *      de tarifa/comisión/historial que cualquier otra guía.
 */
class PedidoService
{
    public function __construct(
        protected PackageService $packageService,
    ) {}

    public function marcarComoPagado(Pedido $pedido): Pedido
    {
        $locked = DB::transaction(function () use ($pedido) {
            $locked = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Pedido::STATUS_PENDIENTE) {
                throw new RuntimeException('Este pedido ya fue procesado.');
            }

            $items = $locked->items()->with('producto')->get();

            foreach ($items as $item) {
                $producto = Producto::whereKey($item->producto_id)->lockForUpdate()->firstOrFail();

                if ($producto->stock < $item->cantidad) {
                    throw new RuntimeException("No hay stock suficiente de \"{$producto->nombre}\" para este pedido.");
                }
            }

            if (! $locked->emprendedor->pickupAlly) {
                throw new RuntimeException(
                    'El emprendedor no tiene una agencia aliada de retiro configurada.'
                );
            }

            foreach ($items as $item) {
                Producto::whereKey($item->producto_id)->decrement('stock', $item->cantidad);
            }

            $locked->update(['status' => Pedido::STATUS_PAGADO]);

            return $locked;
        });

        $this->notificarCliente($locked, Pedido::STATUS_PAGADO);

        return $locked;
    }

    /**
     * @param array{
     *     physical_weight_kg: float, length_cm?: ?float, width_cm?: ?float,
     *     height_cm?: ?float, is_fragile?: bool, has_insurance?: bool,
     *     declared_value_usd?: ?float,
     * } $datosPaquete
     */
    public function registrarGuia(Pedido $pedido, array $datosPaquete, ?int $registeredByUserId, Ally $ally): Package
    {
        [$package, $locked] = DB::transaction(function () use ($pedido, $datosPaquete, $registeredByUserId, $ally) {
            $locked = Pedido::whereKey($pedido->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Pedido::STATUS_PAGADO) {
                throw new RuntimeException('Este pedido no está listo para generar guía (falta confirmar el pago, o ya tiene una guía).');
            }

            $emprendedor = $locked->emprendedor;
            $pickupAlly = $emprendedor->pickupAlly;

            if (! $pickupAlly || $pickupAlly->id !== $ally->id) {
                throw new RuntimeException('Este pedido no pertenece a tu agencia.');
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
                'physical_weight_kg' => (float) $datosPaquete['physical_weight_kg'],
                'length_cm' => $datosPaquete['length_cm'] ?? null,
                'width_cm' => $datosPaquete['width_cm'] ?? null,
                'height_cm' => $datosPaquete['height_cm'] ?? null,
                'is_fragile' => $datosPaquete['is_fragile'] ?? false,
                'has_insurance' => $datosPaquete['has_insurance'] ?? false,
                'declared_value_usd' => $datosPaquete['declared_value_usd'] ?? null,

                'requires_delivery' => true,
                'delivery_address' => $locked->direccion_entrega,
                'delivery_sector' => $locked->destino_ciudad,
                'delivery_reference' => $locked->referencia_entrega,

                'discount_percentage' => $discountPercentage,
            ], $registeredByUserId);

            $locked->update([
                'package_id' => $package->id,
                'status' => Pedido::STATUS_CONFIRMADO,
            ]);

            return [$package, $locked];
        });

        $this->notificarCliente($locked, Pedido::STATUS_CONFIRMADO);

        return $package;
    }

    /**
     * Avisa por correo al cliente que su pedido cambió de estado. Se
     * envía al correo que haya disponible (Pedido::cliente_email, o el
     * de su cuenta si tenía sesión iniciada al pedir) — sin ninguno de
     * los dos, no hay a quién avisar y no se envía nada, mismo criterio
     * que PackageService::notifyStatusChange.
     *
     * Nunca se deja que un fallo de correo interrumpa una operación ya
     * confirmada en base de datos (pago recibido / guía generada), por
     * eso queda protegido en un try/catch.
     */
    protected function notificarCliente(Pedido $pedido, string $status): void
    {
        try {
            $pedido->loadMissing('user');

            $email = $pedido->cliente_email ?: $pedido->user?->email;

            if (! $email) {
                return;
            }

            Notification::route('mail', $email)
                ->notify(new PedidoEstadoActualizado($pedido->id, $status));
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo enviar la notificación de cambio de estado del pedido.',
                [
                    'pedido_id' => $pedido->id,
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
