<?php

namespace App\Livewire\Driver;

use App\Models\Route;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.driver')]
class RouteHistory extends Component
{
    use WithPagination;

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

        /*
         * Rutas que el repartidor tiene o tuvo asignadas, en cualquiera
         * de los estados que reflejan que ya la tomó: assigned,
         * in_progress, completed, cancelled. Una ruta liberada antes
         * de iniciarla vuelve a draft con driver_id null (ver
         * RouteService::release()) y por eso deja de pertenecerle —
         * no se resuelve aquí, es el comportamiento actual del modelo.
         */
        $routes = Route::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', [
                Route::STATUS_ASSIGNED,
                Route::STATUS_IN_PROGRESS,
                Route::STATUS_COMPLETED,
                Route::STATUS_CANCELLED,
            ])
            ->with(['stops.ally', 'stops.warehouse', 'originWarehouse', 'returnWarehouse'])
            ->latest('created_at')
            ->paginate(10);

        return view(
            'livewire.driver.route-history',
            [
                'routes' => $routes,
            ]
        );
    }
}
