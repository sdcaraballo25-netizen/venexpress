<?php

namespace Tests\Feature\Driver;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre el bloque "hub_scan" que GET /api/driver/route ahora agrega a
 * su respuesta para repartidores de HUB, replicando el mismo cálculo
 * que ya usaba Livewire\Driver\Dashboard::render() (bloque "ACCIÓN DE
 * ESCANEO PRINCIPAL (HUB)") — la app Flutter no tenía ninguna fuente
 * de este dato, así que mostraba tarjetas de paquetes genéricas que ni
 * siquiera el propio dashboard web muestra para este rol
 * (@unless($isHub) en dashboard.blade.php).
 */
class DriverRouteActiveHubScanTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    private function createDriverUser(string $driverType): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'password' => bcrypt('password-seguro'),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
            'driver_type' => $driverType,
        ]);

        return [$user, $driver];
    }

    private function authHeaders(User $user): array
    {
        $token = $this->postJson('/api/driver/login', [
            'email' => $user->email,
            'password' => 'password-seguro',
            'device_name' => 'telefono-de-prueba',
        ])->json('token');

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_hub_transfer_route_reports_collection_operation(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly(['business_name' => 'Agencia Los Próceres']);

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

        $this->getJson('/api/driver/route', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('hub_scan.operation', 'collection')
            ->assertJsonPath('hub_scan.title', 'RECOLECCIÓN EN ALIADO')
            ->assertJsonPath('hub_scan.next_stop_name', 'Agencia Los Próceres')
            ->assertJsonPath('hub_scan.pending_count', null);
    }

    public function test_hub_distribution_route_reports_departure_then_arrival(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);

        $warehouse = Warehouse::create([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => 'Zona Industrial',
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

        $headers = $this->authHeaders($user);

        // Fase 1: todavía no ha sacado nada del HUB -> 'hub_departure'.
        $this->getJson('/api/driver/route', $headers)
            ->assertOk()
            ->assertJsonPath('hub_scan.operation', 'hub_departure')
            ->assertJsonPath('hub_scan.title', 'SALIDA DESDE HUB')
            ->assertJsonPath('hub_scan.pending_count', 1);

        $this->postJson('/api/driver/hub/dispatch', [
            'tracking_number' => $package->tracking_number,
        ], $headers)->assertOk();

        // Fase 2: ya tiene paquetes en tránsito bajo su custodia -> 'hub_arrival'.
        $this->getJson('/api/driver/route', $headers)
            ->assertOk()
            ->assertJsonPath('hub_scan.operation', 'hub_arrival')
            ->assertJsonPath('hub_scan.title', 'RECEPCIÓN EN ALMACÉN')
            ->assertJsonPath('hub_scan.warehouse_name', 'Almacén Valencia')
            ->assertJsonPath('hub_scan.pending_count', 1);
    }

    public function test_hub_scan_is_null_for_a_delivery_driver(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de reparto',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_DELIVERY,
        ]);

        $this->getJson('/api/driver/route', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('hub_scan', null);
    }

    public function test_hub_scan_is_null_when_route_is_only_assigned_not_started(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Recolección Caracas',
            'driver_id' => $driver->id,
            'created_by' => $user->id,
            'status' => Route::STATUS_ASSIGNED,
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $this->getJson('/api/driver/route', $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('hub_scan', null);
    }
}
