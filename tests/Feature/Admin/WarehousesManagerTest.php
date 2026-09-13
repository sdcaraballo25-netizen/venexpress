<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\WarehousesManager;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WarehousesManagerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_create_a_warehouse(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('startCreating')
            ->set('name', 'Almacén Valencia')
            ->set('state', 'Carabobo')
            ->set('city', 'Valencia')
            ->set('address', 'Zona Industrial')
            ->call('save');

        $this->assertDatabaseHas('warehouses', [
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_deactivate_a_warehouse(): void
    {
        $admin = $this->createAdmin();

        $warehouse = Warehouse::create([
            'name' => 'Almacén Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleActive', $warehouse->id);

        $this->assertFalse($warehouse->fresh()->is_active);
    }
}
