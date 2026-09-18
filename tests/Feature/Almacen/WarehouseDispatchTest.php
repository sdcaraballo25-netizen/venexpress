<?php

namespace Tests\Feature\Almacen;

use App\Livewire\Almacen\Dashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * El almacén recibe paquetes (EN_TRANSITO_NACIONAL -> LISTO_RETIRO,
 * igual que Ally\PackageReception para agencias) y luego los
 * despacha: a un cliente que lo retira en persona, o a un repartidor
 * con una ruta de reparto en curso hacia esa ciudad.
 */
class WarehouseDispatchTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createWarehouseUser(Warehouse $warehouse): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ALMACEN,
            'status' => User::STATUS_ACTIVE,
            'warehouse_id' => $warehouse->id,
        ]);
    }

    public function test_scanning_arrival_moves_package_to_listo_retiro_and_clears_driver(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);

        $ally = $this->createAlly();
        $hubDriver = Driver::factory()->create(['driver_type' => Driver::TYPE_HUB]);

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $hubDriver->id,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('scanArrival')
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
        $this->assertNull($package->driver_id);
    }

    public function test_warehouse_can_deliver_a_pickup_package_to_the_client(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
            'recipient_id_doc' => 'V-87654321',
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliverToClient')
            ->assertHasNoErrors();

        $this->assertSame(Package::STATUS_ENTREGADO, $package->fresh()->current_status);
    }

    public function test_warehouse_cannot_deliver_to_client_with_wrong_id_doc(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
            'recipient_id_doc' => 'V-87654321',
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->set('recipientIdDoc', 'V-00000000')
            ->call('deliverToClient');

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    public function test_warehouse_can_assign_a_delivery_package_to_a_driver_with_a_matching_route(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'driver_type' => Driver::TYPE_DELIVERY,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $route = Route::create([
            'name' => 'Ruta Valencia',
            'city' => 'Valencia',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
            'created_by' => $almacenUser->id,
            'started_at' => now(),
        ]);

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->call('assignToDriver', $route->id)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_warehouse_staff_from_a_different_warehouse_cannot_receive_a_package(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('scanArrival');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    /**
     * searchDispatch()/deliverToClient()/assignToDriver() solo
     * validaban tracking_number + estado, sin verificar que el
     * paquete tuviera como destino ESTE almacén — a diferencia de
     * scanArrival(), que sí lo hacía. Cualquier empleado de almacén
     * podía despachar (a cliente o a repartidor) una guía destinada a
     * otro almacén en cualquier parte del país.
     */
    public function test_search_dispatch_rejects_a_package_destined_to_another_warehouse(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->assertSet('dispatchPackage', null)
            ->assertSet('dispatchError', fn ($value) => ! empty($value));
    }

    public function test_warehouse_staff_from_a_different_warehouse_cannot_deliver_to_client(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => false,
            'recipient_id_doc' => 'V-87654321',
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliverToClient');

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    public function test_warehouse_staff_from_a_different_warehouse_cannot_assign_to_a_driver(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $driverUser = User::factory()->create(['role' => User::ROLE_REPARTIDOR, 'status' => User::STATUS_ACTIVE]);
        $driver = Driver::factory()->create([
            'user_id' => $driverUser->id,
            'driver_type' => Driver::TYPE_DELIVERY,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $route = Route::create([
            'name' => 'Ruta Maracaibo',
            'city' => 'Maracaibo',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
            'created_by' => $almacenUser->id,
            'started_at' => now(),
        ]);

        $package = $this->createPackage($ally, [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('assignToDriver', $route->id);

        $package->refresh();

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
        $this->assertNull($package->driver_id);
    }

    /**
     * El lector QR usa un único punto de entrada (scanGuide) que
     * decide sola la acción: si la guía todavía no llegó, la recibe;
     * si ya está LISTO_RETIRO, abre el panel de despacho.
     */
    public function test_scan_guide_receives_an_incoming_package(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number)
            ->assertHasNoErrors();

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);
    }

    public function test_scan_guide_opens_dispatch_panel_for_a_package_ready_for_pickup(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_LISTO_RETIRO,
        ]);

        $component = Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number);

        $this->assertSame($package->id, $component->get('dispatchPackage')->id);
    }

    public function test_scan_guide_shows_an_error_for_an_unknown_tracking_number(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', 'VEN-NO-EXISTE')
            ->assertSet('scanError', fn ($value) => ! empty($value));
    }
}
