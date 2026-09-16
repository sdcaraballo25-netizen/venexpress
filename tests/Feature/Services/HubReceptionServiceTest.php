<?php

namespace Tests\Feature\Services;

use App\Models\Ally;
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

    /*
    |--------------------------------------------------------------------------
    | Auto-liberación (Fase 5B-2 encadenada) — HubReceptionService ahora
    | invoca siempre HubReleaseService::release() cuando el almacén
    | recibido ya es el destino final, sin filtrar por modalidad:
    | release() ya decide correctamente según pickup_mode/requires_delivery.
    |--------------------------------------------------------------------------
    */

    public function test_receive_at_warehouse_auto_releases_to_listo_retiro_when_this_warehouse_is_the_final_hub_and_pickup_mode_is_hub(): void
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
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
        $this->assertSame($warehouse->id, $received->current_warehouse_id);
        $this->assertSame($warehouse->id, $received->destination_warehouse_id);
    }

    /**
     * pickup_mode = ALLY todavía necesita un traslado físico más (HUB ->
     * Aliado) antes de estar realmente en su último punto: la
     * auto-liberación despacha de inmediato hacia el Aliado
     * (EN_TRANSITO_NACIONAL), igual que ya hacía el botón manual
     * "Liberar" — release() decide esto, no HubReceptionService.
     */
    public function test_receive_at_warehouse_auto_dispatches_toward_the_ally_when_pickup_mode_is_ally_at_the_final_hub(): void
    {
        $ally = $this->createAlly();
        $pickupAlly = $this->createAlly([
            'business_name' => 'Farmacia Aliada Valencia',
            'is_verified_destination' => true,
        ]);
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
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $received->current_status);
        $this->assertNotSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
    }

    /**
     * pickup_mode = ALLY pero sin un punto de retiro coherente todavía
     * configurado: release() rechaza la liberación (configuración
     * inconsistente) y el paquete se queda EN_HUB, exactamente el mismo
     * respaldo manual de siempre.
     */
    public function test_receive_at_warehouse_stays_en_hub_when_ally_pickup_configuration_is_incoherent(): void
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
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => null,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
    }

    /**
     * requires_delivery = true en su HUB destino final: ya no debe
     * pasar por EN_TRANSITO_NACIONAL, se libera directo a LISTO_RETIRO
     * (mismo destino final que retiro en HUB) para que
     * PackageService::claimForDelivery() lo reclame desde ahí, tal como
     * ya hace hoy con paquetes recibidos en un Aliado.
     */
    public function test_receive_at_warehouse_auto_releases_to_listo_retiro_when_requires_delivery_is_true(): void
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
            'requires_delivery' => true,
            'pickup_mode' => null,
            'delivery_address' => 'Av. Bolívar, Valencia',
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $warehouse);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
        $this->assertNotSame(Package::STATUS_EN_TRANSITO_NACIONAL, $received->current_status);
        $this->assertTrue($received->isAvailableForDeliveryClaim());
    }

    public function test_receive_at_warehouse_stays_en_hub_when_warehouse_is_intermediate_even_with_pickup_mode_hub(): void
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
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $received = $this->service()->receiveAtWarehouse($package, $ally->user_id, $receivingWarehouse);

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertSame($resolvedWarehouse->id, $received->destination_warehouse_id);
    }

    /*
    |--------------------------------------------------------------------------
    | receiveTransferAtWarehouse() — Fase 5B-1, HUB -> HUB directo
    |--------------------------------------------------------------------------
    */

    public function test_transfer_reception_transitions_en_transito_to_en_hub(): void
    {
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
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame(Package::STATUS_EN_HUB, $received->current_status);
    }

    public function test_transfer_reception_sets_current_warehouse_id_to_the_destination_hub(): void
    {
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
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame($destinationWarehouse->id, $received->current_warehouse_id);
    }

    public function test_transfer_reception_keeps_destination_warehouse_id_as_the_destination_hub(): void
    {
        $ally = $this->createAlly();
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
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame($destinationWarehouse->id, $received->destination_warehouse_id);
    }

    public function test_transfer_reception_releases_driver_id(): void
    {
        $ally = $this->createAlly();
        $driver = Driver::factory()->create();
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
            'driver_id' => $driver->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertNull($received->driver_id);
    }

    public function test_transfer_reception_creates_exactly_one_package_history_event(): void
    {
        $ally = $this->createAlly();
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
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $this->service()->receiveTransferAtWarehouse($package, $ally->user_id, $destinationWarehouse);

        $this->assertSame(1, PackageHistory::where('package_id', $package->id)->count());

        $history = PackageHistory::where('package_id', $package->id)->first();
        $this->assertSame(PackageHistory::EVENT_RECEPCION, $history->event_type);
        $this->assertSame(Package::STATUS_EN_HUB, $history->status);
    }

    public function test_transfer_reception_rejects_the_wrong_destination_warehouse(): void
    {
        $ally = $this->createAlly();
        $correctWarehouse = Warehouse::factory()->create(['state' => 'Carabobo', 'city' => 'Valencia']);
        $wrongWarehouse = Warehouse::factory()->create(['is_active' => true]);

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
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Este paquete no tiene como destino este almacén. Verifica que lo estás recibiendo en el HUB correcto.'
        );

        $this->service()->receiveTransferAtWarehouse($package, $ally->user_id, $wrongWarehouse);
    }

    public function test_transfer_reception_rejects_when_package_is_not_en_transito(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
        ]);

        $this->expectException(RuntimeException::class);

        $this->service()->receiveTransferAtWarehouse($package, $ally->user_id, $warehouse);
    }

    public function test_transfer_reception_rejects_an_inactive_warehouse(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => false]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Solo se puede registrar recepción interna en un almacén activo.');

        $this->service()->receiveTransferAtWarehouse($package, $ally->user_id, $warehouse);
    }

    public function test_transfer_reception_rejects_when_coverage_no_longer_resolves(): void
    {
        $ally = $this->createAlly();
        $warehouse = Warehouse::factory()->create(['is_active' => true]);

        // Sin WarehouseCoverage configurada: la resolución en vivo da
        // no_coverage. No se decide nada automáticamente — se rechaza
        // por completo, el paquete no cambia.
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Carabobo',
            'destination_city' => 'Valencia',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
        ]);

        try {
            $this->service()->receiveTransferAtWarehouse($package, $ally->user_id, $warehouse);
            $this->fail('Se esperaba una RuntimeException por falta de cobertura.');
        } catch (RuntimeException $e) {
            // esperado
        }

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertNull($package->current_warehouse_id);
    }

    public function test_transfer_reception_auto_releases_to_listo_retiro_when_pickup_mode_is_hub(): void
    {
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
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
        $this->assertSame($destinationWarehouse->id, $received->current_warehouse_id);
    }

    /**
     * pickup_mode = ALLY en una recepción de transferencia HUB -> HUB:
     * igual que en receiveAtWarehouse(), todavía falta el traslado
     * físico hasta el Aliado, así que se auto-despacha de inmediato
     * hacia EN_TRANSITO_NACIONAL, nunca LISTO_RETIRO.
     */
    public function test_transfer_reception_auto_dispatches_toward_the_ally_when_pickup_mode_is_ally(): void
    {
        $ally = $this->createAlly();
        $pickupAlly = $this->createAlly([
            'business_name' => 'Farmacia Aliada Valencia',
            'is_verified_destination' => true,
        ]);
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
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => false,
            'pickup_mode' => Package::PICKUP_MODE_ALLY,
            'pickup_ally_id' => $pickupAlly->id,
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $received->current_status);
        $this->assertNotSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
    }

    /**
     * Reproduce exactamente el caso real VEN-20260916-541029: paquete
     * con requires_delivery = true, recibido como transferencia directa
     * HUB -> HUB en su almacén destino resuelto (Mérida). Antes del
     * fix, se quedaba en EN_HUB indefinidamente porque
     * attemptAutoRelease() solo consideraba pickup_mode = HUB. Debe
     * quedar LISTO_RETIRO, listo para que un repartidor de entrega lo
     * reclame — nunca EN_TRANSITO_NACIONAL ni EN_HUB.
     */
    public function test_transfer_reception_auto_releases_to_listo_retiro_when_requires_delivery_is_true(): void
    {
        $ally = $this->createAlly(['city' => 'Cumaná', 'state' => 'Sucre']);
        $originWarehouse = Warehouse::factory()->create(['name' => 'Hub central']);
        $destinationWarehouse = Warehouse::factory()->create([
            'name' => 'Almacen Merida',
            'state' => 'Mérida',
            'city' => 'Mérida',
        ]);

        WarehouseCoverage::create([
            'warehouse_id' => $destinationWarehouse->id,
            'state' => 'Mérida',
            'city' => 'Mérida',
            'is_active' => true,
        ]);

        $package = $this->createPackage($ally, [
            'tracking_number' => 'VEN-TEST-541029',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'destination_state' => 'Mérida',
            'destination_city' => 'Mérida',
            'current_warehouse_id' => $originWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => LogisticsResolutionResult::STATUS_RESOLVED,
            'requires_delivery' => true,
            'pickup_mode' => null,
            'pickup_ally_id' => null,
            'delivery_address' => 'Calle principal, Mérida',
        ]);

        $received = $this->service()->receiveTransferAtWarehouse(
            $package,
            $ally->user_id,
            $destinationWarehouse
        );

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $received->current_status);
        $this->assertNotSame(Package::STATUS_EN_TRANSITO_NACIONAL, $received->current_status);
        $this->assertNotSame(Package::STATUS_EN_HUB, $received->current_status);
        $this->assertSame($destinationWarehouse->id, $received->current_warehouse_id);
        $this->assertTrue($received->isAvailableForDeliveryClaim());
    }
}
