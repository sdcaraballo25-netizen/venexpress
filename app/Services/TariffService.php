<?php

namespace App\Services;

use App\Models\RateMatrix;
use RuntimeException;

class TariffService
{
    public function __construct(
        protected BcvRateService $bcvRateService,
    ) {
    }

    /**
     * Peso volumétrico = Largo × Ancho × Alto / 5000.
     * Si faltan dimensiones, se considera 0 (no aplica volumétrico).
     */
    public function calculateVolumetricWeight(?float $lengthCm, ?float $widthCm, ?float $heightCm): float
    {
        if (! $lengthCm || ! $widthCm || ! $heightCm) {
            return 0.0;
        }

        return round(($lengthCm * $widthCm * $heightCm) / 5000, 3);
    }

    /**
     * Peso facturable = MAX(peso físico, peso volumétrico).
     */
    public function calculateBillableWeight(float $physicalWeightKg, float $volumetricWeightKg): float
    {
        return max($physicalWeightKg, $volumetricWeightKg);
    }

    /**
     * Busca la tarifa configurada para una ruta.
     *
     * @throws RuntimeException si la ruta no tiene tarifa configurada.
     */
    public function findRate(string $originCity, string $destinationCity): RateMatrix
    {
        $rate = RateMatrix::forRoute($originCity, $destinationCity);

        if (! $rate) {
            throw new RuntimeException(
                "No existe una tarifa configurada para la ruta {$originCity} → {$destinationCity}."
            );
        }

        return $rate;
    }

    /**
     * Total en USD = precio base + (peso facturable × precio por kg).
     */
    public function calculateTotalUsd(RateMatrix $rateMatrix, float $billableWeightKg): float
    {
        return round(
            (float) $rateMatrix->base_price_usd + ($billableWeightKg * (float) $rateMatrix->price_per_kg_usd),
            2
        );
    }

    /**
     * Calcula todo lo necesario para facturar un paquete: pesos,
     * total en USD/VES y la tasa BCV utilizada.
     *
     * @return array{
     *   volumetric_weight_kg: float,
     *   billable_weight_kg: float,
     *   total_price_usd: float,
     *   total_price_ves: float,
     *   bcv_rate_used: float,
     * }
     */
    public function calculate(
        string $originCity,
        string $destinationCity,
        float $physicalWeightKg,
        ?float $lengthCm = null,
        ?float $widthCm = null,
        ?float $heightCm = null,
    ): array {
        $rateMatrix = $this->findRate($originCity, $destinationCity);
        $bcvRate = $this->bcvRateService->getCurrentRate();

        $volumetricWeight = $this->calculateVolumetricWeight($lengthCm, $widthCm, $heightCm);
        $billableWeight = $this->calculateBillableWeight($physicalWeightKg, $volumetricWeight);
        $totalUsd = $this->calculateTotalUsd($rateMatrix, $billableWeight);
        $totalVes = $this->bcvRateService->convertUsdToVes($totalUsd, $bcvRate);

        return [
            'volumetric_weight_kg' => $volumetricWeight,
            'billable_weight_kg' => $billableWeight,
            'total_price_usd' => $totalUsd,
            'total_price_ves' => $totalVes,
            'bcv_rate_used' => (float) $bcvRate->rate,
        ];
    }
}
