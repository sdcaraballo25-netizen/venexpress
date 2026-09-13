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
     * Registra la recepción física en el HUB de un paquete
     * recolectado en la ruta hub_transfer en curso de este mismo
     * driver (segunda mitad de "Aliado -> HUB", después de
     * scanCollection()).
     *
     * Reutiliza HubReceptionService::receive() tal cual para la
     * transición de estado (RECOLECTADO_VENEXPRESS -> EN_HUB,
     * EVENT_RECEPCION) — ese servicio no cambia y sigue funcionando
     * igual para Admin\PackageReception. Aquí solo se agrega la
     * validación de que el paquete pertenece a la ruta hub_transfer
     * activa de este driver, la misma garantía que ya aplican
     * scanCollection()/scanHubDeparture()/scanHubArrival(): NUNCA se
     * decide por Package.driver_id a solas, sino por el
     * PackageHistory (EVENT_SALIDA) que dejó su recolección.
     *
     * Reglas:
     * - El repartidor debe estar activo y ser de tipo hub.
     * - Debe tener una ruta hub_transfer en curso.
     * - El paquete debe estar RECOLECTADO_VENEXPRESS.
     * - El paquete debe haber sido recolectado en ESA ruta.
     */
    public function scanHubReception(
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
                'Solo un repartidor de HUB puede registrar recepciones en HUB.'
            );
        }

        if ($package->current_status !== Package::STATUS_RECOLECTADO_VENEXPRESS) {
            throw new RuntimeException(
                'Este paquete no está disponible para recepción en HUB. '
                .'Estado actual: '.$package->statusLabel().'.'
            );
        }

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->where('route_type', Route::TYPE_HUB_TRANSFER)
            ->latest('started_at')
            ->first();

        if (! $route) {
            throw new RuntimeException(
                'No tienes una ruta de recolección en curso. '
                .'Inicia una ruta antes de escanear paquetes.'
            );
        }

        $belongsToThisRoute = $this->routeService
            ->packageIdsCollectedOnRoute($route)
            ->contains($package->id);

        if (! $belongsToThisRoute) {
            throw new RuntimeException(
                'Este paquete no fue recolectado en tu ruta activa.'
            );
        }

        return $this->hubReceptionService->receive(
            package: $package,
            userId: $userId,
            hubLocation: 'HUB Venexpress',
        );
    }

    /**
     * Registra la salida de un paquete desde el HUB, escaneada por un
     * driver de Distribución (driver_type = hub) con una ruta
     * hub_distribution en curso.
     *
     * Reglas:
     * - El repartidor debe estar activo y ser de tipo hub.
     * - Debe tener una ruta hub_distribution en curso.
     * - El paquete debe estar EN_HUB.
     * - El escaneo asigna el paquete a este driver si aún no tiene uno.
     * - Delega el cambio de estado a PackageDispatchService::dispatch()
     *   (EN_HUB -> EN_TRANSITO_NACIONAL), sin modificar ese servicio.
     * - No fija route_stop_id todavía: al salir del HUB el camión
     *   lleva paquetes para varias paradas futuras, no una sola.
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
                ->latest('started_at')
                ->first();

            if (! $route) {
                throw new RuntimeException(
                    'No tienes una ruta de distribución en curso. '
                    .'Inicia una ruta antes de escanear paquetes.'
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
            );

            return $dispatched->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        });
    }

    /**
     * Registra la llegada de un paquete al almacén propio de
     * Venexpress destino, escaneada por el mismo driver de
     * Distribución que lo sacó del HUB.
     *
     * IMPORTANTE: este método NO cambia current_status. El paquete
     * permanece EN_TRANSITO_NACIONAL, que es el estado que la app de
     * Delivery (de otro desarrollador) ya sabe leer hoy para reclamar
     * y entregar. Solo se registra el movimiento y se libera la
     * custodia del driver de HUB.
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
     * corresponde este paquete, comparando destination_city/state del
     * paquete contra la ciudad/estado del almacén de cada parada.
     *
     * Mismo criterio de coincidencia por texto que ya usa
     * Ally\PackageReception::belongsToDestinationAgency() para
     * agencias — es la misma limitación que ya existe hoy (no hay FK
     * directo entre Package y su destino), no algo nuevo.
     */
    protected function resolveDestinationStop(
        Package $package,
        Route $route,
    ): ?RouteStop {
        $packageCity = mb_strtolower(trim((string) $package->destination_city));
        $packageState = mb_strtolower(trim((string) $package->destination_state));

        if ($packageCity === '' || $packageState === '') {
            return null;
        }

        return $route->stops->first(function (RouteStop $stop) use ($packageCity, $packageState) {
            if (! $stop->warehouse) {
                return false;
            }

            return mb_strtolower(trim((string) $stop->warehouse->city)) === $packageCity
                && mb_strtolower(trim((string) $stop->warehouse->state)) === $packageState;
        });
    }
}
