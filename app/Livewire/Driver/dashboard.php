<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $period = 'today';

    public function setPeriod(string $period): void
    {
        if (!in_array($period, ['today', 'week', 'month'], true)) {
            return;
        }

        $this->period = $period;
    }

    public function render()
    {
        $driver = auth()->user()->driver;

        if (!$driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        [$from, $to] = match ($this->period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $base = Package::query()
            ->where('driver_id', $driver->id)
            ->whereBetween('updated_at', [$from, $to]);

        $assignedCount = (clone $base)->count();

        $pendingCount = (clone $base)
            ->whereIn('current_status', [
                Package::STATUS_RECOLECTADO_VENEXPRESS,
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ])
            ->where('current_status', '!=', Package::STATUS_ENTREGADO)
            ->count();

        $deliveredCount = (clone $base)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->count();

        $pendingPackages = Package::query()
            ->where('driver_id', $driver->id)
            ->whereIn('current_status', [
                Package::STATUS_RECOLECTADO_VENEXPRESS,
                Package::STATUS_LISTO_RETIRO,
                Package::STATUS_EN_TRANSITO_NACIONAL,
            ])
            ->with('ally')
            ->orderByRaw(
                "CASE current_status
                    WHEN ? THEN 1
                    WHEN ? THEN 2
                    WHEN ? THEN 3
                    ELSE 4 END",
                [
                    Package::STATUS_LISTO_RETIRO,
                    Package::STATUS_EN_TRANSITO_NACIONAL,
                    Package::STATUS_RECOLECTADO_VENEXPRESS,
                ]
            )
            ->latest('updated_at')
            ->take(6)
            ->get();

        $recentDeliveries = Package::query()
            ->where('driver_id', $driver->id)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->latest('delivery_completed_at')
            ->take(5)
            ->get();

        $nextPackage = $pendingPackages->first();

        $delivery = $nextPackage;

        return view('livewire.driver.dashboard', [
            'assignedCount' => $assignedCount,
            'pendingCount' => $pendingCount,
            'deliveredCount' => $deliveredCount,
            'pendingPackages' => $pendingPackages,
            'recentDeliveries' => $recentDeliveries,
            'nextPackage' => $nextPackage,
            'delivery' => $delivery,
        ]);
    }
}
