<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.ally')]
class Commissions extends Component
{
    public function mount(): void
    {
        if (! auth()->user()->resolveAlly()) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }
    }

    /**
     * Resumen de los últimos 6 meses (incluyendo el actual), para dar
     * contexto de tendencia sin necesidad de una gráfica.
     *
     * @return array<int, array{label: string, packages: int, commission_usd: float}>
     */
    protected function monthlyBreakdown(int $allyId): array
    {
        return collect(range(0, 5))
            ->map(function (int $i) use ($allyId) {
                $start = Carbon::now()->startOfMonth()->subMonths($i);
                $end = (clone $start)->endOfMonth();

                $query = Package::where('ally_id', $allyId)
                    ->whereBetween('created_at', [$start, $end]);

                return [
                    'label' => $start->locale('es')->translatedFormat('M Y'),
                    'packages' => (clone $query)->count(),
                    'commission_usd' => (float) (clone $query)->sum('commission_amount_usd'),
                ];
            })
            ->reverse()
            ->values()
            ->all();
    }

    public function render()
    {
        $ally = auth()->user()->resolveAlly();

        $totalPackages = Package::where('ally_id', $ally->id)->count();
        $totalCommissionUsd = (float) Package::where('ally_id', $ally->id)->sum('commission_amount_usd');

        $monthStart = Carbon::now()->startOfMonth();
        $monthPackages = Package::where('ally_id', $ally->id)
            ->where('created_at', '>=', $monthStart)
            ->count();
        $monthCommissionUsd = (float) Package::where('ally_id', $ally->id)
            ->where('created_at', '>=', $monthStart)
            ->sum('commission_amount_usd');

        $averageCommissionUsd = $totalPackages > 0
            ? $totalCommissionUsd / $totalPackages
            : 0.0;

        return view('livewire.ally.commissions', [
            'ally' => $ally,
            'totalPackages' => $totalPackages,
            'totalCommissionUsd' => $totalCommissionUsd,
            'monthPackages' => $monthPackages,
            'monthCommissionUsd' => $monthCommissionUsd,
            'averageCommissionUsd' => $averageCommissionUsd,
            'monthlyBreakdown' => $this->monthlyBreakdown($ally->id),
        ]);
    }
}
