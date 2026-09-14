<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\OfficeLocator;
use App\Models\Ally;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeLocatorLocationTest extends TestCase
{
    use RefreshDatabase;

    private function createVisibleAlly(string $name, float $lat, float $lng): Ally
    {
        $user = User::factory()->create(['role' => User::ROLE_ALIADO]);

        return Ally::create([
            'user_id' => $user->id,
            'business_name' => $name,
            'rif' => 'J-' . random_int(10000000, 99999999) . '-' . random_int(0, 9),
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'latitude' => $lat,
            'longitude' => $lng,
            'commission_percentage' => 10.00,
            'status' => Ally::STATUS_ACTIVE,
        ]);
    }

    public function test_sorts_allies_by_distance_once_a_location_is_shared(): void
    {
        // Caracas aprox.
        $near = $this->createVisibleAlly('Agencia Cercana', 10.4806, -66.9036);

        // Maracaibo aprox. (mucho más lejos de Caracas).
        $far = $this->createVisibleAlly('Agencia Lejana', 10.6427, -71.6125);

        $component = Livewire::test(OfficeLocator::class)
            // Ubicación del visitante: también en Caracas.
            ->call('useMyLocation', 10.5, -66.9);

        $ids = $component->get('allies')->pluck('id')->all();

        $this->assertSame([$near->id, $far->id], $ids);
    }

    public function test_location_denied_sets_a_friendly_error(): void
    {
        Livewire::test(OfficeLocator::class)
            ->call('locationDenied')
            ->assertSet('locationError', fn ($value) => ! empty($value));
    }
}
