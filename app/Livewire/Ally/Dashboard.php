<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use Livewire\Component;
use Livewire\Attributes\Layout;

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
        $ally = auth()->user()->resolveAlly();

        if (! $ally) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }

        [$from, $to] = $this->dateRangeForPeriod();

        $baseQuery = Package::query()
            ->where('ally_id', $ally->id)
            ->whereBetween('created_at', [$from, $to]);

        $totalBilledUsd = (clone $baseQuery)->sum('total_price_usd');
        $processedCount = (clone $baseQuery)->count();
        $commissionBalanceUsd = (clone $baseQuery)->sum('commission_amount_usd');

        $byPaymentMethod = (clone $baseQuery)
            ->whereNotNull('payment_method')
            ->selectRaw('payment_method, SUM(total_price_usd) as total, COUNT(*) as guides')
            ->groupBy('payment_method')
            ->get();

        $codPendingUsd = (clone $baseQuery)
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_PENDIENTE)
            ->sum('cod_amount_usd');

        $codLiquidatedUsd = (clone $baseQuery)
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_LIQUIDADO)
            ->sum('cod_amount_usd');

        // Saldo acumulado histórico por comisiones (no limitado al período,
        // para que RF-ALI-08 muestre el saldo total disponible).
        $totalCommissionBalanceUsd = Package::query()
            ->where('ally_id', $ally->id)
            ->sum('commission_amount_usd');

        return view('Livewire.ally.dashboard', [
            'ally' => $ally,
            'totalBilledUsd' => $totalBilledUsd,
            'processedCount' => $processedCount,
            'commissionBalanceUsd' => $commissionBalanceUsd,
            'totalCommissionBalanceUsd' => $totalCommissionBalanceUsd,
            'byPaymentMethod' => $byPaymentMethod,
            'codPendingUsd' => $codPendingUsd,
            'codLiquidatedUsd' => $codLiquidatedUsd,
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
