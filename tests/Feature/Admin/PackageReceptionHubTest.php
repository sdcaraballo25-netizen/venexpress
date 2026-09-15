<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\PackageReception;
use App\Models\Package;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 5A — Recepción/verificación interna en HUB, desde Admin.
 *
 * Cubre Admin\PackageReception::receive() usando
 * HubReceptionService::receiveAtWarehouse(): selección de almacén,
 * banner correcto según el resultado de LogisticsResolutionService, y
 * validaciones de formulario.
 */
class PackageReceptionHubTest extends TestCase
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

    public function test_admin_can_receive_a_package_and_it_matches_the_resolved_destination(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $warehouse->id)
            ->call('receive')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Recepción registrada. Este almacén es el destino final de este paquete.');

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => $warehouse->id,
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);
    }

    public function test_admin_can_receive_a_package_that_must_redistribute_to_a_different_warehouse(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $receivingWarehouse = Warehouse::factory()->create(['is_active' => true]);
        $resolvedWarehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $resolvedWarehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $receivingWarehouse->id)
            ->call('receive')
            ->assertSet('errorMessage', null)
            ->assertSet(
                'successMessage',
                'Recepción registrada. Este paquete debe redistribuirse hacia otro almacén '
                    .'(pendiente de programar en una fase posterior).'
            );

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => $receivingWarehouse->id,
            'destination_warehouse_id' => $resolvedWarehouse->id,
        ]);
    }

    public function test_admin_receiving_with_no_coverage_shows_the_review_banner(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $warehouse->id)
            ->call('receive')
            ->assertSet('errorMessage', null)
            ->assertSee('Requiere revisión manual');

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_EN_HUB,
            'destination_warehouse_id' => null,
            'destination_resolution_status' => 'no_coverage',
        ]);
    }

    public function test_admin_cannot_receive_without_selecting_a_warehouse(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->call('receive')
            ->assertHasErrors(['warehouseId']);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);
    }

    public function test_admin_cannot_receive_at_an_inactive_warehouse(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => false]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $warehouse->id)
            ->call('receive')
            ->assertHasErrors(['warehouseId']);

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Fase 5B-1 — recepción de transferencia HUB -> HUB (EN_TRANSITO_NACIONAL)
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_receive_a_transfer_from_another_hub(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $originWarehouse = Warehouse::factory()->create();
        $destinationWarehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationWarehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $originWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $destinationWarehouse->id)
            ->call('receive')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Recepción registrada. Este almacén es el destino final de este paquete.');

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_EN_HUB,
            'current_warehouse_id' => $destinationWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
        ]);
    }

    public function test_admin_cannot_receive_a_transfer_at_the_wrong_warehouse(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $correctWarehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);
        $wrongWarehouse = Warehouse::factory()->create();

        WarehouseCoverage::create([
            'warehouse_id' => $correctWarehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'destination_warehouse_id' => $correctWarehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($admin)
            ->test(PackageReception::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('search')
            ->set('warehouseId', $wrongWarehouse->id)
            ->call('receive')
            ->assertSet(
                'errorMessage',
                'Este paquete no tiene como destino este almacén. Verifica que lo estás recibiendo en el '
                    .'HUB correcto.'
            );

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);
    }
}
