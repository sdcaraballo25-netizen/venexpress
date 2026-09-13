<?php

namespace App\Livewire\Driver;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.driver')]
class RouteDetail extends Component
{
    public int $routeId;

    public Route $route;

    public function mount(int $routeId): void
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

        // Scoping por driver_id antes del findOrFail impide que un
        // repartidor acceda al detalle de una ruta ajena cambiando el
        // ID en la URL: si la ruta no le pertenece, esto resulta en
        // 404 en vez de exponer datos de otro repartidor.
        $this->route = Route::query()
            ->where('driver_id', $driver->id)
            ->with(['stops.ally', 'stops.warehouse'])
            ->findOrFail($routeId);
    }

    public function render()
    {
        $totalStopsCount = $this->route->stops->count();

        $visitedStopsCount = $this->route->stops
            ->where('status', RouteStop::STATUS_VISITED)
            ->count();

        $pendingStopsCount = max(
            0,
            $totalStopsCount - $visitedStopsCount
        );

        $routeProgress = $totalStopsCount > 0
            ? (int) round(
                ($visitedStopsCount / $totalStopsCount) * 100
            )
            : 0;

        return view(
            'livewire.driver.route-detail',
            [
                'totalStopsCount' => $totalStopsCount,
                'visitedStopsCount' => $visitedStopsCount,
                'pendingStopsCount' => $pendingStopsCount,
                'routeProgress' => $routeProgress,
            ]
        );
    }
}
