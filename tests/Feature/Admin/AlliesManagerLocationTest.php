<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Regresión de Fase 1: AlliesManager::saveLocation() validaba
 * location_state como texto libre; ahora valida contra el catálogo
 * único (VenezuelaLocationService). No existía ningún test previo
 * para este flujo.
 */
class AlliesManagerLocationTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_save_a_valid_location_for_an_ally(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('editLocation', $ally->id)
            ->set('location_state', 'Carabobo')
            ->set('location_latitude', 10.1621)
            ->set('location_longitude', -68.0077)
            ->call('saveLocation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('allies', [
            'id' => $ally->id,
            'state' => 'Carabobo',
        ]);
    }

    public function test_admin_cannot_save_a_state_outside_the_catalog(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('editLocation', $ally->id)
            ->set('location_state', 'Estado Que No Existe')
            ->set('location_latitude', 10.1621)
            ->set('location_longitude', -68.0077)
            ->call('saveLocation')
            ->assertHasErrors(['location_state']);
    }
}
