<?php

namespace Tests\Feature\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
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

    public function test_departs_package_from_hub_successfully(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
        ]);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('package.current_status', Package::STATUS_EN_TRANSITO_NACIONAL);

        $package->refresh();
        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->current_status);
        $this->assertSame($driver->id, $package->driver_id);

        $this->assertSame(
            1,
            $package->histories()
                ->where('event_type', PackageHistory::EVENT_SALIDA)
                ->count()
        );
    }

    public function test_arrives_package_at_destination_warehouse_without_changing_status(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse();
        $route = $this->startDistributionRoute($driver, $warehouse);
        $stop = $route->stops()->first();

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
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

    public function test_arrival_rejects_when_destination_does_not_match_any_stop(): void
    {
        [$user, $driver] = $this->createHubDriverUser();
        $warehouse = $this->createWarehouse([
            'city' => 'Valencia',
            'state' => 'Carabobo',
        ]);
        $this->startDistributionRoute($driver, $warehouse);

        $ally = $this->createAlly();
        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Maracaibo',
            'destination_state' => 'Zulia',
        ]);

        $headers = $this->authHeaders($user);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $headers)->assertOk();

        $this->postJson('/api/driver/hub/arrival', [
            'tracking_number' => $package->tracking_number,
        ], $headers)->assertStatus(422);

        $this->assertSame(
            Package::STATUS_EN_TRANSITO_NACIONAL,
            $package->fresh()->current_status
        );
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
}
