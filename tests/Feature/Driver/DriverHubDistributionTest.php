<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\LogisticsScanService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre el tramo HUB Distribución (HUB -> almacén propio de Venexpress
 * destino): POST /api/driver/hub/dispatch y POST /api/driver/hub/arrival.
 *
 * Deliberadamente NO toca nada del tramo de Recolección
 * (DriverApiFlowTest) ni de la app de Delivery
 * (DriverDeliveryController) — son actores distintos.
 */
class DriverHubDistributionTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createHubDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_HUB,
        ]);

        return [$user, $driver];
    }

    private function createWarehouse(array $overrides = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => 'Zona Industrial',
            'is_active' => true,
        ], $overrides));
    }

    private function startDistributionRoute(Driver $driver, Warehouse $warehouse): Route
    {
        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Distribución Valencia',
            'driver_id' => $driver->id,
            'created_by' => $driver->user_id,
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

        return $route;
    }

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test', ['driver'])->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    /**
     * Fase 5B-1: scanHubDeparture()/scanHubArrival() ya no comparan
     * texto de ciudad/estado — exigen destination_warehouse_id
     * resuelto por LogisticsResolutionService. Crea la cobertura
     * necesaria para que resuelva hacia $warehouse.
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

    public function test_departs_package_from_hub_successfully(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_EN_TRANSITO_NACIONAL);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);

        // current_warehouse_id NO se toca durante el tránsito: sigue
        // reflejando el último HUB físicamente confirmado (ninguno
        // todavía en este test, ya que el paquete no pasó por una
        // recepción de origen real) — el punto crítico es que el
        // despacho no lo modifica.
        $this->assertNull($package->current_warehouse_id);

        // Una salida HUB->HUB genera exactamente UN evento de salida
        // relevante (EVENT_SALIDA), con el route_stop_id de la parada
        // de destino ya incluido en ese mismo evento — sin un
        // EVENT_TRANSFERENCIA adicional.
        $salidaHistories = $package->histories()
            ->where('event_type', PackageHistory::EVENT_SALIDA)
            ->get();

        $this->assertCount(1, $salidaHistories);
        $this->assertSame(
            $this->routeStopIdFor($driver, $warehouse),
            $salidaHistories->first()->route_stop_id
        );

        $this->assertSame(
            0,
            $package->histories()->where('event_type', PackageHistory::EVENT_TRANSFERENCIA)->count()
        );
    }

    private function routeStopIdFor(Driver $driver, Warehouse $warehouse): int
    {
        return RouteStop::query()
            ->whereHas('route', fn ($q) => $q->where('driver_id', $driver->id))
            ->where('warehouse_id', $warehouse->id)
            ->value('id');
    }

    public function test_arrives_package_at_destination_warehouse_without_changing_status(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);
        $route = $this->startDistributionRoute($driver, $warehouse);
        $stop = $route->stops()->first();

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $headers = $this->authHeaders($user);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $headers)->assertOk();

        $this->postJson('/api/driver/hub/arrival', [
            'tracking_number' => $package->tracking_number,
        ], $headers)
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_EN_TRANSITO_NACIONAL);

        $package->refresh();

        // Punto crítico del diseño: la llegada al almacén NO cambia
        // current_status. Debe seguir EN_TRANSITO_NACIONAL para que la
        // app de Delivery (que no se toca en este flujo) lo siga
        // pudiendo reclamar sin ningún cambio de su parte.
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);

        // La custodia del driver de HUB se libera.
        $this->assertNull($package->driver_id);

        $stop->refresh();
        $this->assertSame(RouteStop::STATUS_VISITED, $stop->status);
        $this->assertNotNull($stop->visited_at);

        $arrivalHistory = $package->histories()
            ->where('event_type', PackageHistory::EVENT_TRANSFERENCIA)
            ->first();

        $this->assertNotNull($arrivalHistory);
        $this->assertSame($stop->id, $arrivalHistory->route_stop_id);
    }

    public function test_dispatch_rejects_package_not_in_hub(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422);

        $this->assertSame(
            Package::STATUS_RECIBIDO_AGENCIA,
            $package->fresh()->current_status
        );
    }

    public function test_dispatch_rejects_driver_without_active_distribution_route(): void
    {
        [$user, $driver] = $this->createHubDriverUser();

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422);
    }

    public function test_dispatch_rejects_package_already_taken_by_another_distribution_driver(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        [$otherUser, $otherDriver] = $this->createHubDriverUser();

        $warehouse = $this->createWarehouse();
        $this->startDistributionRoute($driver, $warehouse);
        $this->startDistributionRoute($otherDriver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'driver_id' => $otherDriver->id,
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422);
    }

    /**
     * Fase 5B-1: scanHubDeparture() ya garantiza que un paquete solo
     * sale hacia una ruta cuya parada coincide con su
     * destination_warehouse_id (ver test_dispatch_rejects_a_package_
     * whose_destination_is_a_different_hub más abajo) — así que en el
     * flujo normal la llegada nunca puede desajustarse. Este test
     * cubre la resolución de scanHubArrival()/resolveDestinationStop()
     * de forma aislada, simulando un paquete que de algún modo llegó
     * a EN_TRANSITO_NACIONAL bajo la custodia de este driver sin que
     * su destino coincida con ninguna parada de la ruta activa (por
     * ejemplo, datos heredados de antes de esta fase).
     */
    public function test_arrival_rejects_when_destination_warehouse_does_not_match_any_stop(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse([
            'city' => 'Valencia',
            'state' => 'Carabobo',
        ]);
        $otherWarehouse = $this->createWarehouse([
            'name' => 'Almacén Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
        ]);
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'driver_id' => $driver->id,
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
            'destination_warehouse_id' => $otherWarehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/arrival', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))->assertStatus(422);

        $this->assertSame(
            Package::STATUS_EN_TRANSITO_NACIONAL,
            $package->fresh()->current_status
        );
    }

    public function test_dispatch_rejects_a_package_whose_destination_is_a_different_hub(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);
        $this->startDistributionRoute($driver, $warehouse);

        $otherWarehouse = $this->createWarehouse([
            'name' => 'Almacén Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
        ]);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_warehouse_id' => $otherWarehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Esta ruta no tiene como destino el HUB que corresponde a este paquete.',
            ]);

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);
    }

    public function test_dispatch_rejects_a_package_without_a_resolved_destination(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Este paquete no tiene un HUB destino resuelto. No puede salir en una '
                    .'transferencia entre HUBs hasta que Admin revise su cobertura logística.',
            ]);

        $this->assertNull($package->fresh()->destination_warehouse_id);
    }

    public function test_current_warehouse_id_keeps_the_origin_hub_after_departure(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $originWarehouse = $this->createWarehouse([
            'name' => 'Almacén Caracas',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);
        $destinationWarehouse = $this->createWarehouse();
        $this->coverWarehouse($destinationWarehouse);
        $this->startDistributionRoute($driver, $destinationWarehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_warehouse_id' => $originWarehouse->id,
            'destination_warehouse_id' => $destinationWarehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))->assertOk();

        // Regla de negocio confirmada: mientras el paquete está en
        // tránsito, current_warehouse_id mantiene el último HUB
        // físicamente confirmado (el de origen) — NUNCA se pone en
        // null durante el tránsito.
        $this->assertSame($originWarehouse->id, $package->fresh()->current_warehouse_id);
        $this->assertSame($destinationWarehouse->id, $package->fresh()->destination_warehouse_id);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);
    }

    public function test_dispatch_rejects_a_package_already_at_its_destination_warehouse(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'current_warehouse_id' => $warehouse->id,
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Este paquete ya está en su HUB destino. No requiere una transferencia entre HUBs.',
            ]);

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);
    }

    public function test_delivery_driver_cannot_use_hub_distribution_endpoints(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => Driver::TYPE_DELIVERY,
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => 'VEN-TEST-INEXISTENTE',
        ], $this->authHeaders($user))
            ->assertStatus(403);

        $this->postJson('/api/driver/hub/arrival', [
            'tracking_number' => 'VEN-TEST-INEXISTENTE',
        ], $this->authHeaders($user))
            ->assertStatus(403);
    }

    /**
     * Antes, cancelar una ruta hub_distribution en curso dejaba el
     * paquete ya despachado "pegado" al driver (driver_id sin cambios)
     * y la parada en PENDING para siempre, aunque la ruta que lo
     * transportaba ya no existiera. RouteService::cancel() ahora
     * libera esa custodia y cierra la parada, igual que hace
     * complete() con las paradas no visitadas.
     */
    public function test_cancelling_an_in_progress_route_releases_dispatched_package_custody(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->coverWarehouse($warehouse);
        $route = $this->startDistributionRoute($driver, $warehouse);
        $stop = $route->stops()->first();

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
            'destination_warehouse_id' => $warehouse->id,
            'destination_resolution_status' => 'resolved',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))->assertOk();

        $package->refresh();
        $this->assertSame($driver->id, $package->driver_id);
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);

        app(RouteService::class)->cancel($route, $user->id);

        $package->refresh();
        $this->assertNull($package->driver_id);
        // El estado no se toca: la recepción manual del paquete sigue
        // el camino de Admin\PackageReception, igual que antes.
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);

        $stop->refresh();
        $this->assertSame(RouteStop::STATUS_SKIPPED, $stop->status);

        $this->assertSame(Route::STATUS_CANCELLED, $route->fresh()->status);

        // El driver ya no aparece bloqueado esperando la llegada de un
        // paquete cuya ruta ya no existe.
        $this->assertFalse(
            Package::query()
                ->where('driver_id', $driver->id)
                ->where('current_status', Package::STATUS_EN_TRANSITO_NACIONAL)
                ->exists()
        );
    }
}
