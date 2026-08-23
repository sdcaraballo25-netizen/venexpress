<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard de Rutas')]
class RoutesDashboard extends Component
{
    public function render()
    {
        $activeRoutes = Route::with('stops')
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->get();

        $stopsQuery = RouteStop::whereIn('route_id', $activeRoutes->pluck('id'));

        $metrics = [
            'active_routes' => $activeRoutes->count(),
            'completed_routes_today' => Route::where('status', Route::STATUS_COMPLETED)
                ->whereDate('completed_at', today())->count(),
            'pending_routes' => Route::whereIn('status', [Route::STATUS_DRAFT, Route::STATUS_ASSIGNED])->count(),
            'collections_completed_today' => Package::where('current_status', Package::STATUS_RECOLECTADO_VENEXPRESS)
                ->whereDate('updated_at', today())->count(),
            'collections_pending' => Package::where('current_status', Package::STATUS_RECIBIDO_AGENCIA)->count(),
            'allies_visited' => (clone $stopsQuery)->where('status', RouteStop::STATUS_VISITED)->count(),
            'allies_pending' => (clone $stopsQuery)->where('status', RouteStop::STATUS_PENDING)->count(),
            'drivers_on_route' => $activeRoutes->pluck('driver_id')->unique()->count(),
        ];

        // Rutas activas sin ninguna parada actualizada en los últimos 30 min: posible ruta estancada.
        $stalledRoutes = $activeRoutes->filter(function (Route $route) {
            $lastActivity = $route->stops->max('visited_at') ?? $route->started_at;

            return $lastActivity && now()->diffInMinutes($lastActivity) > 30;
        });

        // % de cumplimiento de las últimas 10 rutas completadas.
        $completionRate = Route::with('stops')
            ->where('status', Route::STATUS_COMPLETED)
            ->latest('completed_at')
            ->take(10)
            ->get()
            ->map(fn (Route $r) => $r->stops->count()
                ? round($r->visitedStopsCount() / $r->stops->count() * 100)
                : null)
            ->filter()
            ->avg();

        return view('livewire.admin.routes-dashboard', [
            'metrics' => $metrics,
            'activeRoutes' => $activeRoutes,
            'stalledRoutes' => $stalledRoutes,
            'completionRate' => $completionRate ? round($completionRate) : null,
        ]);
    }
}
