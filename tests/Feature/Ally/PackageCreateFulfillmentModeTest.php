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
 * Modalidad de destino final del paquete: Retiro en HUB, Retiro en
 * Punto Aliado, o Delivery.
 *
 * El HUB NUNCA se modela como Aliado: cuando pickup_mode = 'hub',
 * pickup_ally_id debe quedar null siempre — el HUB concreto se
 * resuelve después, en Fase 5A/5B, vía LogisticsResolutionService,
 * nunca elegido aquí.
 */
class PackageCreateFulfillmentModeTest extends TestCase
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

    private function fillCommonFields($component)
    {
        return $component
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
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia');
    }

    public function test_delivery_registers_successfully(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectDelivery')
            ->set('delivery_address', 'Av. Bolívar, Edif. Central')
            ->set('delivery_sector', 'Centro')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'recipient_name' => 'María Gómez',
            'requires_delivery' => true,
            'pickup_mode' => null,
            'pickup_ally_id' => null,
        ]);
    }

    public function test_hub_pickup_registers_successfully_without_selecting_an_ally(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectHubPickup')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'recipient_name' => 'María Gómez',
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
            'pickup_ally_id' => null,
        ]);
    }

    public function test_ally_pickup_registers_successfully(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectAllyPickup')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'recipient_name' => 'María Gómez',
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);
    }

    /**
     * Aunque alguien intente forzar pickup_ally_id junto con
     * pickup_mode='hub' (saltándose la UI), la validación lo rechaza:
     * el HUB nunca puede quedar asociado a un Aliado.
     */
    public function test_hub_pickup_never_persists_a_pickup_ally_id_even_if_forced(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $someAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectHubPickup')
            ->set('pickup_ally_id', $someAlly->id)
            ->call('save')
            ->assertHasErrors(['pickup_ally_id']);

        $this->assertDatabaseMissing('packages', [
            'recipient_name' => 'María Gómez',
        ]);
    }

    public function test_switching_from_delivery_to_hub_clears_delivery_fields_and_pickup_ally_id(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectDelivery')
            ->set('delivery_address', 'Av. Bolívar, Edif. Central')
            ->set('delivery_sector', 'Centro')
            ->call('selectHubPickup')
            ->assertSet('requires_delivery', false)
            ->assertSet('pickup_mode', Package::PICKUP_MODE_HUB)
            ->assertSet('pickup_ally_id', null)
            ->assertSet('delivery_address', '')
            ->assertSet('delivery_sector', '');
    }

    public function test_switching_from_ally_to_hub_clears_pickup_ally_id(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectAllyPickup')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->assertSet('pickup_ally_id', $pickupAlly->id)
            ->call('selectHubPickup')
            ->assertSet('pickup_mode', Package::PICKUP_MODE_HUB)
            ->assertSet('pickup_ally_id', null);
    }

    public function test_switching_from_hub_to_ally_requires_choosing_an_ally_again(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectHubPickup')
            ->call('selectAllyPickup')
            ->assertSet('pickup_mode', Package::PICKUP_MODE_ALLY)
            ->assertSet('pickup_ally_id', null)
            ->call('save')
            ->assertHasErrors(['pickup_ally_id']);
    }

    public function test_switching_from_pickup_to_delivery_clears_pickup_mode_and_ally(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->call('selectAllyPickup')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->call('selectDelivery')
            ->assertSet('requires_delivery', true)
            ->assertSet('pickup_mode', null)
            ->assertSet('pickup_ally_id', null);
    }

    public function test_changing_destination_state_while_in_hub_mode_does_not_populate_pickup_allies(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->call('selectHubPickup')
            ->set('destination_state', 'Carabobo')
            ->assertSet('pickupAllies', []);
    }
}
