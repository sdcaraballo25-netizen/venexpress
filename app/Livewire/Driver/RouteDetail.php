<?php

namespace App\Livewire\Driver;

use App\Livewire\Driver\Support\HubDistributionPhase;
use App\Models\Driver;
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

        /*
        |--------------------------------------------------------------------------
        | ACCIÓN DE ESCANEO PRINCIPAL (HUB)
        |--------------------------------------------------------------------------
        |
        | Mismo criterio que Dashboard.php y Scanner.php (vía
        | HubDistributionPhase), para que las tres pantallas siempre
        | muestren la misma acción a un driver de HUB.
        */

        $hubScanOperation = null;
        $hubScanPendingCount = null;
        $hubScanWarehouseName = null;

        $nextPendingStop = $this->route->stops
            ->firstWhere('status', RouteStop::STATUS_PENDING);

        if ($this->route->isInProgress()) {
            /** @var User|null $user */
            $user = Auth::user();

            /** @var Driver|null $driver */
            $driver = $user?->driver;

            if ($driver && $this->route->route_type === Route::TYPE_HUB_TRANSFER) {
                $hubScanOperation = 'collection';
            } elseif ($driver && $this->route->route_type === Route::TYPE_HUB_DISTRIBUTION) {
                $hubScanOperation = HubDistributionPhase::resolve($driver);

                if ($hubScanOperation === HubDistributionPhase::ARRIVAL) {
                    $hubScanPendingCount = HubDistributionPhase::pendingArrivalsCount($driver);
                    $hubScanWarehouseName = $nextPendingStop?->warehouse?->name;
                } else {
                    $hubScanPendingCount = HubDistributionPhase::pendingDepartureCount($this->route);
                }
            }
        }

        return view(
            'livewire.driver.route-detail',
            [
                'totalStopsCount' => $totalStopsCount,
                'visitedStopsCount' => $visitedStopsCount,
                'pendingStopsCount' => $pendingStopsCount,
                'routeProgress' => $routeProgress,
                'nextPendingStop' => $nextPendingStop,
                'hubScanOperation' => $hubScanOperation,
                'hubScanPendingCount' => $hubScanPendingCount,
                'hubScanWarehouseName' => $hubScanWarehouseName,
            ]
        );
    }
}
