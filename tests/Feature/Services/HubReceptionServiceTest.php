<?php

namespace Tests\Feature\Services;

use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\HubReceptionService;
use App\Services\LogisticsResolutionResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 5A — Recepción/verificación interna en HUB.
 *
 * HubReceptionService::receiveAtWarehouse() es el reemplazo, para el
 * camino administrativo, del viejo receive(): además de la misma
 * transición RECOLECTADO_VENEXPRESS -> EN_HUB, resuelve el HUB
 * destino con LogisticsResolutionService (única fuente de verdad,
 * sin comparación de texto) y deja fijados current_warehouse_id /
 * destination_warehouse_id / destination_resolution_status.
 */
class HubReceptionServiceTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function service(): HubReceptionService
    {
        return app(HubReceptionService::class);
    }

    public function test_reception_succeeds_and_sets_current_warehouse_id(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => null,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertSame($warehouse->id, $received->current_warehouse_id);
    }

    public function test_destination_warehouse_id_is_set_when_resolution_is_resolved(): void
    {
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

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $receivingWarehouse);

        $this->assertSame($resolvedWarehouse->id, $received->destination_warehouse_id);
        $this->assertSame(LogisticsResolutionResult::STATUS_RESOLVED, $received->destination_resolution_status);
    }

    public function test_destination_warehouse_id_matches_the_receiving_warehouse_when_it_is_already_the_destination(): void
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
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame($warehouse->id, $received->destination_warehouse_id);
        $this->assertSame($warehouse->id, $received->current_warehouse_id);
    }

    public function test_no_coverage_still_completes_reception_but_leaves_destination_warehouse_id_null(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertSame($warehouse->id, $received->current_warehouse_id);
        $this->assertNull($received->destination_warehouse_id);
        $this->assertSame(LogisticsResolutionResult::STATUS_NO_COVERAGE, $received->destination_resolution_status);
    }

    public function test_ambiguous_coverage_still_completes_reception_but_leaves_destination_warehouse_id_null(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
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

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertNull($received->destination_warehouse_id);
        $this->assertSame(LogisticsResolutionResult::STATUS_AMBIGUOUS, $received->destination_resolution_status);
    }

    public function test_invalid_destination_still_completes_reception_but_leaves_destination_warehouse_id_null(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'destination_state' => 'Estado Que No Existe',
            'destination_city' => 'Ciudad Falsa',
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertNull($received->destination_warehouse_id);
        $this->assertSame(LogisticsResolutionResult::STATUS_INVALID, $received->destination_resolution_status);
    }

    public function test_driver_id_is_released_on_reception(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);
        $driver = Driver::factory()->create();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertNull($received->driver_id);
    }

    public function test_rejects_reception_at_an_inactive_warehouse(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => false]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Solo se puede registrar recepción interna en un almacén activo.');

        $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);
    }

    public function test_rejects_reception_when_package_is_not_recolectado_venexpress(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);
    }

    public function test_reception_creates_exactly_one_package_history_event(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());

        $history = PackageHistory::where('package_id', $package->id)->first();
        $this->assertSame(PackageHistory::EVENT_RECEPCION, $history->event_type);
        $this->assertSame(Package::STATUS_EN_HUB, $history->status);
    }

    public function test_reception_does_not_create_or_modify_any_route(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $routesBefore = Route::count();

        $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame($routesBefore, Route::count());
    }
}
