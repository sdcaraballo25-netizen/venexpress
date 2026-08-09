<?php

namespace App\Services;

use App\Models\RateMatrix;
use RuntimeException;

class TariffService
{
    /**
     * Calcula el peso volumétrico.
     *
     * Fórmula:
     *
     * Largo × Ancho × Alto / 5000
     */
    public function calculateVolumetricWeight(
        float $length,
        float $width,
        float $height
    ): float {
        if (
            $length <= 0 ||
            $width <= 0 ||
            $height <= 0
        ) {
            return 0;
        }

        return round(
            ($length * $width * $height) / 5000,
            3
        );
    }

    /**
     * Determina el peso que será utilizado
     * para facturar el envío.
     */
    public function calculateBillableWeight(
        float $physicalWeight,
        float $volumetricWeight
    ): float {
        return round(
            max(
                $physicalWeight,
                $volumetricWeight
            ),
            3
        );
    }

    /**
     * Busca la tarifa configurada para una ruta.
     */
    public function findRoute(
        string $originCity,
        string $destinationCity
    ): ?RateMatrix {
        return RateMatrix::query()
            ->where('origin_city', $originCity)
            ->where(
                'destination_city',
                $destinationCity
            )
            ->first();
    }

    /**
     * Calcula el precio total del envío.
     *
     * Precio =
     * tarifa base + (peso facturable × precio por kg)
     */
    public function calculatePrice(
        float $billableWeight,
        RateMatrix $route
    ): float {
        $price = (float) $route->base_price_usd
            + (
                $billableWeight
                * (float) $route->price_per_kg_usd
            );

        return round($price, 2);
    }

    /**
     * Ejecuta el cálculo completo de una tarifa.
     */
    public function calculate(
        string $originCity,
        string $destinationCity,
        float $physicalWeight,
        float $length,
        float $width,
        float $height
    ): array {
        $volumetricWeight =
            $this->calculateVolumetricWeight(
                $length,
                $width,
                $height
            );

        $billableWeight =
            $this->calculateBillableWeight(
                $physicalWeight,
                $volumetricWeight
            );

        $route = $this->findRoute(
            $originCity,
            $destinationCity
        );

        if (!$route) {
            throw new RuntimeException(
                "No existe una tarifa configurada para "
                . "{$originCity} → {$destinationCity}."
            );
        }

        $totalUsd = $this->calculatePrice(
            $billableWeight,
            $route
        );

        return [
            'volumetric_weight_kg' => $volumetricWeight,
            'billable_weight_kg' => $billableWeight,
            'base_price_usd' => (float) $route->base_price_usd,
            'price_per_kg_usd' => (float) $route->price_per_kg_usd,
            'total_price_usd' => $totalUsd,
            'route_id' => $route->id,
        ];
    }
}