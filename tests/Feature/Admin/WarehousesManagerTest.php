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

    /**
     * Fase 1: Estado/Ciudad dejan de ser texto libre. Seleccionar un
     * Estado debe cargar la lista de ciudades dependientes servida
     * por VenezuelaLocationService, y limpiar cualquier ciudad ya
     * elegida (mismo criterio que Admin\RoutesManager).
     */
    public function test_selecting_a_state_loads_its_dependent_cities(): void
    {
        $admin = $this->createAdmin();

        $component = Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('startCreating')
            ->set('city', 'Valencia')
            ->set('state', 'Zulia');

        $component->assertSet('city', '');
        $this->assertContains('Maracaibo', $component->get('cities'));
        $this->assertNotContains('Valencia', $component->get('cities'));
    }

    /**
     * El formulario ya no acepta cualquier texto para Estado/Ciudad:
     * ambos deben pertenecer al catálogo único (VenezuelaLocationService).
     */
    public function test_admin_cannot_create_a_warehouse_with_a_state_outside_the_catalog(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('startCreating')
            ->set('name', 'Almacén Inválido')
            ->set('state', 'Estado Que No Existe')
            ->set('address', 'Zona Industrial')
            ->call('save')
            ->assertHasErrors(['state']);

        $this->assertDatabaseMissing('warehouses', [
            'name' => 'Almacén Inválido',
        ]);
    }

    /**
     * Una ciudad que no pertenece al estado seleccionado (aunque
     * exista en el catálogo para otro estado) también debe rechazarse.
     */
    public function test_admin_cannot_create_a_warehouse_with_a_city_that_does_not_belong_to_the_selected_state(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('startCreating')
            ->set('name', 'Almacén Inconsistente')
            ->set('state', 'Carabobo')
            ->set('city', 'Maracaibo')
            ->set('address', 'Zona Industrial')
            ->call('save')
            ->assertHasErrors(['city']);

        $this->assertDatabaseMissing('warehouses', [
            'name' => 'Almacén Inconsistente',
        ]);
    }

    /**
     * Al editar un almacén existente, la ciudad guardada debe seguir
     * disponible en el select (la lista de ciudades se precarga para
     * el estado ya guardado).
     */
    public function test_editing_a_warehouse_preloads_the_city_options_for_its_state(): void
    {
        $admin = $this->createAdmin();

        $warehouse = Warehouse::create([
            'name' => 'Almacén Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('editWarehouse', $warehouse->id);

        $component->assertSet('city', 'Maracaibo');
        $this->assertContains('Maracaibo', $component->get('cities'));
    }
}
