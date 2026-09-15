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
        protected PackageDispatchService $packageDispatchService,
        protected HubReceptionService $hubReceptionService,
        protected RouteService $routeService,
        protected LogisticsResolutionService $logisticsResolutionService,
    ) {}

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
                .'Estado actual: '.$package->statusLabel().'.'
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

            // Bloqueamos la parada para que dos escaneos concurrentes
            // (dos guías distintas de la misma agencia, casi al mismo
            // tiempo) no pisen el conteo de packages_collected_count
            // el uno al otro (lost update).
            $stop = $route->stops()
                ->where('ally_id', $lockedPackage->ally_id)
                ->whereIn('status', [
                    RouteStop::STATUS_PENDING,
                    RouteStop::STATUS_VISITED,
                ])
                ->orderBy('sequence')
                ->lockForUpdate()
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
                destinationLocation: 'Ruta '.$route->name,
            );

            if ($stop->status === RouteStop::STATUS_PENDING) {
                $stop->update([
                    'status' => RouteStop::STATUS_VISITED,
                    'visited_at' => now(),
                ]);
            }

            // lockForUpdate() aquí no es para bloquear estas filas de
            // histórico (son inmutables), sino para forzar una lectura
            // fresca fuera del snapshot de REPEATABLE READ, ya que el
            // stop está bloqueado y necesitamos ver también el commit
            // de una transacción concurrente que ya haya liberado el
            // lock antes que esta.
            $collectedCount = $stop->packageHistories()
                ->where('event_type', PackageHistory::EVENT_SALIDA)
                ->where('status', Package::STATUS_RECOLECTADO_VENEXPRESS)
                ->lockForUpdate()
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

    /**
     * BLOQUEADO desde Fase 5A.
     *
     * La recepción/verificación interna en HUB dejó de ser una
     * operación que el Driver puede confirmar desde el Scanner: ahora
     * es una operación administrativa interna, ejecutada desde
     * Admin\PackageReception vía
     * HubReceptionService::receiveAtWarehouse() (que además resuelve
     * el HUB destino con LogisticsResolutionService y deja fijados
     * current_warehouse_id/destination_warehouse_id — algo que este
     * método nunca hizo).
     *
     * Se conserva la firma y este método (en vez de eliminarlo) a
     * propósito: Scanner.php sigue llamándolo tal cual al confirmar
     * la operación 'hub_reception' (ver
     * Scanner::executeHubReception()), y no se modifica Scanner.php
     * en esta fase — así el Driver recibe aquí mismo un mensaje claro
     * en vez de un error genérico o un comportamiento inesperado. La
     * limpieza de la interfaz del Scanner (dejar de ofrecer esta
     * operación) queda para una fase posterior.
     */
    public function scanHubReception(
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        throw new RuntimeException(
            'La recepción de paquetes en HUB ahora se confirma desde la operación interna de HUB '
            .'en el panel de Admin. Los repartidores ya no pueden confirmarla desde aquí.'
        );
    }

    /**
     * Registra la salida de un paquete desde el HUB, escaneada por un
     * driver de Distribución (driver_type = hub) con una ruta
     * hub_distribution en curso.
     *
     * Fase 5B-1 — HUB origen -> HUB destino DIRECTO. Reglas:
     * - El repartidor debe estar activo y ser de tipo hub.
     * - Debe tener una ruta hub_distribution en curso.
     * - El paquete debe estar EN_HUB.
     * - destination_resolution_status debe ser 'resolved' y
     *   destination_warehouse_id debe existir — LogisticsResolutionService
     *   ya resolvió esto en la recepción de origen (Fase 5A); aquí NO
     *   se vuelve a decidir nada automáticamente, solo se exige que ya
     *   esté resuelto.
     * - Si el paquete ya está en su HUB destino
     *   (isAtDestinationWarehouse()), se rechaza: no se genera una
     *   transferencia innecesaria.
     * - La ruta activa debe tener una parada (warehouse_id) igual al
     *   destination_warehouse_id del paquete — el paquete solo puede
     *   salir hacia SU HUB destino, nunca hacia cualquier otro.
     * - El escaneo asigna el paquete a este driver si aún no tiene uno.
     * - Delega el cambio de estado a PackageDispatchService::dispatch()
     *   (EN_HUB -> EN_TRANSITO_NACIONAL), pasándole el route_stop_id
     *   de la parada de destino: dispatch() acepta ese parámetro
     *   opcional (Fase 5B-1) y lo usa para llenar route_stop_id del
     *   propio evento SALIDA que ya crea — un único evento por
     *   despacho, sin duplicar historial.
     * - current_warehouse_id NO se toca: sigue reflejando el HUB
     *   origen mientras el paquete está en tránsito (regla de negocio
     *   confirmada: "mientras el paquete está en tránsito, mantener el
     *   último HUB físicamente confirmado").
     */
    public function scanHubDeparture(
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo un repartidor activo puede escanear paquetes.'
            );
        }

        if ($driver->driver_type !== Driver::TYPE_HUB) {
            throw new RuntimeException(
                'Solo un repartidor de HUB puede registrar salidas del HUB.'
            );
        }

        if ($package->current_status !== Package::STATUS_EN_HUB) {
            throw new RuntimeException(
                'Este paquete no está disponible para despacho. '
                .'Estado actual: '.$package->statusLabel().'.'
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

            if ($lockedPackage->current_status !== Package::STATUS_EN_HUB) {
                throw new RuntimeException(
                    'El paquete ya fue procesado por otro movimiento.'
                );
            }

            if (
                $lockedPackage->driver_id !== null
                && (int) $lockedPackage->driver_id !== (int) $driver->id
            ) {
                throw new RuntimeException(
                    'Este paquete ya fue tomado por otro repartidor de distribución.'
                );
            }

            $route = Route::query()
                ->where('driver_id', $driver->id)
                ->where('status', Route::STATUS_IN_PROGRESS)
                ->where('route_type', Route::TYPE_HUB_DISTRIBUTION)
                ->with('stops.warehouse')
                ->latest('started_at')
                ->first();

            if (! $route) {
                throw new RuntimeException(
                    'No tienes una ruta de distribución en curso. '
                    .'Inicia una ruta antes de escanear paquetes.'
                );
            }

            if (
                $lockedPackage->destination_resolution_status !== LogisticsResolutionResult::STATUS_RESOLVED
                || $lockedPackage->destination_warehouse_id === null
            ) {
                throw new RuntimeException(
                    'Este paquete no tiene un HUB destino resuelto. No puede salir en una transferencia '
                    .'entre HUBs hasta que Admin revise su cobertura logística.'
                );
            }

            if ($this->logisticsResolutionService->isAtDestinationWarehouse($lockedPackage)) {
                throw new RuntimeException(
                    'Este paquete ya está en su HUB destino. No requiere una transferencia entre HUBs.'
                );
            }

            $stop = $route->stops->first(
                fn (RouteStop $stop) => $stop->warehouse_id === $lockedPackage->destination_warehouse_id
            );

            if (! $stop) {
                throw new RuntimeException(
                    'Esta ruta no tiene como destino el HUB que corresponde a este paquete.'
                );
            }

            if ($lockedPackage->driver_id === null) {
                $lockedPackage->update([
                    'driver_id' => $driver->id,
                ]);
            }

            $dispatched = $this->packageDispatchService->dispatch(
                package: $lockedPackage,
                userId: $userId,
                originLocation: 'HUB',
                destinationLocation: 'Ruta de distribución '.$route->name,
                routeStopId: $stop->id,
            );

            return $dispatched->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        });
    }

    /**
     * Registra la llegada FÍSICA de un paquete al HUB destino,
     * escaneada por el mismo driver de Distribución que lo sacó del
     * HUB origen.
     *
     * IMPORTANTE: este método NO cambia current_status ni hace la
     * recepción interna. El paquete permanece EN_TRANSITO_NACIONAL.
     * Solo registra que el vehículo llegó y libera la custodia del
     * driver — la verificación/recepción administrativa (que sí
     * transiciona a EN_HUB) es una operación separada, hecha por
     * Admin/interno (ver HubReceptionService::receiveTransferAtWarehouse(),
     * Fase 5B-1). El driver nunca confirma la recepción final, ni
     * siquiera para una transferencia entre HUBs.
     */
    public function scanHubArrival(
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo un repartidor activo puede escanear paquetes.'
            );
        }

        if ($driver->driver_type !== Driver::TYPE_HUB) {
            throw new RuntimeException(
                'Solo un repartidor de HUB puede registrar llegadas a almacén.'
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

            if ((int) $lockedPackage->driver_id !== (int) $driver->id) {
                throw new RuntimeException(
                    'Este paquete no está bajo tu custodia.'
                );
            }

            if ($lockedPackage->current_status !== Package::STATUS_EN_TRANSITO_NACIONAL) {
                throw new RuntimeException(
                    'Este paquete no está en tránsito. '
                    .'Estado actual: '.$lockedPackage->statusLabel().'.'
                );
            }

            $route = Route::query()
                ->where('driver_id', $driver->id)
                ->where('status', Route::STATUS_IN_PROGRESS)
                ->where('route_type', Route::TYPE_HUB_DISTRIBUTION)
                ->with(['stops.warehouse'])
                ->latest('started_at')
                ->first();

            if (! $route) {
                throw new RuntimeException(
                    'No tienes una ruta de distribución en curso.'
                );
            }

            $stop = $this->resolveDestinationStop($lockedPackage, $route);

            if (! $stop) {
                throw new RuntimeException(
                    'Esta guía no pertenece a tu ruta de distribución.'
                );
            }

            $lockedPackage->update([
                'driver_id' => null,
            ]);

            PackageHistory::create([
                'package_id' => $lockedPackage->id,
                'route_stop_id' => $stop->id,
                'status' => $lockedPackage->current_status,
                'event_type' => PackageHistory::EVENT_TRANSFERENCIA,
                'origin_location' => 'Ruta de distribución '.$route->name,
                'destination_location' => $stop->warehouse->name,
                'location_description' => 'Entregado por el repartidor de distribución en el almacén destino',
                'scanned_by_user_id' => $userId,
            ]);

            if ($stop->status === RouteStop::STATUS_PENDING) {
                $stop->update([
                    'status' => RouteStop::STATUS_VISITED,
                    'visited_at' => now(),
                ]);
            }

            return $lockedPackage->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        });
    }

    /**
     * Resuelve a qué parada (almacén) de la ruta de distribución
     * corresponde este paquete.
     *
     * Fase 5B-1: deja de comparar texto de ciudad/estado — usa
     * destination_warehouse_id (ya resuelto por
     * LogisticsResolutionService en la salida del HUB origen,
     * scanHubDeparture()) contra el warehouse_id de cada parada. Es
     * la misma relación real que scanHubDeparture() ya validó al
     * aceptar el despacho, así que aquí solo se vuelve a localizar la
     * parada — no se decide nada nuevo.
     */
    protected function resolveDestinationStop(
        Package $package,
        Route $route,
    ): ?RouteStop {
        if ($package->destination_warehouse_id === null) {
            return null;
        }

        return $route->stops->first(
            fn (RouteStop $stop) => $stop->warehouse_id === $package->destination_warehouse_id
        );
    }

}
