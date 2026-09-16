<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Fase 5B-2 — Qué ocurre cuando un paquete ya llegó a su HUB destino.
 *
 * Punto de entrada único (invocado desde Admin vía el botón "Liberar",
 * y automáticamente desde HubReceptionService::attemptAutoRelease() en
 * cuanto una recepción en HUB confirma el destino — el flujo HUB -> HUB
 * de Fase 5B-1 no cambia). Decide, según la modalidad de destino final
 * ya elegida por el cliente al registrar el pedido
 * (Package::pickup_mode / requires_delivery), qué hace con un paquete
 * EN_HUB que ya está confirmado en su HUB destino:
 *
 * - Retiro en HUB: EN_HUB -> LISTO_RETIRO directamente (nunca "viaja"
 *   a ningún lado, ya está donde el cliente lo retirará).
 * - Delivery (requires_delivery): EN_HUB -> LISTO_RETIRO directamente,
 *   igual que el retiro en HUB — el paquete ya está en el último punto
 *   que Venexpress controla; un repartidor de entrega lo reclama desde
 *   ahí (PackageService::claimForDelivery() ya acepta LISTO_RETIRO como
 *   uno de los dos estados reclamables, Package::CLAIMABLE_FOR_DELIVERY_STATUSES,
 *   y ya lo usa hoy para el camino Aliado -> Delivery vía
 *   DestinationReceptionService). No pasa por EN_TRANSITO_NACIONAL: ese
 *   estado quedaría reservado para cuando SÍ hay un traslado físico
 *   pendiente, y aquí ya no lo hay.
 * - Retiro en Aliado: EN_HUB -> EN_TRANSITO_NACIONAL vía
 *   PackageDispatchService::dispatch() (reutilizado tal cual, sin
 *   modificarlo) — todavía falta el traslado físico hasta el Aliado de
 *   retiro. A partir de ahí, Ally\PackageReception + DestinationReceptionService
 *   llevan el paquete a LISTO_RETIRO cuando el Aliado lo reciba
 *   físicamente.
 *
 * No crea ningún PackageStatus nuevo: solo usa transiciones que ya
 * existen en el sistema (EN_HUB, LISTO_RETIRO, EN_TRANSITO_NACIONAL).
 *
 * Antes de decidir cualquier cosa, release() vuelve a resolver el
 * destino en vivo y sincroniza destination_warehouse_id/
 * destination_resolution_status si habían quedado desactualizados
 * (WarehouseCoverage corregida después de la recepción en HUB) — así
 * "Liberar" recupera por sí solo un paquete con una resolución
 * obsoleta, sin necesidad de una pantalla/acción aparte.
 */
class HubReleaseService
{
    public function __construct(
        protected LogisticsResolutionService $logisticsResolutionService,
        protected PackageDispatchService $packageDispatchService,
    ) {
    }

    public function release(Package $package, int $userId): Package
    {
        return DB::transaction(function () use ($package, $userId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_EN_HUB) {
                throw new RuntimeException(
                    'Solo se puede liberar un paquete que esté EN_HUB. Estado actual: '
                    .$locked->statusLabel().'.'
                );
            }

            // WarehouseCoverage puede haberse corregido DESPUÉS de que
            // este paquete fue recibido en HUB (destination_warehouse_id/
            // destination_resolution_status quedaron congelados en ese
            // instante — ver HubReceptionService::receiveAtWarehouse()).
            // "Liberar" es el punto natural del flujo normal para que
            // Admin recupere ese caso: se vuelve a resolver en vivo con
            // LogisticsResolutionService (única fuente de verdad, sin
            // reimplementar nada) y se sincroniza lo persistido ANTES de
            // decidir si se puede liberar — así canReleaseFromHub() (que
            // ya resolvía en vivo) y release() nunca vuelven a divergir.
            $resolution = $this->logisticsResolutionService->resolveForPackage($locked);

            if (
                $locked->destination_resolution_status !== $resolution->status
                || $locked->destination_warehouse_id !== $resolution->warehouseId
            ) {
                $locked->destination_warehouse_id = $resolution->isResolved()
                    ? $resolution->warehouseId
                    : null;

                $locked->destination_resolution_status = $resolution->status;

                $locked->save();
            }

            if (! $resolution->isResolved()) {
                throw new RuntimeException(
                    'Este paquete no tiene un HUB destino resuelto. No se puede liberar hasta que '
                    .'Admin revise su cobertura logística.'
                );
            }

            // current_warehouse_id vs destination_warehouse_id: no se
            // comparan directamente los IDs guardados, se reutiliza
            // isAtDestinationWarehouse() (Fase 4), que además vuelve a
            // resolver en vivo — la misma fuente de verdad que ya usa
            // scanHubDeparture() para el caso contrario (HUB -> HUB).
            if (! $this->logisticsResolutionService->isAtDestinationWarehouse($locked)) {
                throw new RuntimeException(
                    'Este paquete todavía no está en su HUB destino — sigue pendiente de otra '
                    .'transferencia entre HUBs. No se puede liberar desde aquí.'
                );
            }

            // Coherencia de modalidad: exactamente una de las dos
            // familias (retiro con pickup_mode, o Delivery), nunca
            // ambas ni ninguna. PackageCreate ya lo garantiza al
            // registrar el pedido; esto es una segunda verificación
            // defensiva, no una decisión nueva.
            if ($locked->requires_delivery && $locked->pickup_mode !== null) {
                throw new RuntimeException(
                    'Este paquete tiene requires_delivery activo junto con una modalidad de retiro — '
                    .'configuración inconsistente. Revísalo antes de continuar.'
                );
            }

            if (! $locked->requires_delivery && $locked->pickup_mode === null) {
                throw new RuntimeException(
                    'Este paquete no tiene una modalidad de destino final válida configurada.'
                );
            }

            if ($locked->pickup_mode === Package::PICKUP_MODE_HUB && $locked->pickup_ally_id !== null) {
                throw new RuntimeException(
                    'Este paquete tiene modalidad de retiro en HUB pero también un punto de retiro '
                    .'Aliado asignado — configuración inconsistente.'
                );
            }

            $pickupAlly = null;

            if ($locked->pickup_mode === Package::PICKUP_MODE_ALLY) {
                if ($locked->pickup_ally_id === null) {
                    throw new RuntimeException(
                        'Este paquete tiene modalidad de retiro en Aliado pero no tiene un punto de '
                        .'retiro asignado.'
                    );
                }

                $pickupAlly = Ally::find($locked->pickup_ally_id);

                if (
                    ! $pickupAlly
                    || $pickupAlly->status !== Ally::STATUS_ACTIVE
                    || ! $pickupAlly->isVerifiedDestination()
                ) {
                    throw new RuntimeException(
                        'El punto de retiro Aliado de este paquete ya no está activo/verificado. '
                        .'Revísalo antes de continuar.'
                    );
                }
            }

            return match (true) {
                $locked->pickup_mode === Package::PICKUP_MODE_HUB => $this->releaseForHubPickup($locked, $userId),
                $locked->pickup_mode === Package::PICKUP_MODE_ALLY => $this->releaseForAllyPickup(
                    $locked,
                    $userId,
                    $pickupAlly,
                ),
                default => $this->releaseForDelivery($locked, $userId),
            };
        });
    }

    /**
     * A) Retiro en HUB: EN_HUB -> LISTO_RETIRO directamente. Conserva
     * current_warehouse_id y destination_warehouse_id tal cual (el
     * paquete no se mueve de ahí), libera driver_id (normalmente ya
     * es null desde la recepción en HUB, pero se deja explícito) y
     * registra un único PackageHistory.
     */
    protected function releaseForHubPickup(Package $locked, int $userId): Package
    {
        $locked->current_status = Package::STATUS_LISTO_RETIRO;
        $locked->driver_id = null;
        $locked->save();

        PackageHistory::create([
            'package_id' => $locked->id,
            'status' => Package::STATUS_LISTO_RETIRO,
            'event_type' => PackageHistory::EVENT_RECEPCION,
            'origin_location' => 'HUB destino',
            'destination_location' => 'Retiro en HUB',
            'location_description' => 'Paquete liberado: queda listo para retiro directo en el HUB destino.',
            'scanned_by_user_id' => $userId,
        ]);

        return $locked->fresh([
            'ally',
            'driver',
            'histories',
            'currentWarehouse',
            'destinationWarehouse',
        ]);
    }

    /**
     * B) Retiro en Aliado: EN_HUB -> EN_TRANSITO_NACIONAL vía
     * PackageDispatchService::dispatch() (sin modificar). El paquete
     * NO queda LISTO_RETIRO aquí — solo en tránsito hacia el Aliado;
     * Ally\PackageReception + DestinationReceptionService (Fase 5A,
     * sin cambios) son quienes lo llevan a LISTO_RETIRO cuando el
     * Aliado confirme la recepción física.
     */
    protected function releaseForAllyPickup(Package $locked, int $userId, Ally $pickupAlly): Package
    {
        return $this->packageDispatchService->dispatch(
            package: $locked,
            userId: $userId,
            originLocation: $locked->currentWarehouse?->name ?? 'HUB destino',
            destinationLocation: $pickupAlly->business_name,
        );
    }

    /**
     * C) Delivery: EN_HUB -> LISTO_RETIRO directamente, igual patrón
     * que releaseForHubPickup() — el paquete ya está en el último HUB
     * que Venexpress controla, ahí lo recoge un repartidor de entrega.
     * No se despacha con PackageDispatchService::dispatch() porque no
     * hay ningún traslado físico pendiente: ese método existe para
     * cuando SÍ falta viajar a otro punto (HUB->HUB, HUB->Aliado), y
     * aquí no es el caso.
     *
     * PackageService::claimForDelivery()/DriverDeliveryController
     * (Camino A, sin cambios) ya reclaman con normalidad desde
     * LISTO_RETIRO — es uno de los dos estados de
     * Package::CLAIMABLE_FOR_DELIVERY_STATUSES, y claimForDelivery()
     * ya sabe transicionarlo a EN_TRANSITO_NACIONAL en el momento en
     * que un repartidor lo reclama de verdad.
     */
    protected function releaseForDelivery(Package $locked, int $userId): Package
    {
        $locked->current_status = Package::STATUS_LISTO_RETIRO;
        $locked->driver_id = null;
        $locked->save();

        PackageHistory::create([
            'package_id' => $locked->id,
            'status' => Package::STATUS_LISTO_RETIRO,
            'event_type' => PackageHistory::EVENT_RECEPCION,
            'origin_location' => 'HUB destino',
            'destination_location' => 'Listo para entrega a domicilio',
            'location_description' => 'Paquete liberado: listo para que un repartidor de entrega lo reclame.',
            'scanned_by_user_id' => $userId,
        ]);

        return $locked->fresh([
            'ally',
            'driver',
            'histories',
            'currentWarehouse',
            'destinationWarehouse',
        ]);
    }
}
