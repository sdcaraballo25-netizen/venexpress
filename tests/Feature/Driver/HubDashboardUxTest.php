<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Dashboard;
use App\Livewire\Driver\PackageDetail;
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
 * Cubre el ajuste de UX del panel del repartidor HUB: el dashboard,
 * RouteDetail y PackageDetail deben orientarse a "Mi ruta -> Paradas ->
 * Escanear -> Progreso" para driver_type = hub, sin tocar el
 * comportamiento existente para driver_type = delivery.
 */
class HubDashboardUxTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createDriverUser(string $driverType): array
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

    public function test_hub_dashboard_shows_scanner_as_primary_action(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Mi ruta actual')
            ->assertSee('Escanear paquetes')
            ->assertSee('Abrir escáner');
    }

    public function test_hub_dashboard_shows_active_route_and_next_stop_details(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);

        $ally = $this->createAlly([
            'business_name' => 'Agencia Barquisimeto Centro',
            'city' => 'Barquisimeto',
        ]);
        $visitedAlly = $this->createAlly([
            'business_name' => 'Agencia Ya Visitada',
        ]);
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Barquisimeto',
            'state' => 'Lara',
            'name' => 'Traslado Barquisimeto',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $visitedAlly->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_VISITED,
            'visited_at' => now(),
            'packages_collected_count' => 4,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 2,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee($route->name)
            ->assertSee('Traslado a hub')
            ->assertSee('Agencias aliadas → Hub Venexpress')
            ->assertSee('Próxima parada')
            ->assertSee('Agencia Barquisimeto Centro')
            ->assertSee('Barquisimeto')
            ->assertSee('1 de 2')
            ->assertSee('4 paquetes procesados');
    }

    public function test_hub_dashboard_shows_next_stop_for_hub_distribution_using_warehouse(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $creator = User::factory()->create();

        $warehouse = Warehouse::create([
            'name' => 'Almacén Maracay',
            'city' => 'Maracay',
            'state' => 'Aragua',
            'address' => 'Zona Industrial',
            'is_active' => true,
        ]);

        $route = Route::create([
            'city' => 'Maracay',
            'state' => 'Aragua',
            'name' => 'Distribución Maracay',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
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
            ->test(Dashboard::class)
            ->assertSee('Distribución a almacén')
            ->assertSee('Hub Venexpress → Almacén destino')
            ->assertSee('Almacén Maracay')
            ->assertSee('Maracay');
    }

    public function test_hub_dashboard_differentiates_completed_stops_from_finished_route(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta completa pero en curso',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
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
        ]);

        $component = Livewire::actingAs($user)->test(Dashboard::class);

        $component
            ->assertSee('1 de 1')
            ->assertSee('Paradas completadas')
            ->assertSee('En curso')
            ->assertSee('Finalizar ruta');
    }

    public function test_hub_dashboard_completes_route_using_existing_route_service_flow(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta a finalizar',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
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
        ]);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('completeRoute');

        $this->assertSame(Route::STATUS_COMPLETED, $route->fresh()->status);
    }

    public function test_delivery_dashboard_keeps_the_generic_layout_unchanged(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertDontSee('Mi ruta actual')
            ->assertDontSee('Abrir escáner')
            ->assertSee('Ruta actual')
            ->assertSee('Acciones rápidas');
    }

    public function test_route_detail_keeps_access_to_scanner_when_route_is_in_progress(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta con escáner',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
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

        $this->actingAs($user)
            ->get(route('repartidor.route-detail', $route->id))
            ->assertOk()
            ->assertSee('Escanear paquetes')
            ->assertSee('Abrir escáner')
            ->assertSee(route('repartidor.scanner'));
    }

    public function test_package_detail_hides_delivery_actions_for_hub_driver(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();

        $package = $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        $component = Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $package->id])
            ->assertDontSee('Iniciar entrega')
            ->assertDontSee('Confirmar entrega')
            ->assertDontSee('Registrar cobro COD')
            ->assertSee('Volver a mi ruta')
            ->assertSee('Escanear paquetes');

        // Defensa en profundidad: aunque la UI no muestre el botón, el
        // método sigue rechazando la acción si se invoca directamente.
        $component->call('startDelivery');

        $this->assertSame(
            Package::STATUS_RECOLECTADO_VENEXPRESS,
            $package->fresh()->current_status
        );
    }

    public function test_package_detail_keeps_delivery_actions_for_delivery_driver(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();

        // "Iniciar entrega" sigue visible para Delivery en el estado
        // que le corresponde (sin tocar PackageService ni sus estados).
        $recolectado = $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_RECOLECTADO_VENEXPRESS,
        ]);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $recolectado->id])
            ->assertSee('Iniciar entrega')
            ->assertDontSee('Volver a mi ruta');

        // "Confirmar entrega" sigue siendo una acción real y funcional
        // para Delivery, usando exactamente el flujo/servicio existente.
        $enReparto = $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        Livewire::actingAs($user)
            ->test(PackageDetail::class, ['packageId' => $enReparto->id])
            ->assertSee('Confirmar entrega')
            ->call('completeDelivery');

        $this->assertSame(
            Package::STATUS_ENTREGADO,
            $enReparto->fresh()->current_status
        );
    }
}
