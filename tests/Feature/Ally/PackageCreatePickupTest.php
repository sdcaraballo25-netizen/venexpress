<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\PackageCreate;
use App\Models\Ally;
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
 * Fase 3 — Registro de pedido y destino.
 *
 * El pedido debe registrar correctamente destino + modalidad
 * (Delivery/Retiro) y, si es Retiro, exigir un punto de retiro entre
 * los Aliados verificados como destino (Ally::is_verified_destination,
 * ver Fase 1). El Aliado que registra el pedido NUNCA elige ruta, HUB
 * ni almacén — solo, cuando aplica, el punto de retiro final.
 *
 * No implementa cobertura HUB<->zona ni redistribución: el punto de
 * retiro se filtra únicamente por el estado destino ya elegido.
 */
class PackageCreatePickupTest extends TestCase
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
            ->set('payment_method', 'efectivo_usd');
    }

    public function test_pickup_mode_is_required_when_delivery_is_not_requested(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('requires_delivery', false)
            ->call('save')
            ->assertHasErrors(['pickup_mode']);
    }

    public function test_pickup_ally_id_is_required_when_pickup_mode_is_ally(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('requires_delivery', false)
            ->set('pickup_mode', 'ally')
            ->call('save')
            ->assertHasErrors(['pickup_ally_id']);
    }

    public function test_pickup_point_is_not_required_when_delivery_is_requested(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('requires_delivery', true)
            ->set('delivery_address', 'Av. Bolívar, Edif. Central')
            ->set('delivery_sector', 'Centro')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('packages', [
            'recipient_name' => 'María Gómez',
            'requires_delivery' => true,
            'pickup_ally_id' => null,
        ]);
    }

    public function test_only_verified_active_allies_in_the_destination_state_can_be_selected_as_pickup_point(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $notVerified = $this->createAlly([
            'business_name' => 'Aliado No Verificado',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => false,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('requires_delivery', false)
            ->set('pickup_mode', 'ally')
            ->set('pickup_ally_id', $notVerified->id)
            ->call('save')
            ->assertHasErrors(['pickup_ally_id']);
    }

    public function test_a_verified_ally_from_a_different_state_cannot_be_selected(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $wrongStatePickup = $this->createAlly([
            'business_name' => 'Punto de Retiro Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
            'is_verified_destination' => true,
        ]);

        $component = Livewire::actingAs($ally->user)->test(PackageCreate::class);

        $this->fillCommonFields($component)
            ->set('destination_state', 'Carabobo')
            ->set('destination_city', 'Valencia')
            ->set('requires_delivery', false)
            ->set('pickup_mode', 'ally')
            ->set('pickup_ally_id', $wrongStatePickup->id)
            ->call('save')
            ->assertHasErrors(['pickup_ally_id']);
    }

    public function test_pickup_allies_list_only_shows_verified_active_allies_in_the_destination_state(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $validPickup = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        $this->createAlly([
            'business_name' => 'Aliado No Verificado Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => false,
        ]);

        $this->createAlly([
            'business_name' => 'Punto de Retiro Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
            'is_verified_destination' => true,
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('destination_state', 'Carabobo')
            ->set('pickup_mode', 'ally')
            ->assertViewHas('pickupAllies', function ($pickupAllies) use ($validPickup) {
                $names = array_column($pickupAllies, 'business_name');

                return $names === [$validPickup->business_name];
            });
    }

    public function test_changing_destination_state_clears_the_previously_selected_pickup_point(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('destination_state', 'Carabobo')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->assertSet('pickup_ally_id', $pickupAlly->id)
            ->set('destination_state', 'Zulia')
            ->assertSet('pickup_ally_id', null);
    }

    public function test_enabling_delivery_clears_the_previously_selected_pickup_point(): void
    {
        $ally = $this->createAlly(['city' => 'Caracas', 'state' => 'Distrito Capital']);

        $pickupAlly = $this->createAlly([
            'business_name' => 'Punto de Retiro Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_verified_destination' => true,
        ]);

        Livewire::actingAs($ally->user)
            ->test(PackageCreate::class)
            ->set('destination_state', 'Carabobo')
            ->set('pickup_ally_id', $pickupAlly->id)
            ->set('requires_delivery', true)
            ->assertSet('pickup_ally_id', null);
    }
}
