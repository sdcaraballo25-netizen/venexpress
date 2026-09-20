<?php

namespace Tests\Feature;

use App\Livewire\Recommendations\Create;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecommendationCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_client_can_submit_a_recommendation_tied_to_their_account(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@example.com',
        ]);

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('message', 'Sería genial poder pagar con Zelle.')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('recommendations', [
            'user_id' => $user->id,
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@example.com',
            'status' => Recommendation::STATUS_NEW,
        ]);
    }

    public function test_message_is_required(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('message', '')
            ->call('submit')
            ->assertHasErrors(['message' => 'required']);
    }

    public function test_an_ally_sees_the_ally_layout(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        Ally::create([
            'user_id' => $user->id,
            'business_name' => 'Agencia de prueba',
            'rif' => 'J-12345678-9',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'address' => 'Av. Principal',
            'commission_percentage' => 10,
            'status' => Ally::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('recommendations.create'))
            ->assertOk()
            ->assertSee('Ayuda'); // presente en la barra lateral de layouts.ally
    }

    public function test_a_driver_can_submit_a_recommendation(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($user)
            ->test(Create::class)
            ->set('message', 'Agreguen notificaciones push.')
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('recommendations', [
            'user_id' => $user->id,
            'status' => Recommendation::STATUS_NEW,
        ]);
    }
}
