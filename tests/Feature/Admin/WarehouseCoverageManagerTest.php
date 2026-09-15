<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\WarehousesManager;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fase 4 — Resolución logística.
 *
 * Gestión mínima de cobertura (qué HUB atiende qué estado/ciudad),
 * integrada en Admin\WarehousesManager en vez de un módulo nuevo.
 */
class WarehouseCoverageManagerTest extends TestCase
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

    public function test_admin_can_add_a_city_specific_coverage_to_a_warehouse(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $warehouse->id)
            ->set('coverageState', 'Carabobo')
            ->set('coverageCity', 'Valencia')
            ->call('addCoverage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('warehouse_coverages', [
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_add_a_whole_state_coverage_to_a_warehouse(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $warehouse->id)
            ->set('coverageState', 'Zulia')
            ->set('coverageWholeState', true)
            ->call('addCoverage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('warehouse_coverages', [
            'warehouse_id' => $warehouse->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_add_a_duplicate_coverage_to_the_same_warehouse(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $warehouse->id)
            ->set('coverageState', 'Carabobo')
            ->set('coverageCity', 'Valencia')
            ->call('addCoverage');

        $component->assertSet('coverageError', 'Este almacén ya tiene esa cobertura activa.');
        $this->assertSame(1, WarehouseCoverage::count());
    }

    public function test_admin_cannot_add_a_conflicting_coverage_to_a_different_warehouse(): void
    {
        $admin = $this->createAdmin();
        $existingHub = Warehouse::factory()->create();
        $newHub = Warehouse::factory()->create();

        WarehouseCoverage::create([
            'warehouse_id' => $existingHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $component = Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $newHub->id)
            ->set('coverageState', 'Carabobo')
            ->set('coverageCity', 'Valencia')
            ->call('addCoverage');

        $this->assertNotNull($component->get('coverageError'));
        $this->assertSame(1, WarehouseCoverage::count());
    }

    /**
     * Un HUB con cobertura de todo un estado y otro HUB con cobertura
     * de una ciudad específica dentro de ese mismo estado NO es un
     * conflicto: es el caso normal de "la ciudad específica manda
     * sobre el fallback estatal" (ver reglas de resolución).
     */
    public function test_admin_can_add_overlapping_state_and_city_coverage_from_different_warehouses(): void
    {
        $admin = $this->createAdmin();
        $stateHub = Warehouse::factory()->create();
        $cityHub = Warehouse::factory()->create();

        WarehouseCoverage::create([
            'warehouse_id' => $stateHub->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $cityHub->id)
            ->set('coverageState', 'Zulia')
            ->set('coverageCity', 'Maracaibo')
            ->call('addCoverage')
            ->assertSet('coverageError', null);

        $this->assertSame(2, WarehouseCoverage::count());
    }

    public function test_admin_can_toggle_coverage_active(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        $coverage = WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoverageActive', $coverage->id);

        $this->assertFalse($coverage->fresh()->is_active);
    }

    public function test_admin_cannot_activate_a_coverage_that_would_conflict_with_another_active_one(): void
    {
        $admin = $this->createAdmin();
        $hubA = Warehouse::factory()->create();
        $hubB = Warehouse::factory()->create();

        WarehouseCoverage::create([
            'warehouse_id' => $hubA->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $inactiveCoverage = WarehouseCoverage::create([
            'warehouse_id' => $hubB->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoverageActive', $inactiveCoverage->id);

        $this->assertFalse($inactiveCoverage->fresh()->is_active);
    }

    public function test_admin_cannot_add_coverage_with_a_state_outside_the_catalog(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $warehouse->id)
            ->set('coverageState', 'Estado Que No Existe')
            ->set('coverageWholeState', true)
            ->call('addCoverage')
            ->assertHasErrors(['coverageState']);

        $this->assertSame(0, WarehouseCoverage::count());
    }

    public function test_admin_cannot_add_coverage_with_a_city_that_does_not_belong_to_the_selected_state(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::factory()->create();

        Livewire::actingAs($admin)
            ->test(WarehousesManager::class)
            ->call('toggleCoveragePanel', $warehouse->id)
            ->set('coverageState', 'Carabobo')
            ->set('coverageCity', 'Maracaibo')
            ->call('addCoverage')
            ->assertHasErrors(['coverageCity']);

        $this->assertSame(0, WarehouseCoverage::count());
    }
}
