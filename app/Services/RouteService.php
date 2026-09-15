<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RouteService
{
    public function __construct(
        protected PackageService $packageService,
    ) {}

    /**
     * Crea una nueva ruta con sus agencias/paradas.
     */
    public function createRoute(
        array $data,
        array $allyIdsInOrder,
        int $createdByUserId
    ): Route {
        if (count($allyIdsInOrder) === 0) {
            throw new RuntimeException(
                'Una ruta necesita al menos una agencia.'
            );
        }

        return DB::transaction(function () use (
            $data,
            $allyIdsInOrder,
            $createdByUserId
        ) {
            $route = Route::create([
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'name' => $data['name'],
                'created_by' => $createdByUserId,
                'status' => Route::STATUS_DRAFT,
                'route_type' => $data['route_type'] ?? Route::TYPE_DELIVERY,
                'origin_warehouse_id' => $data['origin_warehouse_id'] ?? null,
                'return_warehouse_id' => $data['return_warehouse_id'] ?? null,
            ]);

            $this->syncStops(
                $route,
                $allyIdsInOrder
            );

            $this->log(
                $createdByUserId,
                'route.created',
                $route,
                "Creó la ruta \"{$route->name}\"{$this->locationSuffix($route)} con "
                .count($allyIdsInOrder)
                .' paradas.',
                [
                    'state' => $route->state,
                    'city' => $route->city,
                    'route_type' => $route->route_type,
                    'origin_warehouse_id' => $route->origin_warehouse_id,
                    'return_warehouse_id' => $route->return_warehouse_id,
                    'stops' => count($allyIdsInOrder),
                ]
            );

            return $route->fresh('stops');
        });
    }

    /**
     * Actualiza los datos y las paradas de una ruta.
     *
     * state/city son opcionales desde la Fase 2 (rutas multiestado):
     * ya no restringen qué paradas puede tener la ruta, quedan solo
     * como metadato descriptivo/de búsqueda para el listado de rutas.
     */
    public function updateRoute(
        int $routeId,
        string $name,
        ?string $state,
        ?string $city,
        array $allyIds,
        int $actingUserId,
        ?int $originWarehouseId = null,
        ?int $returnWarehouseId = null,
    ): Route {
        $route = Route::findOrFail($routeId);

        if (! $route->isEditable()) {
            throw new RuntimeException(
                'Esta ruta ya está en curso o finalizada; no se puede editar.'
            );
        }

        if (count($allyIds) === 0) {
            throw new RuntimeException(
                'Una ruta necesita al menos una agencia.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $name,
            $state,
            $city,
            $allyIds,
            $actingUserId,
            $originWarehouseId,
            $returnWarehouseId,
        ) {
            $route->update([
                'name' => $name,
                'state' => $state,
                'city' => $city,
                'origin_warehouse_id' => $originWarehouseId,
                'return_warehouse_id' => $returnWarehouseId,
            ]);

            $route->stops()->delete();

            $this->syncStops(
                $route,
                $allyIds
            );

            $this->log(
                $actingUserId,
                'route.updated',
                $route,
                "Actualizó la ruta \"{$route->name}\".",
                [
                    'state' => $route->state,
                    'city' => $route->city,
                    'origin_warehouse_id' => $route->origin_warehouse_id,
                    'return_warehouse_id' => $route->return_warehouse_id,
                    'stops' => count($allyIds),
                ]
            );

            return $route->fresh('stops');
        });
    }

    /**
     * Actualiza únicamente el recorrido de la ruta.
     */
    public function updateStops(
        Route $route,
        array $allyIdsInOrder,
        int $actingUserId
    ): Route {
        if (! $route->isEditable()) {
            throw new RuntimeException(
                'Esta ruta ya está en curso o finalizada; '
                .'no se puede editar su recorrido.'
            );
        }

        if (count($allyIdsInOrder) === 0) {
            throw new RuntimeException(
                'Una ruta necesita al menos una agencia.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $allyIdsInOrder,
            $actingUserId
        ) {
            $route->stops()->delete();

            $this->syncStops(
                $route,
                $allyIdsInOrder
            );

            $this->log(
                $actingUserId,
                'route.stops_updated',
                $route,
                "Actualizó el recorrido de \"{$route->name}\" ("
                .count($allyIdsInOrder)
                .' paradas).',
                [
                    'stops' => count($allyIdsInOrder),
                ]
            );

            return $route->fresh('stops');
        });
    }

    /**
     * Crea las paradas respetando el orden recibido.
     *
     * Los IDs son de Ally para cualquier ruta normal, o de Warehouse
     * cuando la ruta es de tipo hub_distribution (HUB -> almacén
     * propio de Venexpress). Una ruta nunca mezcla los dos tipos de
     * parada.
     */
    protected function syncStops(
        Route $route,
        array $allyIdsInOrder
    ): void {
        $locationColumn = $route->isHubDistribution()
            ? 'warehouse_id'
            : 'ally_id';

        foreach (array_values($allyIdsInOrder) as $index => $locationId) {
            RouteStop::create([
                'route_id' => $route->id,
                $locationColumn => $locationId,
                'sequence' => $index + 1,
                'status' => RouteStop::STATUS_PENDING,
            ]);
        }
    }

    /**
     * El Driver toma él mismo una ruta disponible (draft, sin dueño).
     * Reemplaza la asignación manual que antes hacía el Admin — el
     * Admin ahora solo crea/publica rutas; tomarlas es autoservicio
     * del Driver.
     *
     * IMPORTANTE: esta función NO asigna paquetes. Los paquetes
     * permanecen con driver_id = NULL hasta que el repartidor
     * correspondiente los escanee.
     *
     * Protección de concurrencia: lockForUpdate() + re-chequeo de
     * disponibilidad dentro de la transacción, para que si dos
     * Drivers intentan tomar la misma ruta casi al mismo tiempo, solo
     * uno gane (mismo patrón que PackageService::claimForDelivery()).
     */
    public function claimRoute(
        Route $route,
        Driver $driver,
        int $actingUserId
    ): Route {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo un repartidor activo puede tomar rutas.'
            );
        }

        if (! $this->isCompatible($driver, $route)) {
            throw new RuntimeException(
                'Esta ruta no es compatible con tu tipo de repartidor.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $driver,
            $actingUserId
        ) {
            $lockedRoute = Route::query()
                ->whereKey($route->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedRoute->status !== Route::STATUS_DRAFT
                || $lockedRoute->driver_id !== null
            ) {
                throw new RuntimeException(
                    'Esta ruta ya no está disponible.'
                );
            }

            // Un Driver no puede tener más de una ruta activa a la vez.
            $hasActiveRoute = Route::query()
                ->where('driver_id', $driver->id)
                ->whereIn('status', [
                    Route::STATUS_ASSIGNED,
                    Route::STATUS_IN_PROGRESS,
                ])
                ->exists();

            if ($hasActiveRoute) {
                throw new RuntimeException(
                    'Ya tienes una ruta activa. Finalízala antes de tomar otra.'
                );
            }

            /*
             * La ruta debe tener al menos una parada. Se cuenta de
             * forma genérica (no por ally_id) porque una ruta de
             * hub_distribution tiene warehouse_id en vez de ally_id
             * en sus paradas.
             */
            $locationColumn = $lockedRoute->isHubDistribution()
                ? 'warehouse_id'
                : 'ally_id';

            $locationIds = $lockedRoute->stops()
                ->pluck($locationColumn)
                ->unique()
                ->values();

            if ($locationIds->isEmpty()) {
                throw new RuntimeException(
                    'La ruta no tiene paradas asignadas.'
                );
            }

            $lockedRoute->update([
                'driver_id' => $driver->id,
                'status' => Route::STATUS_ASSIGNED,
            ]);

            $this->log(
                $actingUserId,
                'route.claimed',
                $lockedRoute,
                "{$driver->user->name} ({$driver->vehicle_plate}) "
                ."tomó la ruta \"{$lockedRoute->name}\".",
                [
                    'driver_id' => $driver->id,
                    $locationColumn.'s' => $locationIds->all(),
                ]
            );

            return $lockedRoute->fresh('stops');
        });
    }

    /**
     * El Driver libera una ruta que ya tomó pero todavía no inició,
     * para que vuelva a quedar disponible para cualquier repartidor
     * compatible.
     *
     * Distinto de cancel(): aquí la ruta logística NO se cancela,
     * solo se desasocia del driver y vuelve a STATUS_DRAFT. No toca
     * Package en absoluto (igual que claimRoute(), que tampoco los
     * asigna).
     *
     * Solo aplica cuando la ruta está STATUS_ASSIGNED: una ruta
     * STATUS_IN_PROGRESS ya no se "libera", debe finalizarse con
     * complete().
     */
    public function release(
        Route $route,
        Driver $driver,
        int $actingUserId
    ): Route {
        return DB::transaction(function () use (
            $route,
            $driver,
            $actingUserId
        ) {
            $lockedRoute = Route::query()
                ->whereKey($route->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedRoute->driver_id !== (int) $driver->id) {
                throw new RuntimeException(
                    'Esta ruta no está asignada a tu usuario.'
                );
            }

            if ($lockedRoute->status === Route::STATUS_IN_PROGRESS) {
                throw new RuntimeException(
                    'Esta ruta ya está en curso; no se puede liberar. '
                    .'Debes finalizarla.'
                );
            }

            if ($lockedRoute->status !== Route::STATUS_ASSIGNED) {
                throw new RuntimeException(
                    'Solo se puede liberar una ruta que esté asignada '
                    .'y sin iniciar.'
                );
            }

            $lockedRoute->update([
                'driver_id' => null,
                'status' => Route::STATUS_DRAFT,
            ]);

            $this->log(
                $actingUserId,
                'route.released',
                $lockedRoute,
                "{$driver->user->name} ({$driver->vehicle_plate}) "
                ."liberó la ruta \"{$lockedRoute->name}\" antes de "
                .'iniciarla.',
                [
                    'driver_id' => $driver->id,
                ]
            );

            return $lockedRoute->fresh('stops');
        });
    }

    /**
     * route_type compatibles con el driver_type dado. Única fuente de
     * verdad para la compatibilidad driver_type <-> route_type:
     * hub -> hub_transfer, hub_distribution
     * delivery -> delivery
     */
    public function compatibleRouteTypes(Driver $driver): array
    {
        return match ($driver->driver_type) {
            Driver::TYPE_HUB => [Route::TYPE_HUB_TRANSFER, Route::TYPE_HUB_DISTRIBUTION],
            Driver::TYPE_DELIVERY => [Route::TYPE_DELIVERY],
            default => [],
        };
    }

    protected function isCompatible(Driver $driver, Route $route): bool
    {
        return in_array(
            $route->route_type,
            $this->compatibleRouteTypes($driver),
            true
        );
    }

    /**
     * Rutas disponibles para que este Driver las tome: sin dueño, en
     * borrador, compatibles con su driver_type. Sin filtro de
     * zona/estado (el driver puede tomar rutas de cualquier estado si
     * es compatible); ordenadas por antigüedad (FIFO) para que la que
     * lleva más tiempo esperando se ofrezca primero. Fuente única de
     * verdad para "rutas disponibles", usada tanto por la API
     * (DriverRouteController) como por el Dashboard web.
     */
    public function availableRoutesFor(Driver $driver): Collection
    {
        return Route::query()
            ->whereNull('driver_id')
            ->where('status', Route::STATUS_DRAFT)
            ->whereIn('route_type', $this->compatibleRouteTypes($driver))
            ->with(['stops.ally.user', 'stops.warehouse'])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Inicia una ruta.
     */
    public function start(
        Route $route,
        int $actingUserId
    ): Route {
        if (! $route->driver_id) {
            throw new RuntimeException(
                'La ruta necesita un repartidor asignado antes de iniciar.'
            );
        }

        if ($route->status === Route::STATUS_COMPLETED) {
            throw new RuntimeException(
                'Esta ruta ya fue finalizada.'
            );
        }

        if ($route->status === Route::STATUS_CANCELLED) {
            throw new RuntimeException(
                'Esta ruta está cancelada.'
            );
        }

        $route->update([
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        $this->log(
            $actingUserId,
            'route.started',
            $route,
            "Inició el recorrido de \"{$route->name}\"."
        );

        return $route->fresh();
    }

    /**
     * Devuelve los paquetes disponibles para recoger
     * en una determinada parada.
     *
     * Solo muestra paquetes que todavía no tienen repartidor.
     */
    public function collectiblePackagesFor(
        RouteStop $stop
    ): Collection {
        return Package::query()
            ->where('ally_id', $stop->ally_id)
            ->where(
                'current_status',
                Package::STATUS_RECIBIDO_AGENCIA
            )
            ->whereNull('driver_id')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Registra la recolección de paquetes de una parada.
     */
    public function registerCollection(
        Route $route,
        RouteStop $stop,
        array $packageIds,
        int $actingUserId
    ): RouteStop {
        // Validaciones rápidas "optimistas" antes de abrir la
        // transacción, solo para fallar temprano en el caso común.
        // La validación que realmente importa (contra condiciones
        // de carrera) se repite DENTRO de la transacción con el
        // registro bloqueado.
        if (! $route->isInProgress()) {
            throw new RuntimeException(
                'La ruta debe estar en curso para registrar recolecciones.'
            );
        }

        if ($stop->route_id !== $route->id) {
            throw new RuntimeException(
                'Esta parada no pertenece a la ruta indicada.'
            );
        }

        if (! $route->driver_id) {
            throw new RuntimeException(
                'La ruta no tiene un repartidor asignado.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $stop,
            $packageIds,
            $actingUserId
        ) {
            // Bloqueamos la fila de la parada para que dos
            // solicitudes concurrentes (doble tap, dos pestañas,
            // reintento de red) no puedan procesar la misma
            // recolección dos veces.
            $lockedStop = RouteStop::query()
                ->whereKey($stop->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedStop->status !== RouteStop::STATUS_PENDING) {
                throw new RuntimeException(
                    'Esta parada ya fue marcada como visitada u omitida.'
                );
            }

            $stop = $lockedStop;

            if ($stop->ally_id === null) {
                throw new RuntimeException(
                    'Esta parada no es una agencia de recolección.'
                );
            }

            /*
             * Solo permitimos recoger paquetes:
             *
             * - de esta agencia
             * - en RECIBIDO_AGENCIA
             * - asignados al mismo repartidor de la ruta
             */
            $packages = Package::query()
                ->whereIn('id', $packageIds)
                ->where('ally_id', $stop->ally_id)
                ->where(
                    'current_status',
                    Package::STATUS_RECIBIDO_AGENCIA
                )
                ->whereNull('driver_id')
                ->get();

            foreach ($packages as $package) {
                // Asignamos el repartidor de la ruta ANTES de cambiar
                // el estado. Sin esto, el paquete queda huérfano de
                // driver_id y desaparece de las vistas del repartidor
                // (Dashboard, Mis Paquetes, Detalle) aunque ya haya
                // sido recolectado en su ruta.
                $package->update([
                    'driver_id' => $route->driver_id,
                ]);

                $this->packageService->changeStatus(
                    package: $package,
                    newStatus: Package::STATUS_RECOLECTADO_VENEXPRESS,
                    userId: $actingUserId,
                    locationDescription: "Salida escaneada desde {$package->ally->business_name}",
                    routeStopId: $stop->id,
                    eventType: PackageHistory::EVENT_SALIDA,
                    originLocation: 'Agencia Aliada',
                    destinationLocation: 'Ruta '.$route->name,
                );
            }

            $stop->update([
                'status' => RouteStop::STATUS_VISITED,
                'visited_at' => now(),
                'packages_collected_count' => $packages->count(),
            ]);

            $this->log(
                $actingUserId,
                'route.stop_visited',
                $route,
                'Registró la recolección en '
                ."{$stop->ally->business_name} ("
                .$packages->count()
                ." paquetes) — ruta \"{$route->name}\".",
                [
                    'route_stop_id' => $stop->id,
                    'ally_id' => $stop->ally_id,
                    'packages_collected' => $packages->count(),
                ]
            );

            return $stop->fresh();
        });
    }

    /**
     * IDs de Package recolectados en esta ruta durante su ciclo
     * actual: el evento EVENT_SALIDA que ya registran
     * RouteService::registerCollection() (Admin) y
     * LogisticsScanService::scanCollection() (Driver) al pistolear en
     * una de sus paradas. Única fuente de verdad para "qué paquetes
     * pertenecen a esta ruta" en rutas delivery/hub_transfer — NUNCA
     * se determina comparando solo Package.driver_id, porque ese
     * campo también lo usa el flujo independiente
     * PackageService::claimForDelivery(), que no pertenece a ninguna
     * ruta.
     */
    public function packageIdsCollectedOnRoute(Route $route): \Illuminate\Support\Collection
    {
        $stopIds = $route->stops()->pluck('id');

        return PackageHistory::query()
            ->whereIn('route_stop_id', $stopIds)
            ->where('event_type', PackageHistory::EVENT_SALIDA)
            ->pluck('package_id')
            ->unique()
            ->values();
    }

    /**
     * IDs de Package despachados en el tramo HUB -> HUB de esta ruta
     * (route_type hub_distribution): el evento EVENT_SALIDA que
     * PackageDispatchService::dispatch() registra al salir del HUB
     * origen, con route_stop_id ya fijado a la parada de destino
     * (LogisticsScanService::scanHubDeparture() se lo pasa, Fase
     * 5B-1).
     */
    public function packageIdsDispatchedOnRoute(Route $route): \Illuminate\Support\Collection
    {
        $stopIds = $route->stops()->pluck('id');

        return PackageHistory::query()
            ->whereIn('route_stop_id', $stopIds)
            ->where('event_type', PackageHistory::EVENT_SALIDA)
            ->pluck('package_id')
            ->unique()
            ->values();
    }

    /**
     * Cuenta los paquetes de esta ruta que todavía no llegaron a su
     * hito final, según route_type — la definición de "pendiente" no
     * es la misma para los tres tipos:
     *
     * - delivery: el mismo driver de la ruta entrega directamente al
     *   cliente, así que el hito final es ENTREGADO.
     * - hub_transfer: el driver solo lleva el paquete hasta el HUB;
     *   el hito final de SU ruta es EN_HUB (recepción en HUB), no
     *   ENTREGADO — eso ocurre días después, en otra ruta y con otro
     *   driver.
     * - hub_distribution (Fase 5B-1): el driver saca paquetes del HUB
     *   origen hacia su HUB destino directo (scanHubDeparture, que
     *   deja constancia con un evento EVENT_TRANSFERENCIA ligado a la
     *   parada) y los deja bajo su custodia (EN_TRANSITO_NACIONAL)
     *   hasta registrar la llegada física (scanHubArrival, que libera
     *   driver_id pero NO cambia el estado — la recepción interna es
     *   una operación aparte de Admin). El hito final de ESTA ruta
     *   (la responsabilidad del driver) es la llegada, no la
     *   recepción interna: por eso "pendiente" se define como
     *   "todavía bajo custodia de este driver y en tránsito", el
     *   mismo criterio de siempre, ahora escrito con el patrón
     *   PackageHistory/packageIdsDispatchedOnRoute() en vez de
     *   HubDistributionPhase (que solo mira por driver_id, sin
     *   verificar que el paquete realmente salió de ESTA ruta).
     */
    protected function pendingPackagesCountFor(Route $route): int
    {
        return match ($route->route_type) {
            Route::TYPE_DELIVERY => Package::query()
                ->whereIn('id', $this->packageIdsCollectedOnRoute($route))
                ->where(
                    'current_status',
                    '!=',
                    Package::STATUS_ENTREGADO
                )
                ->count(),

            Route::TYPE_HUB_TRANSFER => Package::query()
                ->whereIn('id', $this->packageIdsCollectedOnRoute($route))
                ->where(
                    'current_status',
                    Package::STATUS_RECOLECTADO_VENEXPRESS
                )
                ->count(),

            Route::TYPE_HUB_DISTRIBUTION => Package::query()
                ->whereIn('id', $this->packageIdsDispatchedOnRoute($route))
                ->where('current_status', Package::STATUS_EN_TRANSITO_NACIONAL)
                ->where('driver_id', $route->driver_id)
                ->count(),

            default => 0,
        };
    }

    /**
     * Mensaje de error mostrado cuando complete() se bloquea por
     * paquetes pendientes, redactado según qué hito le falta a cada
     * route_type (ver pendingPackagesCountFor()).
     */
    protected function pendingPackagesMessage(Route $route, int $count): string
    {
        $suffix = match ($route->route_type) {
            Route::TYPE_HUB_TRANSFER => $count === 1
                ? 'tienes 1 paquete pendiente de recibir en el HUB.'
                : "tienes {$count} paquetes pendientes de recibir en el HUB.",

            Route::TYPE_HUB_DISTRIBUTION => $count === 1
                ? 'tienes 1 paquete pendiente de llegar al almacén destino.'
                : "tienes {$count} paquetes pendientes de llegar al almacén destino.",

            default => $count === 1
                ? 'tienes 1 paquete pendiente de entregar.'
                : "tienes {$count} paquetes pendientes de entregar.",
        };

        return "No puedes finalizar esta ruta: todavía {$suffix}";
    }

    /**
     * Finaliza una ruta.
     *
     * No se permite finalizar si quedan paquetes de ESTA ruta sin
     * llegar a su hito final, que depende de route_type (ver
     * pendingPackagesCountFor()).
     */
    public function complete(
        Route $route,
        int $actingUserId
    ): Route {
        if (! $route->isInProgress()) {
            throw new RuntimeException(
                'Solo se puede finalizar una ruta que está en curso.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $actingUserId
        ) {
            $lockedRoute = Route::query()
                ->whereKey($route->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRoute->isInProgress()) {
                throw new RuntimeException(
                    'Solo se puede finalizar una ruta que está en curso.'
                );
            }

            $pendingCount = $this->pendingPackagesCountFor($lockedRoute);

            if ($pendingCount > 0) {
                throw new RuntimeException(
                    $this->pendingPackagesMessage($lockedRoute, $pendingCount)
                );
            }

            $skipped = $lockedRoute->stops()
                ->where(
                    'status',
                    RouteStop::STATUS_PENDING
                )
                ->count();

            $lockedRoute->stops()
                ->where(
                    'status',
                    RouteStop::STATUS_PENDING
                )
                ->update([
                    'status' => RouteStop::STATUS_SKIPPED,
                ]);

            $lockedRoute->update([
                'status' => Route::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->log(
                $actingUserId,
                'route.completed',
                $lockedRoute,
                "Finalizó la ruta \"{$lockedRoute->name}\" "
                ."({$skipped} paradas quedaron omitidas).",
                [
                    'skipped_stops' => $skipped,
                ]
            );

            return $lockedRoute->fresh('stops');
        });
    }

    /**
     * Cancela una ruta.
     */
    public function cancel(
        Route $route,
        int $actingUserId
    ): Route {
        if ($route->status === Route::STATUS_COMPLETED) {
            throw new RuntimeException(
                'Una ruta completada no se puede cancelar.'
            );
        }

        if ($route->status === Route::STATUS_CANCELLED) {
            throw new RuntimeException(
                'Esta ruta ya está cancelada.'
            );
        }

        $route->update([
            'status' => Route::STATUS_CANCELLED,
        ]);

        $this->log(
            $actingUserId,
            'route.cancelled',
            $route,
            "Canceló la ruta \"{$route->name}\"."
        );

        return $route->fresh();
    }

    /**
     * Cancela una ruta por ID.
     */
    public function cancelRoute(
        int $routeId,
        int $actingUserId
    ): Route {
        return $this->cancel(
            Route::findOrFail($routeId),
            $actingUserId
        );
    }

    /**
     * Duplica una ruta para un nuevo ciclo.
     */
    public function duplicate(
        Route $sourceRoute,
        int $actingUserId,
        ?string $newName = null
    ): Route {
        $locationColumn = $sourceRoute->isHubDistribution()
            ? 'warehouse_id'
            : 'ally_id';

        $locationIds = $sourceRoute->stops()
            ->orderBy('sequence')
            ->pluck($locationColumn)
            ->all();

        $newRoute = $this->createRoute(
            data: [
                'state' => $sourceRoute->state,
                'city' => $sourceRoute->city,
                'route_type' => $sourceRoute->route_type,
                'origin_warehouse_id' => $sourceRoute->origin_warehouse_id,
                'return_warehouse_id' => $sourceRoute->return_warehouse_id,
                'name' => $newName
                    ?? $sourceRoute->name.' (nuevo ciclo)',
            ],
            allyIdsInOrder: $locationIds,
            createdByUserId: $actingUserId,
        );

        $this->log(
            $actingUserId,
            'route.duplicated',
            $newRoute,
            "Creó \"{$newRoute->name}\" como nuevo ciclo "
            ."de \"{$sourceRoute->name}\".",
            [
                'source_route_id' => $sourceRoute->id,
            ]
        );

        return $newRoute;
    }

    /**
     * Duplica una ruta por ID.
     */
    public function duplicateRoute(
        int $routeId,
        int $actingUserId,
        ?string $newName = null
    ): Route {
        return $this->duplicate(
            Route::findOrFail($routeId),
            $actingUserId,
            $newName
        );
    }

    /**
     * Texto descriptivo "en Ciudad, Estado" para el log de auditoría.
     * Desde la Fase 2, state/city son opcionales (ya no restringen
     * las paradas de la ruta), así que puede no haber nada que
     * mostrar — en ese caso no se agrega ningún sufijo.
     */
    protected function locationSuffix(Route $route): string
    {
        $parts = array_filter([$route->city, $route->state]);

        return $parts === [] ? '' : ' en '.implode(', ', $parts);
    }

    /**
     * Registra auditoría.
     */
    protected function log(
        int $actorUserId,
        string $action,
        Route $route,
        string $description,
        array $metadata = []
    ): void {
        AuditLog::create([
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'target_type' => Route::class,
            'target_id' => $route->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
