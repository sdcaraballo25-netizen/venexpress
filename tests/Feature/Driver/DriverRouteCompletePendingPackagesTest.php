<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Dashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\HubReceptionService;
use App\Services\LogisticsScanService;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la regla de RouteService::complete(): una ruta no puede
 * finalizarse mientras tenga paquetes pendientes, pero la definición
 * de "pendiente" depende de route_type:
 *
 * - delivery: paquetes recolectados por la ruta que no sean ENTREGADO.
 * - hub_transfer: paquetes recolectados por la ruta que no hayan
 *   llegado a EN_HUB.
 * - hub_distribution: paquetes que el driver sacó del HUB
 *   (scanHubDeparture) y todavía no llegaron al almacén
 *   (scanHubArrival), según HubDistributionPhase.
 */
class DriverRouteCompletePendingPackagesTest extends TestCase
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

    private function createInProgressAllyRoute(string $routeType, Driver $driver, Ally $ally): array
    {
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta en curso',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'route_type' => $routeType,
        ]);

        $stop = RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return [$route, $stop];
    }

    private function createWarehouse(array $overrides = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ], $overrides));
    }

    public function test_delivery_route_cannot_complete_until_its_collected_packages_are_entregado(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();
        [$route, $stop] = $this->createInProgressAllyRoute(Route::TYPE_DELIVERY, $driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $routeService = app(RouteService::class);
        $routeService->registerCollection($route, $stop, [$package->id], (int) $user->id);

        try {
            $routeService->complete($route->fresh(), (int) $user->id);
            $this->fail('Se esperaba una RuntimeException por paquetes pendientes.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('entregar', $e->getMessage());
        }

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);

        $package->fresh()->update(['current_status' => Package::STATUS_ENTREGADO]);

        $completed = $routeService->complete($route->fresh(), (int) $user->id);

        $this->assertSame(Route::STATUS_COMPLETED, $completed->status);
    }

    public function test_hub_transfer_route_cannot_complete_until_its_collected_packages_reach_en_hub(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        [$route, $stop] = $this->createInProgressAllyRoute(Route::TYPE_HUB_TRANSFER, $driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        $routeService = app(RouteService::class);
        $routeService->registerCollection($route, $stop, [$package->id], (int) $user->id);

        $this->assertSame(Package::STATUS_RECOLECTADO_VENEXPRESS, $package->fresh()->current_status);

        try {
            $routeService->complete($route->fresh(), (int) $user->id);
            $this->fail('Se esperaba una RuntimeException por paquetes pendientes de recibir en HUB.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('HUB', $e->getMessage());
        }

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);

        // scanHubReception() del Driver quedó bloqueado en Fase 5A; la
        // recepción en HUB ahora es una operación administrativa
        // interna. Para este test solo hace falta llevar el paquete a
        // EN_HUB, así que se usa HubReceptionService::receive() (el
        // método antiguo, sin cambios) directamente, igual que lo
        // haría Admin\PackageReception hasta antes de esta fase.
        app(HubReceptionService::class)->receive(
            package: $package->fresh(),
            userId: (int) $user->id,
            hubLocation: 'HUB Venexpress',
        );

        $this->assertSame(Package::STATUS_EN_HUB, $package->fresh()->current_status);

        $completed = $routeService->complete($route->fresh(), (int) $user->id);

        $this->assertSame(Route::STATUS_COMPLETED, $completed->status);
    }

    public function test_hub_distribution_route_cannot_complete_until_departed_packages_arrive_at_warehouse(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $warehouse = $this->createWarehouse();

        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'name' => 'Distribución Valencia',
            'driver_id' => $driver->id,
            'created_by' => $creator->id,
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
            'current_status' => Package::STATUS_EN_HUB,
            'destination_city' => 'Valencia',
            'destination_state' => 'Carabobo',
        ]);

        $scanService = app(LogisticsScanService::class);
        $routeService = app(RouteService::class);

        $scanService->scanHubDeparture($package, $driver, (int) $user->id);

        $this->assertSame(Package::STATUS_EN_TRANSITO_NACIONAL, $package->fresh()->current_status);

        try {
            $routeService->complete($route->fresh(), (int) $user->id);
            $this->fail('Se esperaba una RuntimeException por paquetes pendientes de llegar al almacén.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('almacén', $e->getMessage());
        }

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);
        $this->assertSame(RouteStop::STATUS_PENDING, $stop->fresh()->status);

        $scanService->scanHubArrival($package->fresh(), $driver, (int) $user->id);

        $completed = $routeService->complete($route->fresh(), (int) $user->id);

        $this->assertSame(Route::STATUS_COMPLETED, $completed->status);
        $this->assertSame(RouteStop::STATUS_VISITED, $stop->fresh()->status);
    }

    public function test_packages_assigned_outside_the_route_do_not_block_completion(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();
        [$route, $stop] = $this->createInProgressAllyRoute(Route::TYPE_DELIVERY, $driver, $ally);

        // Paquete asignado al mismo driver, pero SIN relación con
        // esta ruta (ningún PackageHistory con route_stop_id de sus
        // paradas) — simula el flujo independiente
        // PackageService::claimForDelivery().
        $this->createPackage($ally, [
            'driver_id' => $driver->id,
            'current_status' => Package::STATUS_EN_TRANSITO_NACIONAL,
            'requires_delivery' => true,
            'delivery_status' => Package::DELIVERY_ACCEPTED,
        ]);

        $completed = app(RouteService::class)->complete($route->fresh(), (int) $user->id);

        $this->assertSame(Route::STATUS_COMPLETED, $completed->status);
        $this->assertSame(RouteStop::STATUS_SKIPPED, $stop->fresh()->status);
    }

    public function test_dashboard_complete_route_flashes_pending_hub_reception_error(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        [$route, $stop] = $this->createInProgressAllyRoute(Route::TYPE_HUB_TRANSFER, $driver, $ally);

        $package = $this->createPackage($ally, [
            'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
        ]);

        app(RouteService::class)->registerCollection($route, $stop, [$package->id], (int) $user->id);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('completeRoute')
            ->assertSee('HUB');

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);
    }
}
