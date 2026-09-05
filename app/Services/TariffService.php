<?php

namespace App\Services;

use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use App\Support\Money;
use InvalidArgumentException;
use RuntimeException;

class TariffService
{
    public function __construct(
        protected BcvRateService $bcvRateService,
        protected DistanceApiService $distanceApiService,
    ) {
    }

    public function calculateVolumetricWeight(
        ?float $lengthCm,
        ?float $widthCm,
        ?float $heightCm
    ): float {
        if (
            $lengthCm === null
            || $widthCm === null
            || $heightCm === null
        ) {
            return 0.0;
        }

        if (
            $lengthCm <= 0
            || $widthCm <= 0
            || $heightCm <= 0
        ) {
            throw new InvalidArgumentException(
                'Las dimensiones deben ser mayores que cero.'
            );
        }

        $volume = Money::mul(Money::mul($lengthCm, $widthCm), $heightCm);

        return Money::round(Money::div($volume, 5000), 3);
    }

    public function calculateBillableWeight(
        float $physicalWeightKg,
        float $volumetricWeightKg
    ): float {
        if ($physicalWeightKg < 0) {
            throw new InvalidArgumentException(
                'El peso físico no puede ser negativo.'
            );
        }

        if ($volumetricWeightKg < 0) {
            throw new InvalidArgumentException(
                'El peso volumétrico no puede ser negativo.'
            );
        }

        return round(
            max(
                $physicalWeightKg,
                $volumetricWeightKg
            ),
            3
        );
    }

    public function findRate(): RateMatrix
    {
        $rate = RateMatrix::current();

        if (! $rate) {
            throw new RuntimeException(
                'No hay ninguna tarifa configurada todavía.'
            );
        }

        return $rate;
    }

    public function findDistanceKm(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState,
    ): int {
        $originCity = trim($originCity);
        $destinationCity = trim($destinationCity);

        $originState = $this->normalizeState($originState);
        $destinationState = $this->normalizeState($destinationState);

        if ($originCity === '') {
            throw new InvalidArgumentException(
                'La ciudad de origen es obligatoria.'
            );
        }

        if ($destinationCity === '') {
            throw new InvalidArgumentException(
                'La ciudad de destino es obligatoria.'
            );
        }

        /*
         * Para una nueva operación comercial exigimos estados.
         * Esto evita que dos ciudades con el mismo nombre sean
         * interpretadas como la misma ubicación.
         */
        if ($originState === null) {
            throw new InvalidArgumentException(
                'El estado de origen es obligatorio.'
            );
        }

        if ($destinationState === null) {
            throw new InvalidArgumentException(
                'El estado de destino es obligatorio.'
            );
        }

        if (
            $this->sameLocation(
                $originCity,
                $originState,
                $destinationCity,
                $destinationState
            )
        ) {
            return 0;
        }

        $stored = CityDistance::between(
            $originCity,
            $originState,
            $destinationCity,
            $destinationState
        );

        if ($stored) {
            return max(
                0,
                (int) $stored->distance_km
            );
        }

        try {
            $distanceKm =
                $this->distanceApiService->drivingDistanceKm(
                    originCity: $originCity,
                    originState: $originState,
                    destinationCity: $destinationCity,
                    destinationState: $destinationState,
                );

            if ($distanceKm < 0) {
                throw new RuntimeException(
                    'La API devolvió una distancia inválida.'
                );
            }

            CityDistance::setDistance(
                cityOne: $originCity,
                stateOne: $originState,
                cityTwo: $destinationCity,
                stateTwo: $destinationState,
                distanceKm: $distanceKm,
            );

            return $distanceKm;
        } catch (\Throwable $e) {
            /*
             * Compatibilidad con distancias antiguas.
             *
             * Solo se usa como fallback cuando la API falla.
             */
            $legacyStored = CityDistance::betweenCities(
                $originCity,
                $destinationCity
            );

            if ($legacyStored) {
                return max(
                    0,
                    (int) $legacyStored->distance_km
                );
            }

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            throw new RuntimeException(
                'No fue posible calcular la distancia entre las ubicaciones seleccionadas.',
                previous: $e
            );
        }
    }

    public function calculatePackageSubtotalUsd(
        RateMatrix $rateMatrix,
        float $billableWeightKg,
        int $distanceKm
    ): float {
        if ($billableWeightKg < 0) {
            throw new InvalidArgumentException(
                'El peso facturable no puede ser negativo.'
            );
        }

        if ($distanceKm < 0) {
            throw new InvalidArgumentException(
                'La distancia no puede ser negativa.'
            );
        }

        $weightCost = Money::mul($billableWeightKg, $rateMatrix->price_per_kg_usd);
        $distanceCost = Money::mul($distanceKm, $rateMatrix->price_per_km_usd);

        return Money::round(
            Money::sum([
                $rateMatrix->base_price_usd,
                $weightCost,
                $distanceCost,
            ]),
            2
        );
    }

    public function calculateEnvelopeSubtotalUsd(
        RateMatrix $rateMatrix,
        int $distanceKm
    ): float {
        if ($distanceKm < 0) {
            throw new InvalidArgumentException(
                'La distancia no puede ser negativa.'
            );
        }

        $distanceCost = Money::mul($distanceKm, $rateMatrix->price_per_km_usd);

        return Money::round(
            Money::add($rateMatrix->envelope_price_usd, $distanceCost),
            2
        );
    }

    public function calculateFragileSurcharge(
        RateMatrix $rateMatrix,
        bool $isFragile
    ): float {
        if (! $isFragile) {
            return 0.0;
        }

        return Money::round(
            max(0.0, (float) $rateMatrix->fragile_surcharge_usd),
            2
        );
    }

    public function calculateInsurancePrice(
        RateMatrix $rateMatrix,
        bool $hasInsurance,
        ?float $declaredValueUsd
    ): float {
        if ($declaredValueUsd !== null && $declaredValueUsd <= 0) {
            throw new InvalidArgumentException(
                'El valor declarado debe ser mayor que cero.'
            );
        }

        if (! $hasInsurance) {
            return 0.0;
        }

        if (
            $declaredValueUsd === null
            || $declaredValueUsd <= 0
        ) {
            throw new RuntimeException(
                'Debes indicar un valor declarado mayor que cero '
                . 'para calcular el seguro.'
            );
        }

        $percentage = max(
            0,
            (float) $rateMatrix->insurance_percentage
        );

        $factor = Money::div($percentage, 100);

        return Money::round(
            Money::mul($declaredValueUsd, $factor),
            2
        );
    }

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
        $originCity = trim($originCity);
        $destinationCity = trim($destinationCity);

        $originState = $this->normalizeState($originState);
        $destinationState = $this->normalizeState($destinationState);

        if ($originCity === '') {
            throw new InvalidArgumentException(
                'La ciudad de origen es obligatoria.'
            );
        }

        if ($destinationCity === '') {
            throw new InvalidArgumentException(
                'La ciudad de destino es obligatoria.'
            );
        }

        if ($originState === null) {
            throw new InvalidArgumentException(
                'El estado de origen es obligatorio.'
            );
        }

        if ($destinationState === null) {
            throw new InvalidArgumentException(
                'El estado de destino es obligatorio.'
            );
        }

        if (! in_array(
            $packageType,
            Package::TYPES,
            true
        )) {
            throw new InvalidArgumentException(
                'El tipo de envío seleccionado no es válido.'
            );
        }

        if ($physicalWeightKg < 0) {
            throw new InvalidArgumentException(
                'El peso físico no puede ser negativo.'
            );
        }

        if (
            $packageType === Package::TYPE_PAQUETE
            && $physicalWeightKg <= 0
        ) {
            throw new InvalidArgumentException(
                'El peso del paquete debe ser mayor que cero.'
            );
        }

        if ($packageType === Package::TYPE_SOBRE) {
            $lengthCm = null;
            $widthCm = null;
            $heightCm = null;
        } else {
            $dimensions = [
                $lengthCm,
                $widthCm,
                $heightCm,
            ];

            $providedDimensions = array_filter(
                $dimensions,
                static fn ($value) => $value !== null
            );

            if (
                count($providedDimensions) > 0
                && count($providedDimensions) < 3
            ) {
                throw new InvalidArgumentException(
                    'Debes indicar largo, ancho y alto '
                    . 'si deseas utilizar peso volumétrico.'
                );
            }

            foreach ($dimensions as $dimension) {
                if (
                    $dimension !== null
                    && $dimension <= 0
                ) {
                    throw new InvalidArgumentException(
                        'Las dimensiones deben ser mayores que cero.'
                    );
                }
            }
        }

        if ($declaredValueUsd !== null && $declaredValueUsd <= 0) {
            throw new InvalidArgumentException(
                'El valor declarado debe ser mayor que cero.'
            );
        }

        $rateMatrix = $this->findRate();

        $distanceKm = $this->findDistanceKm(
            originCity: $originCity,
            originState: $originState,
            destinationCity: $destinationCity,
            destinationState: $destinationState,
        );

        $bcvRate = $this->bcvRateService->getCurrentRate();

        $volumetricWeight =
            $packageType === Package::TYPE_SOBRE
                ? 0.0
                : $this->calculateVolumetricWeight(
                    $lengthCm,
                    $widthCm,
                    $heightCm
                );

        $billableWeight =
            $this->calculateBillableWeight(
                $physicalWeightKg,
                $volumetricWeight
            );

        if ($packageType === Package::TYPE_SOBRE) {
            $subtotalUsd =
                $this->calculateEnvelopeSubtotalUsd(
                    $rateMatrix,
                    $distanceKm
                );
        } else {
            $subtotalUsd =
                $this->calculatePackageSubtotalUsd(
                    $rateMatrix,
                    $billableWeight,
                    $distanceKm
                );
        }

        $fragileSurchargeUsd =
            $this->calculateFragileSurcharge(
                $rateMatrix,
                $isFragile
            );

        $insurancePriceUsd =
            $this->calculateInsurancePrice(
                $rateMatrix,
                $hasInsurance,
                $declaredValueUsd
            );

        $deliveryFeeUsd = $requiresDelivery
            ? Money::round(
                max(0.0, (float) $rateMatrix->delivery_price_usd),
                2
            )
            : 0.0;

        $totalUsd = Money::round(
            Money::sum([
                $subtotalUsd,
                $fragileSurchargeUsd,
                $insurancePriceUsd,
                $deliveryFeeUsd,
            ]),
            2
        );

        if ($totalUsd < 0) {
            throw new RuntimeException(
                'El total calculado no puede ser negativo.'
            );
        }

        $totalVes =
            $this->bcvRateService->convertUsdToVes(
                $totalUsd,
                $bcvRate
            );

        if ($totalVes < 0) {
            throw new RuntimeException(
                'El total en bolívares no puede ser negativo.'
            );
        }

        return [
            'package_type' => $packageType,

            'volumetric_weight_kg' =>
                $volumetricWeight,

            'billable_weight_kg' =>
                $billableWeight,

            'distance_km' =>
                $distanceKm,

            'delivery_fee_usd' =>
                $deliveryFeeUsd,

            'requires_delivery' =>
                $requiresDelivery,

            'subtotal_price_usd' =>
                $subtotalUsd,

            'fragile_surcharge_usd' =>
                $fragileSurchargeUsd,

            'insurance_price_usd' =>
                $insurancePriceUsd,

            'total_price_usd' =>
                $totalUsd,

            'total_price_ves' =>
                $totalVes,

            'bcv_rate_used' =>
                (float) $bcvRate->rate,
        ];
    }

    protected function sameLocation(
        string $cityOne,
        ?string $stateOne,
        string $cityTwo,
        ?string $stateTwo
    ): bool {
        if (
            mb_strtolower(trim($cityOne))
            !== mb_strtolower(trim($cityTwo))
        ) {
            return false;
        }

        if (
            $stateOne !== null
            && $stateTwo !== null
        ) {
            return mb_strtolower(trim($stateOne))
                === mb_strtolower(trim($stateTwo));
        }

        return false;
    }

    protected function normalizeState(
        ?string $state
    ): ?string {
        if ($state === null) {
            return null;
        }

        $state = trim($state);

        return $state === ''
            ? null
            : $state;
    }
}
