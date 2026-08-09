<?php

namespace App\Services;

use App\Models\BcvRate;
use Carbon\Carbon;
use RuntimeException;

class BcvRateService
{
    /**
     * Obtiene la tasa BCV vigente más reciente.
     *
     * @throws RuntimeException si no hay ninguna tasa registrada.
     */
    public function getCurrentRate(): BcvRate
    {
        $rate = BcvRate::current();

        if (! $rate) {
            throw new RuntimeException('No hay ninguna tasa BCV registrada todavía.');
        }

        return $rate;
    }

    /**
     * Registra (o actualiza) la tasa BCV para una fecha determinada.
     * Por defecto usa la fecha de hoy.
     */
    public function setRate(float $rate, ?Carbon $effectiveDate = null): BcvRate
    {
        $effectiveDate ??= Carbon::today();

        return BcvRate::updateOrCreate(
            ['effective_date' => $effectiveDate->toDateString()],
            ['rate' => $rate],
        );
    }

    /**
     * Convierte un monto en USD a VES usando la tasa indicada,
     * o la tasa vigente si no se pasa ninguna.
     */
    public function convertUsdToVes(float $usdAmount, ?BcvRate $rate = null): float
    {
        $rate ??= $this->getCurrentRate();

        return round($usdAmount * (float) $rate->rate, 2);
    }
}
