<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\AlliesManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * El Admin necesita revisar los datos completos de un aliado (incluida
 * la foto de fachada en tamaño grande) antes de aprobar/rechazar su
 * postulación, en vez de solo el nombre/RIF/ciudad visibles en la tabla.
 */
class AlliesManagerDetailsTest extends TestCase
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

    public function test_admin_can_open_and_close_the_ally_details_modal(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly(['business_name' => 'Agencia Central']);

        Livewire::actingAs($admin)
            ->test(AlliesManager::class)
            ->call('viewDetails', $ally->id)
            ->assertSet('showDetailsModal', true)
            ->assertSet('viewingAllyId', $ally->id)
            ->assertSee('Agencia Central')
            ->assertSee($ally->rif)
            ->call('closeDetails')
            ->assertSet('showDetailsModal', false)
            ->assertSet('viewingAllyId', null);
    }
}
