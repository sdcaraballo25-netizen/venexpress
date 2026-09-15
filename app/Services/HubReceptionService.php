<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HubReceptionService
{
    public function __construct(
        protected LogisticsResolutionService $logisticsResolutionService,
    ) {
    }

    public function receive(
        Package $package,
        int $userId,
        string $hubLocation,
    ): Package {
        $hubLocation = trim($hubLocation);

        if ($hubLocation === '') {
            throw new RuntimeException('Indica el Hub donde fue recibido el paquete.');
        }

        return DB::transaction(function () use ($package, $userId, $hubLocation) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_RECOLECTADO_VENEXPRESS) {
                throw new RuntimeException(
                    'Solo se puede registrar recepción de Hub para un paquete recolectado por Venexpress. '
                    . 'Estado actual: ' . $locked->statusLabel() . '.'
                );
            }

            $locked->current_status = Package::STATUS_EN_HUB;

            // El paquete deja la custodia del repartidor que lo
            // recolectó en origen y pasa a la red de hubs. Si no
            // limpiamos driver_id aquí, ese id de repartidor de origen
            // queda "pegado" al paquete y bloquea más adelante la
            // asignación del repartidor de reparto en destino
            // (DeliveryAssignmentService::assign() rechaza el paquete
            // creyendo que ya pertenece a otro repartidor).
            $locked->driver_id = null;

            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'status' => Package::STATUS_EN_HUB,
                'event_type' => PackageHistory::EVENT_RECEPCION,
                'origin_location' => 'Recolección del repartidor',
                'destination_location' => $hubLocation,
                'location_description' => 'Recepción física en Hub Venexpress',
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh(['ally', 'driver', 'histories']);
        });
    }

    /**
     * Fase 5A — Recepción/verificación interna en HUB.
     *
     * Reemplaza, para el camino administrativo, a receive(): además
     * de la misma transición RECOLECTADO_VENEXPRESS -> EN_HUB, deja
     * fijado en qué almacén está el paquete ahora mismo
     * (current_warehouse_id) y resuelve — usando
     * LogisticsResolutionService como única fuente de verdad, sin
     * comparación de texto — a qué HUB debería llegar finalmente
     * (destination_warehouse_id).
     *
     * La recepción física SIEMPRE se completa (current_status pasa a
     * EN_HUB, current_warehouse_id queda fijado) incluso cuando la
     * resolución no da un resultado utilizable: lo único que queda
     * bloqueado en ese caso es la continuación logística
     * (destination_warehouse_id queda null), nunca la recepción en sí
     * — el paquete llegó físicamente y eso no depende de que la
     * cobertura esté bien configurada. destination_resolution_status
     * deja constancia de cuál de los cuatro resultados posibles
     * ('resolved', 'no_coverage', 'ambiguous', 'invalid') se obtuvo,
     * para que Admin pueda detectar y corregir el caso más adelante
     * sin necesidad de un nuevo estado de Package ni de una pantalla
     * de excepciones todavía.
     *
     * receive() (arriba) no cambia: sigue siendo el método que usa el
     * camino antiguo (hoy solo invocado internamente; el escaneo del
     * Driver quedó bloqueado en LogisticsScanService::scanHubReception()).
     */
    public function receiveAtWarehouse(
        Package $package,
        int $userId,
        Warehouse $warehouse,
    ): Package {
        if (! $warehouse->is_active) {
            throw new RuntimeException(
                'Solo se puede registrar recepción interna en un almacén activo.'
            );
        }

        return DB::transaction(function () use ($package, $userId, $warehouse) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_RECOLECTADO_VENEXPRESS) {
                throw new RuntimeException(
                    'Solo se puede registrar recepción de Hub para un paquete recolectado por Venexpress. '
                    . 'Estado actual: ' . $locked->statusLabel() . '.'
                );
            }

            $resolution = $this->logisticsResolutionService->resolveForPackage($locked);

            $locked->current_status = Package::STATUS_EN_HUB;

            // Igual que en receive(): el paquete deja la custodia del
            // repartidor que lo recolectó en origen y pasa a la red
            // de hubs.
            $locked->driver_id = null;

            $locked->current_warehouse_id = $warehouse->id;

            $locked->destination_warehouse_id = $resolution->isResolved()
                ? $resolution->warehouseId
                : null;

            $locked->destination_resolution_status = $resolution->status;

            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'status' => Package::STATUS_EN_HUB,
                'event_type' => PackageHistory::EVENT_RECEPCION,
                'origin_location' => 'Recolección del repartidor',
                'destination_location' => $warehouse->name,
                'location_description' => $this->receptionDescription($warehouse, $resolution),
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh([
                'ally',
                'driver',
                'histories',
                'currentWarehouse',
                'destinationWarehouse',
            ]);
        });
    }

    /**
     * Detalle legible de la resolución para el historial permanente
     * (a diferencia del mensaje que ve Admin en pantalla, este queda
     * grabado para siempre en PackageHistory).
     */
    protected function receptionDescription(
        Warehouse $warehouse,
        LogisticsResolutionResult $resolution,
    ): string {
        return match ($resolution->status) {
            LogisticsResolutionResult::STATUS_RESOLVED => $resolution->warehouseId === $warehouse->id
                ? "Recepción interna verificada en {$warehouse->name}. Destino final confirmado."
                : 'Recepción interna verificada en '.$warehouse->name.'. Pendiente de redistribución hacia '
                    .($this->warehouseName($resolution->warehouseId)).'.',

            LogisticsResolutionResult::STATUS_NO_COVERAGE =>
                "Recepción interna verificada en {$warehouse->name}. SIN COBERTURA configurada para el "
                ."destino — requiere revisión manual. {$resolution->reason}",

            LogisticsResolutionResult::STATUS_AMBIGUOUS =>
                "Recepción interna verificada en {$warehouse->name}. COBERTURA AMBIGUA para el destino — "
                ."requiere revisión manual. {$resolution->reason}",

            LogisticsResolutionResult::STATUS_INVALID =>
                "Recepción interna verificada en {$warehouse->name}. Estado/ciudad de destino inválido — "
                ."requiere revisión manual. {$resolution->reason}",

            default => "Recepción interna verificada en {$warehouse->name}.",
        };
    }

    protected function warehouseName(?int $warehouseId): string
    {
        if ($warehouseId === null) {
            return 'un almacén sin identificar';
        }

        return Warehouse::find($warehouseId)?->name ?? "almacén #{$warehouseId}";
    }
}
