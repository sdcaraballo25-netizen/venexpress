<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.driver')]
class Dashboard extends Component
{
    /**
     * Inicia la ruta asignada al repartidor.
     */
    public function startRoute(): void
    {
        /** @var \App\Models\User|null $user */
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

        $route->update([
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        session()->flash(
            'routeSuccess',
            'Ruta iniciada correctamente. Ya puedes comenzar a escanear paquetes.'
        );
    }

    public function render()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

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
            ->with('stops')
            ->latest('created_at')
            ->first();

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
                'routeStopsCount' => $routeStopsCount,
                'visitedStopsCount' => $visitedStopsCount,
                'pendingStopsCount' => $pendingStopsCount,
                'routeProgress' => $routeProgress,
            ]
        );
    }
}