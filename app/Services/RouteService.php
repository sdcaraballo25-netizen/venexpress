<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RouteService
{
    public function __construct(
        protected PackageService $packageService,
    ) {
    }

    public function createRoute(
        array $data,
        array $allyIdsInOrder,
        int $createdByUserId
    ): Route {
        if (count($allyIdsInOrder) === 0) {
            throw new RuntimeException('Una ruta necesita al menos una agencia.');
        }

        return DB::transaction(function () use (
            $data,
            $allyIdsInOrder,
            $createdByUserId
        ) {
            $route = Route::create([
                'state' => $data['state'] ?? null,
                'city' => $data['city'],
                'name' => $data['name'],
                'created_by' => $createdByUserId,
                'status' => Route::STATUS_DRAFT,
            ]);

            $this->syncStops($route, $allyIdsInOrder);

            $this->log(
                $createdByUserId,
                'route.created',
                $route,
                "Creó la ruta \"{$route->name}\" en {$route->city}, {$route->state} con "
                . count($allyIdsInOrder)
                . ' paradas.',
                [
                    'state' => $route->state,
                    'city' => $route->city,
                    'stops' => count($allyIdsInOrder),
                ]
            );

            return $route->fresh('stops');
        });
    }

    public function updateRoute(
        int $routeId,
        string $name,
        string $state,
        string $city,
        array $allyIds,
        int $actingUserId
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
            $actingUserId
        ) {
            $route->update([
                'name' => $name,
                'state' => $state,
                'city' => $city,
            ]);

            $route->stops()->delete();

            $this->syncStops($route, $allyIds);

            $this->log(
                $actingUserId,
                'route.updated',
                $route,
                "Actualizó la ruta \"{$route->name}\".",
                [
                    'state' => $route->state,
                    'city' => $route->city,
                    'stops' => count($allyIds),
                ]
            );

            return $route->fresh('stops');
        });
    }

    public function updateStops(
        Route $route,
        array $allyIdsInOrder,
        int $actingUserId
    ): Route {
        if (! $route->isEditable()) {
            throw new RuntimeException(
                'Esta ruta ya está en curso o finalizada; no se puede editar su recorrido.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $allyIdsInOrder,
            $actingUserId
        ) {
            $route->stops()->delete();

            $this->syncStops($route, $allyIdsInOrder);

            $this->log(
                $actingUserId,
                'route.stops_updated',
                $route,
                "Actualizó el recorrido de \"{$route->name}\" ("
                . count($allyIdsInOrder)
                . ' paradas).',
                [
                    'stops' => count($allyIdsInOrder),
                ]
            );

            return $route->fresh('stops');
        });
    }

    protected function syncStops(
        Route $route,
        array $allyIdsInOrder
    ): void {
        foreach (array_values($allyIdsInOrder) as $index => $allyId) {
            RouteStop::create([
                'route_id' => $route->id,
                'ally_id' => $allyId,
                'sequence' => $index + 1,
                'status' => RouteStop::STATUS_PENDING,
            ]);
        }
    }

    public function assignDriver(
        Route $route,
        Driver $driver,
        int $actingUserId
    ): Route {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo se pueden asignar repartidores activos.'
            );
        }

        $route->update([
            'driver_id' => $driver->id,
            'status' => Route::STATUS_ASSIGNED,
        ]);

        $this->log(
            $actingUserId,
            'route.driver_assigned',
            $route,
            "Asignó a {$driver->user->name} ({$driver->vehicle_plate}) "
            . "a la ruta \"{$route->name}\".",
            [
                'driver_id' => $driver->id,
            ]
        );

        return $route->fresh();
    }

    public function assignDriverById(
        int $routeId,
        int $driverId,
        int $actingUserId
    ): Route {
        $route = Route::findOrFail($routeId);
        $driver = Driver::findOrFail($driverId);

        return $this->assignDriver(
            $route,
            $driver,
            $actingUserId
        );
    }

    public function start(
        Route $route,
        int $actingUserId
    ): Route {
        if (! $route->driver_id) {
            throw new RuntimeException(
                'La ruta necesita un repartidor asignado antes de iniciar.'
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

    public function startRoute(
        int $routeId,
        int $actingUserId
    ): Route {
        return $this->start(
            Route::findOrFail($routeId),
            $actingUserId
        );
    }

    public function collectiblePackagesFor(
        RouteStop $stop
    ): Collection {
        return Package::query()
            ->where('ally_id', $stop->ally_id)
            ->where(
                'current_status',
                Package::STATUS_RECIBIDO_AGENCIA
            )
            ->orderBy('created_at')
            ->get();
    }

    public function registerCollection(
        Route $route,
        RouteStop $stop,
        array $packageIds,
        int $actingUserId
    ): RouteStop {
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

        if ($stop->status !== RouteStop::STATUS_PENDING) {
            throw new RuntimeException(
                'Esta parada ya fue marcada como visitada u omitida.'
            );
        }

        return DB::transaction(function () use (
            $route,
            $stop,
            $packageIds,
            $actingUserId
        ) {
            $packages = Package::query()
                ->whereIn('id', $packageIds)
                ->where('ally_id', $stop->ally_id)
                ->where(
                    'current_status',
                    Package::STATUS_RECIBIDO_AGENCIA
                )
                ->get();

            foreach ($packages as $package) {
                $this->packageService->changeStatus(
                    package: $package,
                    newStatus: Package::STATUS_RECOLECTADO_VENEXPRESS,
                    userId: $actingUserId,
                    locationDescription:
                        "Recolectado en {$package->ally->business_name}",
                    routeStopId: $stop->id,
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
                "Registró la recolección en "
                . "{$stop->ally->business_name} ("
                . $packages->count()
                . " paquetes) — ruta \"{$route->name}\".",
                [
                    'route_stop_id' => $stop->id,
                    'ally_id' => $stop->ally_id,
                    'packages_collected' => $packages->count(),
                ]
            );

            return $stop->fresh();
        });
    }

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
            $skipped = $route->stops()
                ->where(
                    'status',
                    RouteStop::STATUS_PENDING
                )
                ->count();

            $route->stops()
                ->where(
                    'status',
                    RouteStop::STATUS_PENDING
                )
                ->update([
                    'status' => RouteStop::STATUS_SKIPPED,
                ]);

            $route->update([
                'status' => Route::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->log(
                $actingUserId,
                'route.completed',
                $route,
                "Finalizó la ruta \"{$route->name}\" "
                . "({$skipped} paradas quedaron omitidas).",
                [
                    'skipped_stops' => $skipped,
                ]
            );

            return $route->fresh('stops');
        });
    }

    public function completeRoute(
        int $routeId,
        int $actingUserId
    ): Route {
        return $this->complete(
            Route::findOrFail($routeId),
            $actingUserId
        );
    }

    public function cancel(
        Route $route,
        int $actingUserId
    ): Route {
        if ($route->status === Route::STATUS_COMPLETED) {
            throw new RuntimeException(
                'Una ruta completada no se puede cancelar.'
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

    public function cancelRoute(
        int $routeId,
        int $actingUserId
    ): Route {
        return $this->cancel(
            Route::findOrFail($routeId),
            $actingUserId
        );
    }

    public function duplicate(
        Route $sourceRoute,
        int $actingUserId,
        ?string $newName = null
    ): Route {
        $allyIds = $sourceRoute->stops()
            ->orderBy('sequence')
            ->pluck('ally_id')
            ->all();

        $newRoute = $this->createRoute(
            data: [
                'state' => $sourceRoute->state,
                'city' => $sourceRoute->city,
                'name' => $newName
                    ?? $sourceRoute->name . ' (nuevo ciclo)',
            ],
            allyIdsInOrder: $allyIds,
            createdByUserId: $actingUserId,
        );

        $this->log(
            $actingUserId,
            'route.duplicated',
            $newRoute,
            "Creó \"{$newRoute->name}\" como nuevo ciclo "
            . "de \"{$sourceRoute->name}\".",
            [
                'source_route_id' => $sourceRoute->id,
            ]
        );

        return $newRoute;
    }

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
