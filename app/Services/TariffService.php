<?php

namespace App\Services;

use App\Models\CityDistance;
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
     * Obtiene la tarifa global vigente (base + por kg + por km).
     *
     * @throws RuntimeException si todavía no se ha configurado ninguna tarifa.
     */
    public function findRate(): RateMatrix
    {
        $rate = RateMatrix::current();

        if (! $rate) {
            throw new RuntimeException('No hay ninguna tarifa configurada todavía.');
        }

        return $rate;
    }

    /**
     * Busca la distancia registrada entre dos ciudades.
     *
     * @throws RuntimeException si la ruta no tiene distancia configurada.
     */
    public function findDistance(string $originCity, string $destinationCity): CityDistance
    {
        $distance = CityDistance::between($originCity, $destinationCity);

        if (! $distance) {
            throw new RuntimeException(
                "No existe una distancia configurada para la ruta {$originCity} → {$destinationCity}."
            );
        }

        return $distance;
    }

    /**
     * Total en USD = precio base
     *              + (peso facturable × precio por kg)
     *              + (distancia en km × precio por km).
     */
    public function calculateTotalUsd(RateMatrix $rateMatrix, float $billableWeightKg, int $distanceKm): float
    {
        return round(
            (float) $rateMatrix->base_price_usd
                + ($billableWeightKg * (float) $rateMatrix->price_per_kg_usd)
                + ($distanceKm * (float) $rateMatrix->price_per_km_usd),
            2
        );
    }

    /**
     * Calcula todo lo necesario para facturar un paquete: pesos,
     * distancia, total en USD/VES y la tasa BCV utilizada.
     *
     * @return array{
     *   volumetric_weight_kg: float,
     *   billable_weight_kg: float,
     *   distance_km: int,
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
        $rateMatrix = $this->findRate();
        $cityDistance = $this->findDistance($originCity, $destinationCity);
        $bcvRate = $this->bcvRateService->getCurrentRate();

        $volumetricWeight = $this->calculateVolumetricWeight($lengthCm, $widthCm, $heightCm);
        $billableWeight = $this->calculateBillableWeight($physicalWeightKg, $volumetricWeight);
        $totalUsd = $this->calculateTotalUsd($rateMatrix, $billableWeight, $cityDistance->distance_km);
        $totalVes = $this->bcvRateService->convertUsdToVes($totalUsd, $bcvRate);

        return [
            'volumetric_weight_kg' => $volumetricWeight,
            'billable_weight_kg' => $billableWeight,
            'distance_km' => $cityDistance->distance_km,
            'total_price_usd' => $totalUsd,
            'total_price_ves' => $totalVes,
            'bcv_rate_used' => (float) $bcvRate->rate,
        ];
    }
}
