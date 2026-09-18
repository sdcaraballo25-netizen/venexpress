<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\RateMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * cod_amount_usd es una propiedad pública de Livewire que el propio
 * componente autocompleta con el precio calculado (ver
 * refreshPricePreview()), pero nada impedía que un request de
 * Livewire manipulado la sobrescribiera con cualquier valor antes de
 * llamar a save(). Este test simula exactamente eso con ->set() y
 * confirma que PackageService::createPackage() ignora ese valor y usa
 * siempre el total calculado por TariffService.
 */
class PackageCreateCodAmountTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

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

        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );
    }

    public function test_a_tampered_cod_amount_is_overridden_by_the_calculated_price(): void
    {
        $ally = $this->createAlly([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('sender_doc_type', 'V')
            ->set('sender_doc_number', '12345678')
            ->set('sender_name', 'Juan Pérez')
            ->set('sender_phone', '0414-1234567')
            ->set('recipient_doc_type', 'V')
            ->set('recipient_doc_number', '87654321')
            ->set('recipient_name', 'María Gómez')
            ->set('recipient_phone', '0424-7654321')
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('pickup_mode', 'ally')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->set('package_type', Package::TYPE_PAQUETE)
            ->set('physical_weight_kg', 2.0)
            ->set('is_cod', true);

        $realCodAmount = $component->get('cod_amount_usd');
        $this->assertNotNull($realCodAmount);
        $this->assertGreaterThan(0, $realCodAmount);

        // Simula un request de Livewire manipulado: el cliente
        // sobrescribe cod_amount_usd con un valor propio, muy distinto
        // al calculado, justo antes de guardar.
        $component->set('cod_amount_usd', 999999.99)
            ->call('save')
            ->assertHasNoErrors();

        $package = Package::where('ally_id', $ally->id)->firstOrFail();

        $this->assertEquals(round($realCodAmount, 2), round((float) $package->cod_amount_usd, 2));
        $this->assertNotEquals(999999.99, (float) $package->cod_amount_usd);
    }
}
