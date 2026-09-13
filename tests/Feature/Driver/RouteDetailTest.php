<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Dashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la nueva pantalla de detalle de ruta del repartidor
 * (/repartidor/ruta/{routeId}), que permite ver las paradas y sus
 * aliados/almacenes una vez que la ruta deja de estar en "disponibles"
 * (draft) y pasa a assigned/in_progress.
 */
class RouteDetailTest extends TestCase
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
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'address' => 'Zona Industrial',
            'is_active' => true,
        ], $overrides));
    }

    public function test_driver_can_view_their_own_route_detail(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta propia del repartidor',
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

        $this->actingAs($user)
            ->get(route('repartidor.route-detail', $route->id))
            ->assertOk()
            ->assertSee($route->name)
            ->assertSee($ally->business_name);
    }

    public function test_driver_cannot_view_another_drivers_route_detail(): void
    {
        [$owner, $ownerDriver] = $this->createDriverUser();
        [$intruder] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta ajena',
            'driver_id' => $ownerDriver->id,
            'created_by' => $owner->id,
            'status' => Route::STATUS_ASSIGNED,
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $this->actingAs($intruder)
            ->get(route('repartidor.route-detail', $route->id))
            ->assertNotFound();
    }

    public function test_hub_transfer_route_shows_ally_name_and_city(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly([
            'business_name' => 'Agencia Los Próceres',
            'city' => 'Mérida',
        ]);

        $route = Route::create([
            'city' => 'Mérida',
            'state' => 'Mérida',
            'name' => 'Traslado Mérida',
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
            'status' => RouteStop::STATUS_VISITED,
            'visited_at' => now(),
            'packages_collected_count' => 3,
        ]);

        $this->actingAs($user)
            ->get(route('repartidor.route-detail', $route->id))
            ->assertOk()
            ->assertSee('Agencia Los Próceres')
            ->assertSee('Mérida')
            ->assertSee('3 paquetes recolectados')
            ->assertSee('Visitada');
    }

    public function test_hub_distribution_route_shows_warehouse_name_and_city(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $warehouse = $this->createWarehouse([
            'name' => 'Almacén Central Valencia',
            'city' => 'Valencia',
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

        $this->actingAs($user)
            ->get(route('repartidor.route-detail', $route->id))
            ->assertOk()
            ->assertSee('Almacén Central Valencia')
            ->assertSee('Valencia')
            ->assertSee('Distribución a almacén')
            ->assertSee('Pendiente');
    }

    public function test_dashboard_keeps_showing_active_route_with_details_link_after_claiming_and_starting(): void
    {
        $creator = User::factory()->create();
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta autoservicio',
            'created_by' => $creator->id,
            'status' => Route::STATUS_DRAFT,
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        $component = Livewire::actingAs($user)->test(Dashboard::class);
        $component->call('claimRoute', $route->id);

        $component
            ->assertSee($route->name)
            ->assertSee('Ver detalles')
            ->assertSee('Iniciar ruta');

        $component->call('startRoute');

        $component
            ->assertSee($route->name)
            ->assertSee('Ver detalles')
            ->assertSee('Ruta en curso');

        $this->assertSame(
            Route::STATUS_IN_PROGRESS,
            $route->fresh()->status
        );
    }
}
