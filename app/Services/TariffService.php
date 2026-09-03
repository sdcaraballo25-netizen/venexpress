<?php

namespace App\Services;

use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use InvalidArgumentException;
use RuntimeException;

class TariffService
{
    public function __construct(
        protected BcvRateService $bcvRateService,
        protected DistanceApiService $distanceApiService,
    ) {
    }

    /**
     * Peso volumétrico:
     *
     * Largo × Ancho × Alto / 5000
     */
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

        return round(
            ($lengthCm * $widthCm * $heightCm) / 5000,
            3
        );
    }

    /**
     * Peso facturable:
     * máximo entre peso físico y volumétrico.
     */
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

    /**
     * Obtiene la tarifa vigente.
     */
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

    /**
     * Obtiene la distancia por carretera.
     *
     * Primero se consulta la API.
     * Después se guarda el resultado localmente.
     *
     * Si la API falla, se utiliza la distancia previamente
     * almacenada.
     */
    public function findDistanceKm(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState,
    ): int {
        $originCity = trim($originCity);
        $destinationCity = trim($destinationCity);

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
         * Misma ciudad y mismo estado = 0 km.
         */
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

        /*
         * Primero intentamos la distancia almacenada.
         *
         * Esto evita hacer una llamada HTTP innecesaria cada vez
         * que el usuario consulta una ruta que ya conocemos.
         */
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

        /*
         * Si no existe localmente, calculamos mediante API.
         */
        try {
            $distanceKm =
                $this->distanceApiService->drivingDistanceKm(
                    originCity: $originCity,
                    originState: $originState,
                    destinationCity: $destinationCity,
                    destinationState: $destinationState,
                );

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
             * Segundo intento: buscar cualquier registro antiguo
             * que solamente tenga ciudades.
             *
             * Esto permite que las distancias existentes antes
             * de esta actualización no se pierdan.
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

            throw new RuntimeException(
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Precio de un paquete.
     */
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

        return round(
            (float) $rateMatrix->base_price_usd
            + (
                $billableWeightKg
                * (float) $rateMatrix->price_per_kg_usd
            )
            + (
                $distanceKm
                * (float) $rateMatrix->price_per_km_usd
            ),
            2
        );
    }

    /**
     * Precio de un sobre.
     */
    public function calculateEnvelopeSubtotalUsd(
        RateMatrix $rateMatrix,
        int $distanceKm
    ): float {
        if ($distanceKm < 0) {
            throw new InvalidArgumentException(
                'La distancia no puede ser negativa.'
            );
        }

        return round(
            (float) $rateMatrix->envelope_price_usd
            + (
                $distanceKm
                * (float) $rateMatrix->price_per_km_usd
            ),
            2
        );
    }

    /**
     * Recargo por envío frágil.
     */
    public function calculateFragileSurcharge(
        RateMatrix $rateMatrix,
        bool $isFragile
    ): float {
        if (! $isFragile) {
            return 0.0;
        }

        return round(
            max(
                0,
                (float) $rateMatrix->fragile_surcharge_usd
            ),
            2
        );
    }

    /**
     * Calcula el seguro.
     */
    public function calculateInsurancePrice(
        RateMatrix $rateMatrix,
        bool $hasInsurance,
        ?float $declaredValueUsd
    ): float {
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

        return round(
            $declaredValueUsd
            * ($percentage / 100),
            2
        );
    }

    /**
     * Calcula todos los valores de una guía.
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
        /*
         * Validaciones generales.
         */
        $originCity = trim($originCity);
        $destinationCity = trim($destinationCity);

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
         * Solo aceptamos los tipos definidos por Package.
         */
        $allowedTypes = [
            Package::TYPE_PAQUETE,
            Package::TYPE_SOBRE,
        ];

        if (! in_array($packageType, $allowedTypes, true)) {
            throw new InvalidArgumentException(
                'El tipo de envío seleccionado no es válido.'
            );
        }

        /*
         * Un paquete requiere peso físico.
         * Un sobre no necesita peso ni dimensiones.
         */
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

        /*
         * Para sobres ignoramos las dimensiones.
         * Para paquetes validamos que estén completas o ninguna.
         */
        if ($packageType === Package::TYPE_PAQUETE) {
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

        $rateMatrix = $this->findRate();

        $distanceKm = $this->findDistanceKm(
            originCity: $originCity,
            originState: $originState,
            destinationCity: $destinationCity,
            destinationState: $destinationState,
        );

        $bcvRate = $this->bcvRateService->getCurrentRate();

        $volumetricWeight = $packageType === Package::TYPE_SOBRE
            ? 0.0
            : $this->calculateVolumetricWeight(
                $lengthCm,
                $widthCm,
                $heightCm
            );

        $billableWeight = $this->calculateBillableWeight(
            $physicalWeightKg,
            $volumetricWeight
        );

        /*
         * El tipo de envío se decide explícitamente.
         */
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
            ? round(
                max(
                    0,
                    (float) $rateMatrix->delivery_price_usd
                ),
                2
            )
            : 0.0;

        $totalUsd = round(
            $subtotalUsd
            + $fragileSurchargeUsd
            + $insurancePriceUsd
            + $deliveryFeeUsd,
            2
        );

        $totalVes =
            $this->bcvRateService->convertUsdToVes(
                $totalUsd,
                $bcvRate
            );

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

    /**
     * Compara dos ubicaciones.
     */
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

        return $stateOne === null
            && $stateTwo === null;
    }
}
