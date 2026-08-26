<?php

namespace App\Services;

use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use RuntimeException;

class TariffService
{
    public function __construct(
        protected BcvRateService $bcvRateService,
        protected DistanceApiService $distanceApiService,
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
     * Obtiene la tarifa global vigente.
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
     * Obtiene la distancia por carretera entre las ciudades.
     *
     * Primero se usa una distancia ya almacenada en city_distances.
     * Si no existe, se consulta la API Nominatim + OSRM y se guarda
     * el resultado para reutilizarlo posteriormente.
     */
    public function findDistanceKm(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState,
    ): int {
        try {
            // La API es la fuente principal: distancia real por carretera.
            $distanceKm = $this->distanceApiService->drivingDistanceKm(
                originCity: $originCity,
                originState: $originState,
                destinationCity: $destinationCity,
                destinationState: $destinationState,
            );

            // Guardamos una copia local para respaldo y consultas futuras.
            CityDistance::setDistance($originCity, $destinationCity, $distanceKm);

            return $distanceKm;
        } catch (\Throwable $e) {
            // Si la API no responde, usamos una distancia previamente
            // almacenada para no bloquear el registro de la guía.
            $stored = CityDistance::between($originCity, $destinationCity);

            if ($stored) {
                return (int) $stored->distance_km;
            }

            throw new RuntimeException(
                $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * Subtotal en USD para un PAQUETE (no sobre):
     * base + (peso facturable × precio por kg) + (distancia en km × precio por km).
     */
    public function calculatePackageSubtotalUsd(RateMatrix $rateMatrix, float $billableWeightKg, int $distanceKm): float
    {
        return round(
            (float) $rateMatrix->base_price_usd
                + ($billableWeightKg * (float) $rateMatrix->price_per_kg_usd)
                + ($distanceKm * (float) $rateMatrix->price_per_km_usd),
            2
        );
    }

    /**
     * Subtotal en USD para un SOBRE: precio fijo del sobre, sin
     * peso ni volumen, pero sigue sumando distancia igual que un paquete.
     */
    public function calculateEnvelopeSubtotalUsd(RateMatrix $rateMatrix, int $distanceKm): float
    {
        return round(
            (float) $rateMatrix->envelope_price_usd
                + ($distanceKm * (float) $rateMatrix->price_per_km_usd),
            2
        );
    }

    /**
     * Recargo fijo por envío frágil (aplica a sobre y a paquete por igual).
     */
    public function calculateFragileSurcharge(RateMatrix $rateMatrix, bool $isFragile): float
    {
        return $isFragile ? round((float) $rateMatrix->fragile_surcharge_usd, 2) : 0.0;
    }

    /**
     * Precio del seguro = valor declarado × porcentaje de seguro configurado.
     *
     * @throws RuntimeException si se pide seguro sin indicar valor declarado.
     */
    public function calculateInsurancePrice(RateMatrix $rateMatrix, bool $hasInsurance, ?float $declaredValueUsd): float
    {
        if (! $hasInsurance) {
            return 0.0;
        }

        if ($declaredValueUsd === null || $declaredValueUsd <= 0) {
            throw new RuntimeException('Debes indicar el valor declarado del paquete para calcular el seguro.');
        }

        return round($declaredValueUsd * ((float) $rateMatrix->insurance_percentage / 100), 2);
    }

    /**
     * Calcula todo lo necesario para facturar un envío: pesos,
     * distancia, subtotal según tipo, recargos, total en USD/VES
     * y la tasa BCV utilizada.
     *
     * @return array{
     *   package_type: string,
     *   volumetric_weight_kg: float,
     *   billable_weight_kg: float,
     *   distance_km: int,
     *   subtotal_price_usd: float,
     *   fragile_surcharge_usd: float,
     *   insurance_price_usd: float, delivery_fee_usd: float,
     *   total_price_usd: float,
     *   total_price_ves: float,
     *   bcv_rate_used: float,
     * }
     *
     * @throws RuntimeException si falta tarifa, distancia, o valor declarado con seguro activo.
     */
    public function calculate(
        string $originCity,
        string $destinationCity,
        string $packageType = Package::TYPE_PAQUETE,
        float $physicalWeightKg = 0.0,
        ?float $lengthCm = null,
        ?float $widthCm = null,
        ?float $heightCm = null,
        bool $isFragile = false,
        bool $hasInsurance = false,
        ?float $declaredValueUsd = null,
        ?string $originState = null,
        ?string $destinationState = null,
        bool $requiresDelivery = false,
    ): array {
        $rateMatrix = $this->findRate();
        $distanceKm = $this->findDistanceKm(
            originCity: $originCity,
            originState: $originState,
            destinationCity: $destinationCity,
            destinationState: $destinationState,
        );
        $bcvRate = $this->bcvRateService->getCurrentRate();

        $volumetricWeight = $this->calculateVolumetricWeight($lengthCm, $widthCm, $heightCm);
        $billableWeight = $this->calculateBillableWeight($physicalWeightKg, $volumetricWeight);

        $subtotalUsd = $packageType === Package::TYPE_SOBRE
            ? $this->calculateEnvelopeSubtotalUsd($rateMatrix, $distanceKm)
            : $this->calculatePackageSubtotalUsd($rateMatrix, $billableWeight, $distanceKm);

        $fragileSurchargeUsd = $this->calculateFragileSurcharge($rateMatrix, $isFragile);
        $insurancePriceUsd = $this->calculateInsurancePrice($rateMatrix, $hasInsurance, $declaredValueUsd);
        $deliveryFeeUsd = $requiresDelivery
            ? round((float) $rateMatrix->delivery_price_usd, 2)
            : 0.0;

        $totalUsd = round(
            $subtotalUsd
                + $fragileSurchargeUsd
                + $insurancePriceUsd
                + $deliveryFeeUsd,
            2
        );
        $totalVes = $this->bcvRateService->convertUsdToVes($totalUsd, $bcvRate);

        return [
            'package_type' => $packageType,
            'volumetric_weight_kg' => $volumetricWeight,
            'billable_weight_kg' => $billableWeight,
            'distance_km' => $distanceKm,
            'delivery_fee_usd' => $deliveryFeeUsd,
            'requires_delivery' => $requiresDelivery,
            'subtotal_price_usd' => $subtotalUsd,
            'fragile_surcharge_usd' => $fragileSurchargeUsd,
            'insurance_price_usd' => $insurancePriceUsd,
            'total_price_usd' => $totalUsd,
            'total_price_ves' => $totalVes,
            'bcv_rate_used' => (float) $bcvRate->rate,
        ];
    }
}
