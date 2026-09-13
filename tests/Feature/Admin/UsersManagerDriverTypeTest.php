<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UsersManager;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cubre la configuración de driver_type desde el panel de Admin
 * (crear y editar), ya que ningún flujo anterior permitía crear ni
 * corregir un driver de tipo HUB.
 */
class UsersManagerDriverTypeTest extends TestCase
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

    public function test_admin_can_create_a_hub_driver_with_forced_vehicle_type(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'repartidor')
            ->set('name', 'Repartidor Hub')
            ->set('email', 'hub-driver@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('driver_type', Driver::TYPE_HUB)
            ->set('vehicle_plate', 'HUB-001')
            ->set('phone', '04120000000')
            ->call('requestCreate')
            ->set('adminPassword', 'admin-password')
            ->call('createUser');

        $user = User::where('email', 'hub-driver@example.com')->firstOrFail();
        $driver = $user->driver;

        $this->assertNotNull($driver);
        $this->assertSame(Driver::TYPE_HUB, $driver->driver_type);
        $this->assertSame(Driver::HUB_VEHICLE_TYPE, $driver->vehicle_type);
    }

    public function test_admin_can_create_a_delivery_driver_with_chosen_vehicle_type(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openCreateModal')
            ->set('role', 'repartidor')
            ->set('name', 'Repartidor Delivery')
            ->set('email', 'delivery-driver@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('driver_type', Driver::TYPE_DELIVERY)
            ->set('vehicle_plate', 'DEL-002')
            ->set('vehicle_type', 'Moto')
            ->set('phone', '04120000001')
            ->call('requestCreate')
            ->set('adminPassword', 'admin-password')
            ->call('createUser');

        $user = User::where('email', 'delivery-driver@example.com')->firstOrFail();
        $driver = $user->driver;

        $this->assertNotNull($driver);
        $this->assertSame(Driver::TYPE_DELIVERY, $driver->driver_type);
        $this->assertSame('Moto', $driver->vehicle_type);
    }

    public function test_admin_can_edit_an_existing_delivery_driver_to_hub(): void
    {
        $admin = $this->createAdmin();

        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'driver_type' => Driver::TYPE_DELIVERY,
            'vehicle_type' => 'Moto',
            'vehicle_plate' => 'SASA-01',
        ]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openEditModal', $driverUser->id)
            ->assertSet('editIsDriver', true)
            ->assertSet('edit_driver_type', Driver::TYPE_DELIVERY)
            ->set('edit_driver_type', Driver::TYPE_HUB)
            ->set('edit_vehicle_plate', 'SASA-01')
            ->set('edit_phone', $driver->phone)
            ->call('updateUser');

        $driver->refresh();
        $this->assertSame(Driver::TYPE_HUB, $driver->driver_type);
        $this->assertSame(Driver::HUB_VEHICLE_TYPE, $driver->vehicle_type);
    }

    public function test_editing_hub_to_delivery_requires_a_vehicle_type(): void
    {
        $admin = $this->createAdmin();

        $driverUser = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'driver_type' => Driver::TYPE_HUB,
            'vehicle_type' => Driver::HUB_VEHICLE_TYPE,
        ]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openEditModal', $driverUser->id)
            ->set('edit_driver_type', Driver::TYPE_DELIVERY)
            ->set('edit_vehicle_type', '')
            ->set('edit_vehicle_plate', $driver->vehicle_plate)
            ->set('edit_phone', $driver->phone)
            ->call('updateUser')
            ->assertHasErrors(['edit_vehicle_type' => 'required']);

        $this->assertSame(Driver::TYPE_HUB, $driver->fresh()->driver_type);
    }

    public function test_editing_a_non_driver_user_does_not_touch_any_driver_record(): void
    {
        $admin = $this->createAdmin();

        $ally = User::factory()->create([
            'role' => User::ROLE_ALIADO,
            'status' => User::STATUS_ACTIVE,
        ]);

        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openEditModal', $ally->id)
            ->assertSet('editIsDriver', false)
            ->set('edit_name', 'Aliado Renombrado')
            ->set('edit_email', $ally->email)
            ->call('updateUser');

        $this->assertSame('Aliado Renombrado', $ally->fresh()->name);
        $this->assertDatabaseCount('drivers', 0);
    }

    public function test_duplicate_plate_against_another_driver_is_rejected_but_same_plate_is_allowed(): void
    {
        $admin = $this->createAdmin();

        $otherUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR]);
        Driver::factory()->create([
            'user_id' => $otherUser->id,
            'vehicle_plate' => 'TAKEN-99',
        ]);

        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR]);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'driver_type' => Driver::TYPE_DELIVERY,
            'vehicle_plate' => 'MINE-01',
            'vehicle_type' => 'Moto',
        ]);

        // Mantener su propia placa no debe fallar.
        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openEditModal', $driverUser->id)
            ->set('edit_vehicle_plate', 'MINE-01')
            ->set('edit_phone', $driver->phone)
            ->set('edit_vehicle_type', 'Moto')
            ->call('updateUser')
            ->assertHasNoErrors();

        // Tomar la placa de otro driver sí debe fallar.
        Livewire::actingAs($admin)
            ->test(UsersManager::class)
            ->call('openEditModal', $driverUser->id)
            ->set('edit_vehicle_plate', 'TAKEN-99')
            ->set('edit_phone', $driver->phone)
            ->set('edit_vehicle_type', 'Moto')
            ->call('updateUser')
            ->assertHasErrors(['edit_vehicle_plate' => 'unique']);
    }
}
