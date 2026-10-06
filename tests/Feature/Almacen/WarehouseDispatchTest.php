<?php

namespace Tests\Feature\Almacen;

use App\Livewire\Almacen\Dashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * El almacén recibe paquetes con HubReceptionService (igual que la
 * recepción en HUB de Admin): si es su destino se liberan solos
 * (LISTO_RETIRO o PENDIENTE_ENTREGA). Luego los despacha: a quien lo
 * retira en persona (o a un tercero autorizado), o a un repartidor con
 * una ruta de reparto en curso desde este almacén. Tras una entrega
 * fallida decide un nuevo intento o la devolución.
 */
class WarehouseDispatchTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    /** Almacén de Valencia con cobertura activa para Valencia, Carabobo. */
    private function valenciaWarehouse(): Warehouse
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'is_active' => true,
        ]);

        return $warehouse;
    }

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
        $warehouse = $this->valenciaWarehouse();
        $almacenUser = $this->createWarehouseUser($warehouse);

        $ally = $this->createAlly();
        $hubDriver = Driver::factory()->create(['driver_type' => Driver::TYPE_HUB]);

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $hubDriver->id,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('scanArrival')
            ->assertSet('scanError', null)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->current_status);
        $this->assertNull($package->driver_id);
        // Queda registrado el HUB donde está físicamente.
        $this->assertSame($warehouse->id, $package->current_warehouse_id);

        $reception = $package->histories()->latest('id')->first();
        $this->assertSame(PackageHistory::EVENT_RECEPCION, $reception->event_type);
        $this->assertSame(Package::STATUS_LISTO_RETIRO, $reception->status);
        $this->assertSame($almacenUser->id, $reception->scanned_by_user_id);
    }

    /**
     * Antes driver_id se borraba ANTES de llamar a la recepción y fuera
     * de cualquier transacción: si la recepción fallaba, el paquete
     * perdía su custodia igual. Ahora todo ocurre en una transacción.
     */
    public function test_a_failed_arrival_scan_leaves_the_package_untouched(): void
    {
        $warehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        $almacenUser = $this->createWarehouseUser($warehouse);
        $hubDriver = Driver::factory()->create(['driver_type' => Driver::TYPE_HUB]);

        // Destino correcto, pero en tránsito sin cobertura logística
        // configurada: HubReceptionService lo rechaza.
        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $hubDriver->id,
        ]);

        $historiesBefore = $package->histories()->count();

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('scanArrival')
            ->assertNotSet('scanError', null)
            ->assertSet('scanSuccess', null);

        $package->refresh();

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($hubDriver->id, $package->driver_id);
        $this->assertNull($package->current_warehouse_id);
        $this->assertSame($historiesBefore, $package->histories()->count());
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

    public function test_warehouse_pickup_of_a_cod_package_records_the_collection(): void
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
            'is_cod' => true,
            'cod_amount_usd' => 10.00,
            'cod_status' => Package::COD_PENDIENTE,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->set('recipientIdDoc', 'V-87654321')
            ->call('deliverToClient')
            ->assertSet('dispatchError', null);

        $package->refresh();

        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertNotNull($package->cod_collected_at);
        $this->assertNotNull($package->delivery_completed_at);
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
            'state' => 'Carabobo',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => $driver->id,
            'created_by' => $almacenUser->id,
            'started_at' => now(),
            'origin_warehouse_id' => $warehouse->id,
        ]);

        // Ruta en curso hacia la misma ciudad pero desde otro almacén:
        // no se ofrece (regla de zona).
        $otherWarehouse = Warehouse::factory()->create(['city' => 'Valencia', 'state' => 'Carabobo']);
        Route::create([
            'name' => 'Ruta de otro almacén',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'route_type' => Route::TYPE_DELIVERY,
            'status' => Route::STATUS_IN_PROGRESS,
            'driver_id' => Driver::factory()->create(['driver_type' => Driver::TYPE_DELIVERY])->id,
            'created_by' => $almacenUser->id,
            'started_at' => now(),
            'origin_warehouse_id' => $otherWarehouse->id,
        ]);

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'current_warehouse_id' => $warehouse->id,
            'requires_delivery' => true,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->assertSee('Ruta Valencia')
            ->assertDontSee('Ruta de otro almacén')
            ->call('assignToDriver', $route->id)
            ->assertSet('dispatchError', null)
            ->assertHasNoErrors();

        $package->refresh();

        $this->assertSame(Package::STATUS_EN_RUTA, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_scanning_arrival_of_a_home_delivery_leaves_it_pending_delivery(): void
    {
        $warehouse = $this->valenciaWarehouse();
        $almacenUser = $this->createWarehouseUser($warehouse);

        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => true,
            'delivery_address' => 'Av. Bolívar',
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number)
            ->assertSet('scanError', null)
            ->assertSee('pendiente de entrega a domicilio');

        $package->refresh();
        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertSame($warehouse->id, $package->current_warehouse_id);
        $this->assertTrue($package->isAvailableForDeliveryClaim());

        // Un segundo escaneo ya abre el despacho (asignar a repartidor).
        $component = Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number);

        $this->assertSame($package->id, $component->get('dispatchPackage')->id);
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
            'current_status' => Package::STATUS_PENDIENTE_ENTREGA,
            'requires_delivery' => true,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('assignToDriver', $route->id);

        $package->refresh();

        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertNull($package->driver_id);
    }

    /**
     * El lector QR usa un único punto de entrada (scanGuide) que
     * decide sola la acción: si la guía todavía no llegó, la recibe;
     * si ya está LISTO_RETIRO, abre el panel de despacho.
     */
    public function test_scan_guide_receives_an_incoming_package(): void
    {
        $warehouse = $this->valenciaWarehouse();
        $almacenUser = $this->createWarehouseUser($warehouse);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'pickup_mode' => Package::PICKUP_MODE_HUB,
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

    /*
    |--------------------------------------------------------------------------
    | Fase 3
    |--------------------------------------------------------------------------
    */

    public function test_a_package_collected_at_origin_is_received_into_the_hub_network(): void
    {
        $warehouse = $this->valenciaWarehouse();
        $almacenUser = $this->createWarehouseUser($warehouse);
        $driver = Driver::factory()->create(['driver_type' => Driver::TYPE_HUB]);

        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
            'driver_id' => $driver->id,
        ]);

        Livewire::actingAs($almacenUser)
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number)
            ->assertSet('scanError', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_HUB, $package->current_status);
        $this->assertSame($warehouse->id, $package->current_warehouse_id);
        $this->assertNull($package->driver_id);
    }

    public function test_a_package_scanned_at_the_wrong_warehouse_alerts_the_admins(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $valencia = $this->valenciaWarehouse();
        $maracaibo = Warehouse::factory()->create(['name' => 'Almacén Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);
        WarehouseCoverage::create(['warehouse_id' => $maracaibo->id, 'state' => 'Zulia', 'city' => 'Maracaibo', 'is_active' => true]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN_PRINCIPAL, 'status' => User::STATUS_ACTIVE]);

        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
        ]);

        foreach ([1, 2] as $scan) {
            Livewire::actingAs($this->createWarehouseUser($valencia))
                ->test(Dashboard::class)
                ->call('scanGuide', $package->tracking_number)
                ->assertSet('scanError', 'Este paquete no tiene como destino este almacén (va a Almacén Maracaibo). No lo recibas: se avisó al administrador para corregir el envío.');
        }

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
        $this->assertSame(1, \App\Models\Incident::where('package_id', $package->id)->where('type', 'DESTINO_EQUIVOCADO')->count());

        \Illuminate\Support\Facades\Notification::assertSentTo($admin, \App\Notifications\MisroutedPackageAlert::class);
    }

    private function failedDeliveryAt(Warehouse $warehouse): Package
    {
        $driver = Driver::factory()->create(['driver_type' => Driver::TYPE_DELIVERY]);

        return $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'requires_delivery' => true,
            'current_status' => Package::STATUS_ENTREGA_FALLIDA,
            'current_warehouse_id' => $warehouse->id,
            'driver_id' => $driver->id,
            'delivery_attempts' => 1,
            'failed_delivery_reason' => 'CLIENTE_AUSENTE',
            'failed_delivery_at' => now(),
        ]);
    }

    public function test_a_failed_delivery_back_at_the_warehouse_gets_a_new_attempt(): void
    {
        $warehouse = $this->valenciaWarehouse();
        $package = $this->failedDeliveryAt($warehouse);

        Livewire::actingAs($this->createWarehouseUser($warehouse))
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number)
            ->assertSee('Entrega fallida')
            ->assertSee('Destinatario ausente')
            ->call('retryDelivery')
            ->assertSet('dispatchError', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_PENDIENTE_ENTREGA, $package->current_status);
        $this->assertNull($package->driver_id);
        $this->assertSame(1, $package->delivery_attempts);
        $this->assertTrue($package->isAvailableForDeliveryClaim());
    }

    public function test_a_failed_delivery_back_at_the_warehouse_can_be_returned_to_the_sender(): void
    {
        $warehouse = $this->valenciaWarehouse();
        $package = $this->failedDeliveryAt($warehouse);

        Livewire::actingAs($this->createWarehouseUser($warehouse))
            ->test(Dashboard::class)
            ->call('scanGuide', $package->tracking_number)
            ->call('returnToSender')
            ->assertHasErrors(['returnReason' => 'required'])
            ->set('returnReason', 'Destinatario ausente en dos intentos')
            ->call('returnToSender')
            ->assertSet('dispatchError', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_DEVOLUCION, $package->current_status);
        $this->assertNull($package->driver_id);
        $this->assertSame($warehouse->id, $package->current_warehouse_id);
    }

    public function test_an_authorized_third_party_can_pick_up_at_the_warehouse(): void
    {
        Storage::fake('documents');

        $warehouse = $this->valenciaWarehouse();
        $package = $this->createPackage($this->createAlly(), [
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_status' => Package::STATUS_LISTO_RETIRO,
            'current_warehouse_id' => $warehouse->id,
            'requires_delivery' => false,
            'recipient_id_doc' => 'V-87654321',
        ]);

        $component = Livewire::actingAs($this->createWarehouseUser($warehouse))
            ->test(Dashboard::class)
            ->set('dispatchTrackingNumber', $package->tracking_number)
            ->call('searchDispatch')
            ->set('recipientIdDoc', 'V-11111111')
            ->set('byThirdParty', true)
            ->call('deliverToClient')
            ->assertHasErrors(['thirdPartyName', 'thirdPartyIdPhoto', 'recipientIdCopy']);

        $this->assertSame(Package::STATUS_LISTO_RETIRO, $package->fresh()->current_status);

        $component
            ->set('thirdPartyName', 'Pedro Gómez')
            ->set('thirdPartyIdPhoto', UploadedFile::fake()->image('cedula-tercero.jpg'))
            ->set('recipientIdCopy', UploadedFile::fake()->image('copia-cedula.jpg'))
            ->call('deliverToClient')
            ->assertSet('dispatchError', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_ENTREGADO, $package->current_status);
        $this->assertTrue($package->received_by_third_party);
        $this->assertSame('Pedro Gómez', $package->receiver_name);
        $this->assertSame('V-11111111', $package->receiver_id_doc);
        Storage::disk('documents')->assertExists($package->third_party_id_photo_path);
        Storage::disk('documents')->assertExists($package->recipient_id_copy_path);
    }
}
