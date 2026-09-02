<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LogisticsScanService
{
    public function __construct(
        protected PackageService $packageService,
    ) {
    }

    /**
     * Registra el pistoleo de salida de una agencia.
     *
     * Reglas:
     * - El repartidor debe estar activo.
     * - Debe tener una ruta en curso.
     * - La agencia del paquete debe ser una parada de esa ruta.
     * - La parada puede estar pendiente o ya visitada.
     * - El paquete debe estar RECIBIDO_AGENCIA.
     * - El escaneo asigna el paquete al repartidor si aún no tiene uno.
     * - El escaneo cambia el estado a RECOLECTADO_VENEXPRESS.
     * - Se crea un evento SALIDA inmutable.
     */
    public function scanCollection(
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo un repartidor activo puede escanear paquetes.'
            );
        }

        if ($package->current_status !== Package::STATUS_RECIBIDO_AGENCIA) {
            throw new RuntimeException(
                'Este paquete no está disponible para recolección. '
                . 'Estado actual: ' . $package->statusLabel() . '.'
            );
        }

        return DB::transaction(function () use (
            $package,
            $driver,
            $userId,
        ) {
            $lockedPackage = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedPackage->current_status
                !== Package::STATUS_RECIBIDO_AGENCIA
            ) {
                throw new RuntimeException(
                    'El paquete ya fue procesado por otro movimiento.'
                );
            }

            if (
                $lockedPackage->driver_id !== null
                && (int) $lockedPackage->driver_id !== (int) $driver->id
            ) {
                throw new RuntimeException(
                    'Este paquete ya está asignado a otro repartidor.'
                );
            }

            $route = Route::query()
                ->where('driver_id', $driver->id)
                ->where('status', Route::STATUS_IN_PROGRESS)
                ->with(['stops'])
                ->latest('started_at')
                ->first();

            if (! $route) {
                throw new RuntimeException(
                    'No tienes una ruta en curso. Inicia una ruta antes de escanear paquetes.'
                );
            }

            $stop = $route->stops()
                ->where('ally_id', $lockedPackage->ally_id)
                ->whereIn('status', [
                    RouteStop::STATUS_PENDING,
                    RouteStop::STATUS_VISITED,
                ])
                ->orderBy('sequence')
                ->first();

            if (! $stop) {
                throw new RuntimeException(
                    'La agencia de este paquete no pertenece a tu ruta activa.'
                );
            }

            if ($lockedPackage->driver_id === null) {
                $lockedPackage->update([
                    'driver_id' => $driver->id,
                ]);
            }

            $package = $this->packageService->changeStatus(
                package: $lockedPackage,
                newStatus: Package::STATUS_RECOLECTADO_VENEXPRESS,
                userId: $userId,
                locationDescription: 'Salida escaneada desde Agencia Aliada',
                routeStopId: $stop->id,
                eventType: PackageHistory::EVENT_SALIDA,
                originLocation: 'Agencia Aliada',
                destinationLocation: 'Ruta ' . $route->name,
            );

            if ($stop->status === RouteStop::STATUS_PENDING) {
                $stop->update([
                    'status' => RouteStop::STATUS_VISITED,
                    'visited_at' => now(),
                ]);
            }

            $collectedCount = $stop->packageHistories()
                ->where('event_type', PackageHistory::EVENT_SALIDA)
                ->where('status', Package::STATUS_RECOLECTADO_VENEXPRESS)
                ->count();

            $stop->update([
                'packages_collected_count' => $collectedCount,
            ]);

            return $package->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        });
    }
}
