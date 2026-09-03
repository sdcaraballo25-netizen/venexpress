<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use App\Services\AllyFinancialService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.ally')]
class Dashboard extends Component
{
    public string $period = 'today';

    public function setPeriod(
        string $period
    ): void {
        if (
            in_array(
                $period,
                [
                    'today',
                    'week',
                    'month',
                ],
                true
            )
        ) {
            $this->period = $period;
        }
    }

    public function render(
        AllyFinancialService $financialService
    ) {
        $ally =
            auth()->user()->resolveAlly();

        if (! $ally) {
            abort(
                403,
                'Tu usuario no tiene una agencia aliada asociada.'
            );
        }

        [
            $from,
            $to,
        ] = $this->dateRangeForPeriod();

        $baseQuery = Package::query()
            ->where(
                'ally_id',
                $ally->id
            )
            ->whereBetween(
                'created_at',
                [
                    $from,
                    $to,
                ]
            );

        $totalBilledUsd =
            (clone $baseQuery)
                ->sum(
                    'total_price_usd'
                );

        $processedCount =
            (clone $baseQuery)
                ->count();

        /*
         * Este valor sí representa el saldo real acumulado.
         */
        $totalCommissionBalanceUsd =
            $financialService->getBalance(
                $ally->id
            );

        $totalGeneratedCommissionUsd =
            $financialService->getGeneratedCommission(
                $ally->id
            );

        $totalPaidCommissionUsd =
            $financialService->getPaidAmount(
                $ally->id
            );

        $commissionBalanceUsd =
            (clone $baseQuery)
                ->sum(
                    'commission_amount_usd'
                );

        $byPaymentMethod =
            (clone $baseQuery)
                ->whereNotNull(
                    'payment_method'
                )
                ->selectRaw(
                    'payment_method,
                     SUM(total_price_usd) as total,
                     COUNT(*) as guides'
                )
                ->groupBy(
                    'payment_method'
                )
                ->get();

        $codPendingUsd =
            (clone $baseQuery)
                ->where(
                    'is_cod',
                    true
                )
                ->where(
                    'cod_status',
                    Package::COD_PENDIENTE
                )
                ->sum(
                    'cod_amount_usd'
                );

        $codLiquidatedUsd =
            (clone $baseQuery)
                ->where(
                    'is_cod',
                    true
                )
                ->where(
                    'cod_status',
                    Package::COD_LIQUIDADO
                )
                ->sum(
                    'cod_amount_usd'
                );

        return view(
            'livewire.ally.dashboard',
            [
                'ally' =>
                    $ally,

                'totalBilledUsd' =>
                    $totalBilledUsd,

                'processedCount' =>
                    $processedCount,

                'commissionBalanceUsd' =>
                    $commissionBalanceUsd,

                'totalCommissionBalanceUsd' =>
                    $totalCommissionBalanceUsd,

                'totalGeneratedCommissionUsd' =>
                    $totalGeneratedCommissionUsd,

                'totalPaidCommissionUsd' =>
                    $totalPaidCommissionUsd,

                'byPaymentMethod' =>
                    $byPaymentMethod,

                'codPendingUsd' =>
                    $codPendingUsd,

                'codLiquidatedUsd' =>
                    $codLiquidatedUsd,
            ]
        );
    }

    /**
     * @return array{
     *     0: Carbon,
     *     1: Carbon
     * }
     */
    protected function dateRangeForPeriod(): array
    {
        return match ($this->period) {
            'week' => [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ],

            'month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],

            default => [
                now()->startOfDay(),
                now()->endOfDay(),
            ],
        };
    }
}
