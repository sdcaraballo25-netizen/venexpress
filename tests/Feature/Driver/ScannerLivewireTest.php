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
 * "Un paquete = un solo escaneo": searchPackage() ejecuta la operación
 * resuelta en la misma llamada, salvo que sea exactamente la misma
 * guía que se acaba de procesar (lastProcessedPackageId), caso en el
 * que queda pendiente de confirmación explícita (confirmOperation()).
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

    public function test_a_single_scan_executes_collection_when_active_route_is_hub_transfer(): void
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

        // Un solo escaneo -> searchPackage() ya ejecuta la operación,
        // sin un segundo toque de confirmOperation().
        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.')
            ->assertSet('pendingOperation', null)
            ->assertSet('lastProcessedPackageId', $package->id);

        $package->refresh();
        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_a_single_scan_executes_hub_departure_when_active_route_is_hub_distribution_and_package_is_en_hub(): void
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
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Salida de HUB registrada correctamente. El paquete quedó en tránsito nacional.')
            ->assertSet('pendingOperation', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);
    }

    public function test_a_single_scan_executes_hub_arrival_when_package_already_en_transito(): void
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
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Llegada al almacén destino registrada correctamente.')
            ->assertSet('pendingOperation', null);

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
     * El escenario real que protege lastProcessedPackageId: un driver de
     * distribución escanea la salida de HUB (EN_HUB -> EN_TRANSITO) y,
     * por error o costumbre, vuelve a acercar la MISMA guía física de
     * inmediato. Sin esta protección, ese segundo escaneo encadenaría
     * sola la llegada al almacén (hub_arrival) segundos después de la
     * salida, sin que el driver lo pidiera. En vez de eso, el segundo
     * escaneo de la misma guía queda pendiente de confirmación
     * explícita.
     */
    public function test_rescanning_the_same_package_immediately_does_not_auto_execute_the_next_stage(): void
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
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('successMessage', 'Salida de HUB registrada correctamente. El paquete quedó en tránsito nacional.')
            ->assertSet('lastProcessedPackageId', $package->id);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);

        // Reescaneo inmediato de la MISMA guía: no debe ejecutar
        // "hub_arrival" solo, aunque ya sea la operación elegible según
        // el nuevo current_status.
        $component
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', null)
            ->assertSet('pendingOperation', 'hub_arrival');

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);

        // Recién con la confirmación explícita se ejecuta la llegada.
        $component
            ->call('confirmOperation', 'hub_arrival')
            ->assertSet('successMessage', 'Llegada al almacén destino registrada correctamente.')
            ->assertSet('pendingOperation', null);

        $this->assertNull($package->fresh()->driver_id);
    }

    /**
     * Si el estado del paquete cambió entre identificarlo (en el
     * camino de confirmación pendiente) y confirmar -- por ejemplo,
     * otro proceso ya lo movió -- el snapshot capturado lo detecta y
     * bloquea la ejecución sin llamar al servicio.
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

        // Forzamos el camino de confirmación pendiente (equivalente a
        // haber procesado ya esta guía en un escaneo previo) sin
        // depender de qué operación exacta sea la siguiente.
        $component = Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('lastProcessedPackageId', $package->id)
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
            ->set('lastProcessedPackageId', $package->id)
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
     * Regla de negocio que debe seguir intacta incluso en el camino de
     * confirmación pendiente: driver_id nunca se asigna por el solo
     * hecho de identificar la guía, solo al confirmar y ejecutar la
     * operación.
     */
    public function test_driver_id_is_not_assigned_by_a_pending_rescan_until_confirmed(): void
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
            ->set('lastProcessedPackageId', $package->id)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('pendingOperation', 'collection');

        // Identificado (camino pendiente), pero todavía sin dueño.
        $this->assertNull($package->fresh()->driver_id);

        $component->call('confirmOperation', 'collection');

        // Recién tras confirmar se asigna el repartidor.
        $this->assertSame($driver->id, $package->fresh()->driver_id);
    }

    /**
     * Un paquete ya asignado a otro repartidor sigue rechazándose: el
     * escaneo identifica y ejecuta "collection" en el mismo paso (ya no
     * hace falta un segundo toque), pero
     * LogisticsScanService::scanCollection() rechaza la ejecución sin
     * asignar ni mutar nada.
     */
    public function test_scanning_a_package_already_assigned_to_another_driver_is_rejected_in_a_single_scan(): void
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

        Livewire::actingAs($user)
            ->test(Scanner::class)
            ->set('trackingNumber', $package->tracking_number)
            ->call('searchPackage')
            ->assertSet('errorMessage', 'Este paquete ya está asignado a otro repartidor.')
            ->assertSet('pendingOperation', null);

        $package->refresh();
        $this->assertSame(Package::STATUS_RECIBIDO_AGENCIA, $package->current_status);
        $this->assertSame($otherDriver->id, $package->driver_id);
    }
}
