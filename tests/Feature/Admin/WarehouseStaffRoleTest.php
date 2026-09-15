<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UsersManager;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre el nuevo rol 'almacen': creación desde el panel de Admin
 * (UsersManager) y acceso a su dashboard mínimo.
 */
class WarehouseStaffRoleTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('admin-password'),
        ]);
    }

    public function test_admin_can_create_a_warehouse_staff_user(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'almacen')
            ->set('name', 'Personal Almacén')
            ->set('email', 'almacen@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('warehouse_id', $warehouse->id)
            ->call('requestCreate')
            ->set('adminPassword', 'admin-password')
            ->call('createUser')
            ->assertHasNoErrors();

        $user = User::where('email', 'almacen@example.com')->firstOrFail();

        $this->assertTrue($user->isAlmacen());
        $this->assertSame($warehouse->id, $user->warehouse_id);
    }

    public function test_creating_warehouse_staff_without_a_warehouse_fails_validation(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'almacen')
            ->set('name', 'Personal Almacén')
            ->set('email', 'almacen2@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('requestCreate')
            ->assertHasErrors(['warehouse_id' => 'required']);
    }

    public function test_warehouse_staff_can_access_their_dashboard(): void
    {
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $user = User::factory()->create([
            'role' => User::ROLE_ALMACEN,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'warehouse_id' => $warehouse->id,
        ]);

        $this->actingAs($user)
            ->get(route('almacen.dashboard'))
            ->assertOk()
            ->assertSee($warehouse->name);
    }

    public function test_other_roles_cannot_access_the_warehouse_dashboard(): void
    {
        $driver = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($driver)
            ->get(route('almacen.dashboard'))
            ->assertForbidden();
    }
}
