<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Scanner;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la interfaz del Scanner HUB (no la lógica de negocio, ya
 * cubierta por ScannerLivewireTest y DriverHubDistributionTest): que
 * cada operación (recolección / salida de HUB / recepción en almacén)
 * se identifique claramente en pantalla, que el vocabulario de Delivery
 * no se filtre a la experiencia HUB, y que el driver pueda encadenar
 * escaneos sin navegar.
 */
class ScannerHubOperationUxTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createDriverUser(string $driverType = Driver::TYPE_HUB): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => $driverType,
        ]);

        return [$user, $driver];
    }

    private function createWarehouse(array $overrides = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'name' => 'Almacén Tucupita',
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'address' => 'Zona Industrial',
            'is_active' => true,
        ], $overrides));
    }

    /**
     * Fase 5B-1: scanHubDeparture()/scanHubArrival() ya no comparan
     * texto de ciudad/estado, exigen destination_warehouse_id
     * resuelto. Crea la cobertura necesaria para que
     * LogisticsResolutionService resuelva hacia $warehouse.
     */
    private function coverWarehouse(Warehouse $warehouse): void
    {
        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => $warehouse->state,
            'city' => $warehouse->city,
            'is_active' => true,
        ]);
    }

    public function test_hub_transfer_shows_collection_operation_with_ally_and_city_before_scanning(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly([
            'business_name' => 'Agencia Tucupita Centro',
            'city' => 'Tucupita',
        ]);

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Recolección Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->assertSee('RECOLECCIÓN EN ALIADO')
            ->assertSee('Agencia Tucupita Centro')
            ->assertSee('Tucupita')
            ->assertDontSee('SALIDA DESDE HUB')
            ->assertDontSee('RECEPCIÓN EN ALMACÉN');
    }

    public function test_hub_distribution_shows_departure_operation_with_warehouse_before_scanning(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->assertSee('SALIDA DESDE HUB')
            ->assertSee('Almacén Tucupita')
            ->assertDontSee('RECOLECCIÓN EN ALIADO')
            ->assertDontSee('RECEPCIÓN EN ALMACÉN')
            ->assertDontSee('Iniciar entrega')
            ->assertDontSee('Confirmar entrega');
    }

    public function test_hub_distribution_defaults_to_arrival_operation_once_driver_has_packages_in_transit(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->assertSee('RECEPCIÓN EN ALMACÉN')
            ->assertDontSee('SALIDA DESDE HUB');
    }

    public function test_successful_collection_scan_shows_processed_confirmation_and_hides_delivery_wording(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
            'requires_delivery' => true,
            'delivery_address' => 'Calle 5, Casa 10',
            'is_cod' => true,
            'cod_amount_usd' => 25.00,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection')
            ->assertSet('processedCount', 0)
            ->call('confirmOperation', 'collection')
            ->assertSee('✓ Paquete procesado')
            ->assertSee($package->tracking_number)
            ->assertSee('Salida registrada desde la agencia')
            ->assertDontSee('Entrega a domicilio')
            ->assertDontSee('Cobro contra entrega')
            ->assertDontSee('Iniciar entrega')
            ->assertSet('processedCount', 1);

        $this->assertSame(1, $component->get('processedCount'));
    }

    public function test_successful_hub_departure_scan_shows_destination_confirmation(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'hub_departure')
            ->call('confirmOperation', 'hub_departure')
            ->assertSee('✓ Paquete procesado')
            ->assertSee('Salida del HUB registrada')
            ->assertSee('Tucupita')
            ->assertDontSee('Confirmar entrega');
    }

    public function test_successful_hub_arrival_scan_shows_warehouse_confirmation(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'hub_arrival')
            ->call('confirmOperation', 'hub_arrival')
            ->assertSee('✓ Paquete procesado')
            ->assertSee('Recepción en almacén registrada')
            ->assertSee('Almacén Tucupita');
    }

    public function test_processed_count_increments_across_sequential_scans_without_navigating(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $first = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);
        $second = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $first->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'collection')
            ->assertSet('processedCount', 1)
            ->call('clearSearch')
            ->assertSet('lastAction', null)
            ->set('trackingNumber', $second->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'collection')
            ->assertSet('processedCount', 2)
            ->assertSet('errorMessage', null);

        $this->assertSame(2, $component->get('processedCount'));
    }

    public function test_collection_progress_message_shows_x_of_y_and_completes_when_no_pending_remain(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $first = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);
        $second = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $component = Livewire::actingAs($user)->test(Scanner::class);

        $component
            ->set('trackingNumber', $first->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'collection')
            ->assertSee('1 de 2 paquetes procesados')
            ->assertSee('Continúa escaneando las guías restantes de este aliado');

        $component
            ->call('clearSearch')
            ->set('trackingNumber', $second->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'collection')
            ->assertSee('2 de 2 paquetes procesados')
            ->assertSee('Operación completada');
    }

    public function test_hub_departure_progress_message_shows_x_of_y_and_completes_when_no_pending_remain(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $first = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);
        $second = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $component = Livewire::actingAs($user)->test(Scanner::class);

        $component
            ->set('trackingNumber', $first->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'hub_departure')
            ->assertSee('1 de 2 paquetes procesados')
            ->assertSee('Continúa escaneando las guías restantes');

        $component
            ->call('clearSearch')
            ->set('trackingNumber', $second->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'hub_departure')
            ->assertSee('2 de 2 paquetes procesados')
            ->assertSee('Operación completada');
    }

    public function test_hub_arrival_progress_message_shows_x_of_y_and_completes_when_no_pending_remain(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);

        $route = Route::create([
            'city' => 'Tucupita',
            'state' => 'Delta Amacuro',
            'name' => 'Distribución Tucupita',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $first = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);
        $second = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'destination_city' => 'Tucupita',
            'destination_state' => 'Delta Amacuro',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $component = Livewire::actingAs($user)->test(Scanner::class);

        $component
            ->set('trackingNumber', $first->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'hub_arrival')
            ->assertSee('1 de 2 paquetes procesados')
            ->assertSee('Continúa escaneando las guías restantes');

        $component
            ->call('clearSearch')
            ->set('trackingNumber', $second->tracking_number)
            ->call('searchPackage')
            ->call('confirmOperation', 'hub_arrival')
            ->assertSee('2 de 2 paquetes procesados')
            ->assertSee('Operación completada');
    }

    public function test_hub_driver_never_sees_delivery_wording_when_active_route_is_unsupported(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de entrega',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_DELIVERY,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->assertDontSee('RECOLECCIÓN EN ALIADO')
            ->assertDontSee('SALIDA DESDE HUB')
            ->assertDontSee('RECEPCIÓN EN ALMACÉN');
    }
}
