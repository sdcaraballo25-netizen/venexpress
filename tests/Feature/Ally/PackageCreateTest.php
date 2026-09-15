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
 * Regresión de Fase 1: el registro de paquetes del Aliado ahora
 * valida destination_state/destination_city contra
 * VenezuelaLocationService en vez de config('venezuela.states').
 * Este test confirma que el flujo de registro sigue funcionando de
 * punta a punta con la nueva fuente de catálogo.
 */
class PackageCreateTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ninguna llamada HTTP real: cualquier consulta de distancia
        // no pre-cargada vía CityDistance debe fallar rápido, no
        // esperar un timeout de red real.
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

        // Evita llamadas HTTP reales a la API de distancias.
        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );
    }

    public function test_ally_can_register_a_package_choosing_destination_from_the_catalog(): void
    {
        $ally = $this->createAlly([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        // Desde la Fase 3, un pedido que no requiere delivery necesita
        // un punto de retiro verificado en el estado destino.
        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->assertSet('origin_city', 'Caracas')
            ->assertSet('origin_state', 'Distrito Capital')
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
            ->set('payment_method', 'efectivo_usd')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'ally_id' => $ally->id,
            'pickup_ally_id' => $pickupAlly->id,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'recipient_name' => 'María Gómez',
        ]);
    }

    public function test_destination_city_must_belong_to_the_selected_destination_state(): void
    {
        $ally = $this->createAlly([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('destination_state', 'Carabobo')
            // "Maracaibo" pertenece a Zulia, no a Carabobo.
            ->set('destination_city', 'Maracaibo')
            ->set('sender_doc_type', 'V')
            ->set('sender_doc_number', '12345678')
            ->set('sender_name', 'Juan Pérez')
            ->set('sender_phone', '0414-1234567')
            ->set('recipient_doc_type', 'V')
            ->set('recipient_doc_number', '87654321')
            ->set('recipient_name', 'María Gómez')
            ->set('recipient_phone', '0424-7654321')
            ->set('package_type', Package::TYPE_PAQUETE)
            ->set('physical_weight_kg', 2.0)
            ->set('payment_method', 'efectivo_usd')
            ->call('save')
            ->assertHasErrors(['destination_city']);
    }

    public function test_destination_state_must_belong_to_the_catalog(): void
    {
        $ally = $this->createAlly([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('destination_state', 'Estado Que No Existe')
            ->call('save')
            ->assertHasErrors(['destination_state']);
    }
}
