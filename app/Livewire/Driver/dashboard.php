<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.driver')]
class Dashboard extends Component
{
    public function render()
    {
       $user = \Illuminate\Support\Facades\Auth::user();

$driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $baseQuery = Package::query()
            ->where('driver_id', $driver->id);

        $assignedCount = (clone $baseQuery)->count();

        $pendingCount = (clone $baseQuery)
            ->whereIn('current_status', [
                Package::STATUS_RECIBIDO_AGENCIA,
                Package::STATUS_RECOLECTADO_VENEXPRESS,
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ])
            ->count();

        $deliveredCount = (clone $baseQuery)
            ->where(
                'current_status',
                Package::STATUS_ENTREGADO
            )
            ->count();

        $pendingPackages = Package::query()
            ->where('driver_id', $driver->id)
            ->whereIn('current_status', [
                Package::STATUS_RECIBIDO_AGENCIA,
                Package::STATUS_RECOLECTADO_VENEXPRESS,
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ])
            ->orderByDesc('distance_km')
            ->orderBy('id')
            ->limit(10)
            ->get();

        $recentDeliveries = Package::query()
            ->where('driver_id', $driver->id)
            ->where(
                'current_status',
                Package::STATUS_ENTREGADO
            )
            ->latest('delivery_completed_at')
            ->limit(10)
            ->get();

        return view(
            'livewire.driver.dashboard',
            [
                'driver' => $driver,
                'assignedCount' => $assignedCount,
                'pendingCount' => $pendingCount,
                'deliveredCount' => $deliveredCount,
                'pendingPackages' => $pendingPackages,
                'recentDeliveries' => $recentDeliveries,
            ]
        );
    }
}
