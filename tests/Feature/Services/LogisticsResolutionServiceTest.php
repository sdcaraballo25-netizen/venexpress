<?php

namespace Tests\Feature\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\LogisticsResolutionResult;
use App\Services\LogisticsResolutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 4 — Resolución logística.
 *
 * LogisticsResolutionService es una capa de LECTURA: resuelve a qué
 * HUB pertenece un destino y si un paquete ya está en su HUB, pero
 * nunca cambia current_status, current_warehouse_id, PackageHistory
 * ni crea rutas. Esos casos de "no side effects" se verifican
 * explícitamente al final de este archivo.
 */
class LogisticsResolutionServiceTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function service(): LogisticsResolutionService
    {
        return app(LogisticsResolutionService::class);
    }

    public function test_a_warehouse_covers_a_specific_city(): void
    {
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $result = $this->service()->resolveDestinationWarehouse('Carabobo', 'Valencia');

        $this->assertTrue($result->isResolved());
        $this->assertSame($warehouse->id, $result->warehouseId);
        $this->assertSame(LogisticsResolutionResult::RULE_CITY, $result->rule);
    }

    public function test_a_warehouse_covers_a_whole_state(): void
    {
        $warehouse = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        $result = $this->service()->resolveDestinationWarehouse('Zulia', 'Cabimas');

        $this->assertTrue($result->isResolved());
        $this->assertSame($warehouse->id, $result->warehouseId);
        $this->assertSame(LogisticsResolutionResult::RULE_STATE, $result->rule);
    }

    public function test_city_specific_coverage_has_priority_over_state_coverage(): void
    {
        $stateHub = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo']);
        $cityHub = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Cabimas']);

        WarehouseCoverage::create([
            'warehouse_id' => $stateHub->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $cityHub->id,
            'state' => 'Zulia',
            'city' => 'Cabimas',
            'is_active' => true,
        ]);

        $result = $this->service()->resolveDestinationWarehouse('Zulia', 'Cabimas');

        $this->assertTrue($result->isResolved());
        $this->assertSame($cityHub->id, $result->warehouseId);
        $this->assertSame(LogisticsResolutionResult::RULE_CITY, $result->rule);

        // La cobertura estatal sigue resolviendo el resto del estado.
        $fallback = $this->service()->resolveDestinationWarehouse('Zulia', 'Maracaibo');
        $this->assertTrue($fallback->isResolved());
        $this->assertSame($stateHub->id, $fallback->warehouseId);
        $this->assertSame(LogisticsResolutionResult::RULE_STATE, $fallback->rule);
    }

    public function test_two_warehouses_can_have_different_coverages(): void
    {
        $carabobo = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);
        $zulia = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo']);

        WarehouseCoverage::create([
            'warehouse_id' => $carabobo->id,
            'state' => 'Carabobo',
            'city' => null,
            'is_active' => true,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $zulia->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        $this->assertSame(
            $carabobo->id,
            $this->service()->resolveDestinationWarehouse('Carabobo', 'Valencia')->warehouseId
        );

        $this->assertSame(
            $zulia->id,
            $this->service()->resolveDestinationWarehouse('Zulia', 'Maracaibo')->warehouseId
        );
    }

    public function test_resolves_destination_for_a_package(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $result = $this->service()->resolveForPackage($package);

        $this->assertTrue($result->isResolved());
        $this->assertSame($warehouse->id, $result->warehouseId);
    }

    public function test_is_at_destination_warehouse_returns_true_when_current_warehouse_matches(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $warehouse->id,
        ]);

        $this->assertTrue($this->service()->isAtDestinationWarehouse($package));
    }

    public function test_is_at_destination_warehouse_returns_false_when_in_a_different_warehouse(): void
    {
        $ally = $this->createAlly();
        $destinationHub = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);
        $otherHub = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo']);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationHub->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_warehouse_id' => $otherHub->id,
        ]);

        $this->assertFalse($this->service()->isAtDestinationWarehouse($package));
    }

    public function test_no_coverage_returns_a_controlled_result_without_inventing_a_destination(): void
    {
        $result = $this->service()->resolveDestinationWarehouse('Carabobo', 'Valencia');

        $this->assertSame(LogisticsResolutionResult::STATUS_NO_COVERAGE, $result->status);
        $this->assertNull($result->warehouseId);
        $this->assertNotSame('', $result->reason);
    }

    public function test_ambiguous_coverage_is_detected_and_rejected(): void
    {
        $hubA = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);
        $hubB = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $hubA->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $hubB->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $result = $this->service()->resolveDestinationWarehouse('Carabobo', 'Valencia');

        $this->assertSame(LogisticsResolutionResult::STATUS_AMBIGUOUS, $result->status);
        $this->assertNull($result->warehouseId);
    }

    public function test_ambiguous_whole_state_coverage_is_detected_and_rejected(): void
    {
        $hubA = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Maracaibo']);
        $hubB = Warehouse::factory()->create(['state' => 'Zulia', 'city' => 'Cabimas']);

        WarehouseCoverage::create([
            'warehouse_id' => $hubA->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $hubB->id,
            'state' => 'Zulia',
            'city' => null,
            'is_active' => true,
        ]);

        $result = $this->service()->resolveDestinationWarehouse('Zulia', 'Maracaibo');

        $this->assertSame(LogisticsResolutionResult::STATUS_AMBIGUOUS, $result->status);
        $this->assertNull($result->warehouseId);
    }

    public function test_invalid_state_is_rejected(): void
    {
        $result = $this->service()->resolveDestinationWarehouse('Estado Que No Existe', null);

        $this->assertSame(LogisticsResolutionResult::STATUS_INVALID, $result->status);
        $this->assertNull($result->warehouseId);
    }

    public function test_invalid_city_for_the_given_state_is_rejected(): void
    {
        // Maracaibo es una ciudad válida, pero pertenece a Zulia, no a Carabobo.
        $result = $this->service()->resolveDestinationWarehouse('Carabobo', 'Maracaibo');

        $this->assertSame(LogisticsResolutionResult::STATUS_INVALID, $result->status);
        $this->assertNull($result->warehouseId);
    }

    public function test_packages_with_pickup_ally_id_remain_compatible(): void
    {
        $ally = $this->createAlly();
        $pickupAlly = $this->createAlly(['city' => 'Valencia', 'state' => 'Carabobo']);
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'requires_delivery' => false,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $result = $this->service()->resolveForPackage($package);

        $this->assertTrue($result->isResolved());
        $this->assertSame($warehouse->id, $result->warehouseId);
        $this->assertSame($pickupAlly->id, $package->pickup_ally_id);
    }

    public function test_resolving_does_not_change_current_status(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->service()->resolveForPackage($package);
        $this->service()->isAtDestinationWarehouse($package);

        $this->assertSame(
            Package::STATUS_EN_TRANSITO_NACIONAL,
            $package->fresh()->current_status
        );
    }

    public function test_resolving_does_not_create_package_history_records(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $this->service()->resolveForPackage($package);
        $this->service()->isAtDestinationWarehouse($package);

        $this->assertSame(0, PackageHistory::where('package_id', $package->id)->count());
    }

    public function test_resolving_does_not_create_routes(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $routesBefore = Route::count();

        $this->service()->resolveForPackage($package);
        $this->service()->isAtDestinationWarehouse($package);

        $this->assertSame($routesBefore, Route::count());
    }
}
