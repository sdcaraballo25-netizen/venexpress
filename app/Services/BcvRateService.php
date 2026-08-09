<?php

namespace App\Services;

use App\Models\BcvRate;

class BcvRateService
{
    /**
     * Obtiene la tasa BCV vigente.
     */
    public function current(): ?BcvRate
    {
        return BcvRate::query()
            ->orderByDesc('effective_date')
            ->first();
    }

    /**
     * Obtiene únicamente el valor de la tasa vigente.
     */
    public function currentRate(): ?float
    {
        $rate = $this->current();

        return $rate
            ? (float) $rate->rate
            : null;
    }

    /**
     * Convierte un monto en USD a VES.
     */
    public function convertUsdToVes(
        float $usd,
        ?float $rate = null
    ): float {
        $rate ??= $this->currentRate();

        if (!$rate || $rate <= 0) {
            throw new \RuntimeException(
                'No existe una tasa BCV válida configurada.'
            );
        }

        return round($usd * $rate, 2);
    }
}