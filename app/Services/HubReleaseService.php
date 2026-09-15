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
 * Punto de entrada único, explícito (invocado desde Admin, nunca
 * automático dentro de HubReceptionService ni de LogisticsScanService
 * — el flujo HUB -> HUB de Fase 5B-1 no cambia). Decide, según la
 * modalidad de destino final ya elegida por el cliente al registrar
 * el pedido (Package::pickup_mode / requires_delivery), qué hace con
 * un paquete EN_HUB que ya está confirmado en su HUB destino:
 *
 * - Retiro en HUB: EN_HUB -> LISTO_RETIRO directamente (nunca "viaja"
 *   a ningún lado, ya está donde el cliente lo retirará).
 * - Retiro en Aliado / Delivery: EN_HUB -> EN_TRANSITO_NACIONAL vía
 *   PackageDispatchService::dispatch() (reutilizado tal cual, sin
 *   modificarlo) — a partir de ahí, Ally\PackageReception +
 *   DestinationReceptionService (retiro en Aliado) o
 *   PackageService::claimForDelivery()/DriverDeliveryController
 *   (Delivery, Camino A) ya toman el resto del flujo sin ningún
 *   cambio.
 *
 * No crea ningún PackageStatus nuevo: solo usa transiciones que ya
 * existen en el sistema (EN_HUB, LISTO_RETIRO, EN_TRANSITO_NACIONAL).
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

            if (
                $locked->destination_resolution_status !== LogisticsResolutionResult::STATUS_RESOLVED
                || $locked->destination_warehouse_id === null
            ) {
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
     * C) Delivery: EN_HUB -> EN_TRANSITO_NACIONAL vía
     * PackageDispatchService::dispatch() (sin modificar). A partir de
     * ahí, PackageService::claimForDelivery()/DriverDeliveryController
     * (Camino A, sin cambios) ya pueden reclamar y entregar el
     * paquete con normalidad.
     */
    protected function releaseForDelivery(Package $locked, int $userId): Package
    {
        return $this->packageDispatchService->dispatch(
            package: $locked,
            userId: $userId,
            originLocation: $locked->currentWarehouse?->name ?? 'HUB destino',
            destinationLocation: 'Flujo de Delivery',
        );
    }
}
