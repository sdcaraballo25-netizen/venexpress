<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    /**
     * Período del resumen: 'today', 'week' o 'month'.
     */
    public string $period = 'today';

    public function setPeriod(string $period): void
    {
        if (in_array($period, ['today', 'week', 'month'], true)) {
            $this->period = $period;
        }
    }

    public function render()
    {
        $driver = auth()->user()->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        [$from, $to] = $this->dateRangeForPeriod();

        $baseQuery = Package::query()->where('driver_id', $driver->id);

        // Asignados y entregados: acotados al período seleccionado.
        $assignedCount = (clone $baseQuery)
            ->whereBetween('created_at', [$from, $to])
            ->count();

        $deliveredCount = (clone $baseQuery)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereBetween('updated_at', [$from, $to])
            ->count();

        // Pendientes: backlog actual real, sin acotar por período,
        // para que el repartidor siempre vea lo que le falta por entregar.
        $pendingPackages = (clone $baseQuery)
            ->where('current_status', '!=', Package::STATUS_ENTREGADO)
            ->orderBy('created_at')
            ->get();

        $pendingCount = $pendingPackages->count();

        $recentDeliveries = (clone $baseQuery)
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('Livewire.driver.dashboard', [
            'assignedCount' => $assignedCount,
            'pendingCount' => $pendingCount,
            'deliveredCount' => $deliveredCount,
            'pendingPackages' => $pendingPackages,
            'recentDeliveries' => $recentDeliveries,
        ]);
    }

    /**
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    protected function dateRangeForPeriod(): array
    {
        return match ($this->period) {
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };
    }
}
