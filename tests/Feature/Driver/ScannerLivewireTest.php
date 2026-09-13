<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Scanner;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre el Scanner Livewire (/repartidor/escanear) tras adaptarlo para
 * soportar dos tipos de ruta HUB en la misma pantalla:
 *
 * - hub_transfer (Recolección): delega en
 *   LogisticsScanService::scanCollection().
 * - hub_distribution (Distribución): delega en scanHubDeparture()/
 *   scanHubArrival() según el estado del paquete.
 * - cualquier otro route_type: error explícito, no cae a recolección
 *   por defecto.
 *
 * No prueba de nuevo las reglas de negocio de esos servicios (ya
 * cubiertas por DriverApiFlowTest y DriverHubDistributionTest); solo
 * que el componente delega al método correcto según el tipo de ruta.
 */
class ScannerLivewireTest extends TestCase
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

    public function test_scans_collection_when_active_route_is_hub_transfer(): void
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
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', null)
            ->assertSet('pendingOperation', 'collection');

        // El escaneo por sí solo NUNCA ejecuta la transición.
        $this->assertSame(Package::STATUS_RECIBIDO_AGENCIA, $package->fresh()->current_status);
        $this->assertNull($package->fresh()->driver_id);

        $component
            ->call('confirmOperation', 'collection')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.')
            ->assertSet('pendingOperation', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_scans_hub_departure_when_active_route_is_hub_distribution_and_package_is_en_hub(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = Warehouse::create([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);

        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Distribución Valencia',
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
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', null)
            ->assertSet('pendingOperation', 'hub_departure');

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);

        $component
            ->call('confirmOperation', 'hub_departure')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Salida de HUB registrada correctamente. El paquete quedó en tránsito nacional.');

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_scans_hub_arrival_when_package_already_en_transito(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = Warehouse::create([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);

        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Distribución Valencia',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_DISTRIBUTION,
        ]);

        $stop = RouteStop::create([
            'route_id' => $route->id,
            'warehouse_id' => $warehouse->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', null)
            ->assertSet('pendingOperation', 'hub_arrival');

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
        $this->assertSame($driver->id, $package->fresh()->driver_id);

        $component
            ->call('confirmOperation', 'hub_arrival')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Llegada al almacén destino registrada correctamente.');

        $package->refresh();

        // Punto crítico: la llegada NO cambia current_status (queda
        // intacto para que la app de Delivery lo siga pudiendo
        // reclamar sin ningún cambio de su parte).
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertNull($package->driver_id);

        $this->assertSame(RouteStop::STATUS_VISITED, $stop->fresh()->status);
    }

    public function test_shows_explicit_error_for_unsupported_route_type(): void
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

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet(
                'errorMessage',
                "Tipo de ruta no soportado para escaneo: {$route->route_type}."
            );

        // No debe haber intentado escanear como recolección.
        $this->assertSame(
            Package::STATUS_RECIBIDO_AGENCIA,
            $package->fresh()->current_status
        );
    }

    public function test_shows_error_when_driver_has_no_active_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet(
                'errorMessage',
                'No tienes una ruta en curso. Inicia una ruta antes de escanear paquetes.'
            );
    }

    /**
     * Confirmar dos veces la misma operación (doble clic, o un
     * reintento de red que duplica la petición) no debe ejecutar la
     * transición dos veces. La primera confirmación limpia
     * pendingOperation; la segunda ya no encuentra nada que confirmar.
     */
    public function test_confirming_the_same_operation_twice_does_not_execute_it_twice(): void
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
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection');

        $component
            ->call('confirmOperation', 'collection')
            ->assertSet('successMessage', 'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.')
            ->assertSet('pendingOperation', null);

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);

        // Segundo clic sobre el mismo botón: ya no hay pendingOperation,
        // así que no debe volver a llamar a LogisticsScanService.
        $component
            ->call('confirmOperation', 'collection')
            ->assertSet(
                'errorMessage',
                'No hay ninguna operación pendiente de confirmar. Vuelve a escanear la guía.'
            );

        // El paquete se quedó en RECOLECTADO_VENEXPRESS: el segundo clic
        // no lo hizo avanzar a EN_HUB por su cuenta.
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);
    }

    /**
     * Si el estado del paquete cambió entre identificarlo y confirmar
     * (por ejemplo, otro proceso ya lo movió), el snapshot capturado en
     * el escaneo lo detecta y bloquea la ejecución sin llamar al
     * servicio — sin sustituir la validación real, que además seguiría
     * rechazándolo dentro de LogisticsScanService si se le forzara.
     */
    public function test_stale_snapshot_blocks_execution_without_calling_the_service(): void
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
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection');

        // El paquete cambia de estado por otra vía (otro repartidor,
        // otra pestaña) DESPUÉS de identificarlo pero ANTES de confirmar.
        $package->update(['current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS]);

        $component
            ->call('confirmOperation', 'collection')
            ->assertSet(
                'errorMessage',
                'Este paquete ya cambió de estado. Vuelve a escanearlo para ver la operación disponible.'
            )
            ->assertSet('pendingOperation', null);

        // Sigue en RECOLECTADO_VENEXPRESS (el cambio externo), no pasó a
        // EN_HUB: confirmOperation no llegó a invocar al servicio.
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);
    }

    /**
     * confirmOperation() exige que el parámetro recibido coincida con
     * pendingOperation. Protege contra un botón desincronizado (por
     * ejemplo, una vista vieja en caché) que intente confirmar una
     * operación distinta a la que el servidor calculó.
     */
    public function test_confirm_operation_rejects_a_mismatched_operation_key(): void
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
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection');

        $component
            ->call('confirmOperation', 'hub_arrival')
            ->assertSet(
                'errorMessage',
                'La operación seleccionada ya no coincide con la guía escaneada. Vuelve a escanear.'
            );

        $this->assertSame(Package::STATUS_RECIBIDO_AGENCIA, $package->fresh()->current_status);
    }

    /**
     * Regla de negocio que debe seguir intacta: driver_id nunca se
     * asigna por el solo hecho de escanear/identificar la guía, solo
     * al confirmar y ejecutar la operación.
     */
    public function test_driver_id_is_not_assigned_until_the_operation_is_confirmed(): void
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
        ]);

        $this->assertNull($package->driver_id);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection');

        // Identificado, pero todavía sin dueño.
        $this->assertNull($package->fresh()->driver_id);

        $component->call('confirmOperation', 'collection');

        // Recién tras confirmar se asigna el repartidor.
        $this->assertSame($driver->id, $package->fresh()->driver_id);
    }

    /**
     * Un paquete ya asignado a otro repartidor sigue rechazándose: el
     * escaneo identifica y propone "collection" igual (solo mira
     * current_status), pero LogisticsScanService::scanCollection()
     * rechaza la ejecución al confirmar, sin asignar ni mutar nada.
     */
    public function test_confirming_collection_rejects_a_package_already_assigned_to_another_driver(): void
    {
        [$user, $driver] = $this->createDriverUser();
        [, $otherDriver] = $this->createDriverUser();
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
            'driver_id' => $otherDriver->id,
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('pendingOperation', 'collection');

        $component
            ->call('confirmOperation', 'collection')
            ->assertSet('errorMessage', 'Este paquete ya está asignado a otro repartidor.');

        $package->refresh();
        $this->assertSame(Package::STATUS_RECIBIDO_AGENCIA, $package->current_status);
        $this->assertSame($otherDriver->id, $package->driver_id);
    }
}
