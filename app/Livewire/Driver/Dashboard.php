<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.driver')]
class Dashboard extends Component
{
    /**
     * Inicia la ruta asignada al repartidor.
     */
    public function startRoute(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_ASSIGNED)
            ->latest('created_at')
            ->first();

        if (! $route) {
            session()->flash(
                'routeError',
                'No tienes una ruta asignada pendiente de iniciar.'
            );

            return;
        }

        app(RouteService::class)->start(
            route: $route,
            actingUserId: (int) $user->id,
        );

        session()->flash(
            'routeSuccess',
            'Ruta iniciada correctamente. Ya puedes comenzar a escanear paquetes.'
        );
    }

    /**
     * El repartidor toma una ruta disponible (draft, sin dueño,
     * compatible con su driver_type). Reutiliza
     * RouteService::claimRoute() tal cual — misma validación y
     * protección de concurrencia que ya usa la API del repartidor.
     */
    public function claimRoute(int $routeId): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $route = Route::findOrFail($routeId);

        try {
            app(RouteService::class)->claimRoute(
                route: $route,
                driver: $driver,
                actingUserId: (int) $user->id,
            );

            session()->flash(
                'routeSuccess',
                '¡Ruta tomada! Ya puedes iniciarla cuando estés listo.'
            );
        } catch (\RuntimeException $e) {
            session()->flash('routeError', $e->getMessage());
        }
    }

    /**
     * Finaliza la ruta en curso del repartidor. Reutiliza
     * RouteService::complete() tal cual — el mismo método que ya
     * expone la API del repartidor (POST /api/driver/route/complete),
     * ahora también disponible desde el panel web.
     */
    public function completeRoute(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->latest('created_at')
            ->first();

        if (! $route) {
            session()->flash(
                'routeError',
                'No tienes una ruta en curso para finalizar.'
            );

            return;
        }

        try {
            app(RouteService::class)->complete(
                route: $route,
                actingUserId: (int) $user->id,
            );

            session()->flash(
                'routeSuccess',
                'Ruta finalizada correctamente.'
            );
        } catch (RuntimeException $e) {
            session()->flash('routeError', $e->getMessage());
        }
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $isHub = $driver->driver_type === Driver::TYPE_HUB;

        /*
        |--------------------------------------------------------------------------
        | PAQUETES DEL REPARTIDOR
        |--------------------------------------------------------------------------
        */

        $baseQuery = Package::query()
            ->where('driver_id', $driver->id);

        // Total de paquetes que han sido asignados al repartidor.
        $assignedCount = (clone $baseQuery)->count();

        // Paquetes que todavía no han sido entregados.
        $pendingCount = (clone $baseQuery)
            ->where(
                'current_status',
                '!=',
                Package::STATUS_ENTREGADO
            )
            ->count();

        // Paquetes que ya salieron de la agencia y están bajo gestión
        // logística del repartidor.
        $collectedCount = (clone $baseQuery)
            ->whereIn('current_status', [
                Package::STATUS_RECOLECTADO_VENEXPRESS,
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ])
            ->count();

        // Paquetes entregados.
        $deliveredCount = (clone $baseQuery)
            ->where(
                'current_status',
                Package::STATUS_ENTREGADO
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | PAQUETES PENDIENTES
        |--------------------------------------------------------------------------
        */

        $pendingPackages = Package::query()
            ->where('driver_id', $driver->id)
            ->where(
                'current_status',
                '!=',
                Package::STATUS_ENTREGADO
            )
            ->orderByDesc('distance_km')
            ->orderBy('id')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | ENTREGAS RECIENTES
        |--------------------------------------------------------------------------
        */

        $recentDeliveries = Package::query()
            ->where('driver_id', $driver->id)
            ->where(
                'current_status',
                Package::STATUS_ENTREGADO
            )
            ->latest('delivery_completed_at')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | RUTA ACTIVA
        |--------------------------------------------------------------------------
        */

        $activeRoute = Route::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Route::STATUS_ASSIGNED,
                Route::STATUS_IN_PROGRESS,
            ])
            ->with(['stops.ally', 'stops.warehouse'])
            ->latest('created_at')
            ->first();

        $availableRoutes = $activeRoute
            ? collect()
            : app(RouteService::class)->availableRoutesFor($driver);

        $routeStopsCount = $activeRoute?->stops->count() ?? 0;

        $visitedStopsCount = $activeRoute?->stops
            ->where(
                'status',
                RouteStop::STATUS_VISITED
            )
            ->count() ?? 0;

        $pendingStopsCount = max(
            0,
            $routeStopsCount - $visitedStopsCount
        );

        $routeProgress = $routeStopsCount > 0
            ? (int) round(
                ($visitedStopsCount / $routeStopsCount) * 100
            )
            : 0;

        // Siguiente parada pendiente, en orden de secuencia. Se toma
        // de la colección ya cargada por 'stops.ally'/'stops.warehouse'
        // en vez de Route::nextPendingStop() para no disparar una
        // consulta adicional.
        $nextPendingStop = $activeRoute?->stops
            ->firstWhere('status', RouteStop::STATUS_PENDING);

        // Paquetes ya procesados en las paradas de la ruta activa
        // (dato que RouteService ya registra por parada al visitarla).
        $routePackagesProcessed = $activeRoute?->stops
            ->sum('packages_collected_count') ?? 0;

        /*
        |--------------------------------------------------------------------------
        | RESUMEN DEL DÍA
        |--------------------------------------------------------------------------
        */

        $deliveredTodayCount = Package::query()
            ->where('driver_id', $driver->id)
            ->where(
                'current_status',
                Package::STATUS_ENTREGADO
            )
            ->whereDate(
                'delivery_completed_at',
                today()
            )
            ->count();

        return view(
            'livewire.driver.dashboard',
            [
                'driver' => $driver,
                'isHub' => $isHub,

                // Paquetes
                'assignedCount' => $assignedCount,
                'pendingCount' => $pendingCount,
                'collectedCount' => $collectedCount,
                'deliveredCount' => $deliveredCount,
                'deliveredTodayCount' => $deliveredTodayCount,

                // Listados
                'pendingPackages' => $pendingPackages,
                'recentDeliveries' => $recentDeliveries,

                // Ruta
                'activeRoute' => $activeRoute,
                'availableRoutes' => $availableRoutes,
                'routeStopsCount' => $routeStopsCount,
                'visitedStopsCount' => $visitedStopsCount,
                'pendingStopsCount' => $pendingStopsCount,
                'routeProgress' => $routeProgress,
                'nextPendingStop' => $nextPendingStop,
                'routePackagesProcessed' => $routePackagesProcessed,
            ]
        );
    }
}
