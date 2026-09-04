<?php

namespace Tests\Feature;

use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use App\Services\TariffService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class TariffServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TariffService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Tarifa base conocida para poder verificar los cálculos a mano.
        RateMatrix::create([
            'base_price_usd' => 2.00,
            'price_per_kg_usd' => 0.50,
            'price_per_km_usd' => 0.01,
            'envelope_price_usd' => 1.50,
            'fragile_surcharge_usd' => 3.00,
            'insurance_percentage' => 2.00,
            'delivery_price_usd' => 4.00,
        ]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        // Evitamos llamadas HTTP reales a la API de distancias:
        // pre-cargamos la distancia entre las dos ciudades de prueba.
        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );

        $this->service = app(TariffService::class);
    }

    public function test_volumetric_weight_formula(): void
    {
        // 40 x 30 x 20 / 5000 = 4.8 kg
        $weight = $this->service->calculateVolumetricWeight(40, 30, 20);

        $this->assertSame(4.8, $weight);
    }

    public function test_volumetric_weight_rejects_zero_or_negative_dimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculateVolumetricWeight(0, 30, 20);
    }

    public function test_billable_weight_uses_the_greater_of_physical_or_volumetric(): void
    {
        $this->assertSame(5.0, $this->service->calculateBillableWeight(5.0, 4.8));
        $this->assertSame(4.8, $this->service->calculateBillableWeight(2.0, 4.8));
    }

    public function test_calculate_package_subtotal_matches_expected_formula(): void
    {
        $rateMatrix = RateMatrix::current();

        // base(2.00) + peso(3kg * 0.50) + distancia(150km * 0.01) = 5.00
        $subtotal = $this->service->calculatePackageSubtotalUsd(
            $rateMatrix,
            billableWeightKg: 3.0,
            distanceKm: 150,
        );

        $this->assertSame(5.00, $subtotal);
    }

    public function test_calculate_end_to_end_for_a_paquete_without_extras(): void
    {
        $result = $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Valencia',
            packageType: Package::TYPE_PAQUETE,
            physicalWeightKg: 3.0,
            originState: 'Distrito Capital',
            destinationState: 'Carabobo',
        );

        // base(2.00) + peso(3 * 0.50) + distancia(150 * 0.01) = 5.00
        $this->assertSame(5.00, $result['total_price_usd']);
        $this->assertSame(150, $result['distance_km']);
        $this->assertSame(0.0, $result['fragile_surcharge_usd']);
        $this->assertSame(0.0, $result['insurance_price_usd']);
        $this->assertSame(0.0, $result['delivery_fee_usd']);

        // 5.00 USD * 40.00 Bs = 200.00 Bs
        $this->assertSame(200.00, $result['total_price_ves']);
    }

    public function test_calculate_applies_fragile_and_insurance_and_delivery_surcharges(): void
    {
        $result = $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Valencia',
            packageType: Package::TYPE_PAQUETE,
            physicalWeightKg: 3.0,
            isFragile: true,
            hasInsurance: true,
            declaredValueUsd: 100.00,
            originState: 'Distrito Capital',
            destinationState: 'Carabobo',
            requiresDelivery: true,
        );

        // subtotal 5.00 + frágil 3.00 + seguro (100 * 2%) 2.00 + delivery 4.00 = 14.00
        $this->assertSame(3.00, $result['fragile_surcharge_usd']);
        $this->assertSame(2.00, $result['insurance_price_usd']);
        $this->assertSame(4.00, $result['delivery_fee_usd']);
        $this->assertSame(14.00, $result['total_price_usd']);
    }

    public function test_calculate_requires_origin_and_destination_states(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Valencia',
            packageType: Package::TYPE_PAQUETE,
            physicalWeightKg: 3.0,
            originState: null,
            destinationState: 'Carabobo',
        );
    }

    public function test_calculate_rejects_zero_weight_for_paquete(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Valencia',
            packageType: Package::TYPE_PAQUETE,
            physicalWeightKg: 0,
            originState: 'Distrito Capital',
            destinationState: 'Carabobo',
        );
    }

    public function test_calculate_for_sobre_ignores_weight_and_uses_envelope_price(): void
    {
        $result = $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Valencia',
            packageType: Package::TYPE_SOBRE,
            originState: 'Distrito Capital',
            destinationState: 'Carabobo',
        );

        // envelope(1.50) + distancia(150 * 0.01) = 3.00
        $this->assertSame(3.00, $result['total_price_usd']);
        $this->assertSame(0.0, $result['volumetric_weight_kg']);
    }

    public function test_same_city_and_state_results_in_zero_distance(): void
    {
        $result = $this->service->calculate(
            originCity: 'Caracas',
            destinationCity: 'Caracas',
            packageType: Package::TYPE_PAQUETE,
            physicalWeightKg: 1.0,
            originState: 'Distrito Capital',
            destinationState: 'Distrito Capital',
        );

        $this->assertSame(0, $result['distance_km']);
    }
}
