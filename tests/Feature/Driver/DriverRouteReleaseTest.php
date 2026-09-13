<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\Dashboard;
use App\Models\Ally;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\RouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la liberación de una ruta ya tomada (STATUS_ASSIGNED) pero
 * todavía no iniciada: RouteService::release() y
 * Dashboard::releaseRoute(). Distinto de cancel()/cancelRoute(): la
 * ruta logística NO se cancela, solo vuelve a STATUS_DRAFT sin dueño.
 */
class DriverRouteReleaseTest extends TestCase
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

    private function createDraftRoute(string $routeType, Ally $ally): Route
    {
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta de prueba '.$routeType,
            'created_by' => $creator->id,
            'status' => Route::STATUS_DRAFT,
            'route_type' => $routeType,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return $route;
    }

    public function test_driver_can_release_an_assigned_route_before_starting_it(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $routeService = app(RouteService::class);
        $routeService->claimRoute($route, $driver, (int) $user->id);

        $released = $routeService->release($route->fresh(), $driver, (int) $user->id);

        $this->assertSame(Route::STATUS_DRAFT, $released->status);
        $this->assertNull($released->driver_id);

        $this->assertTrue(
            $routeService->availableRoutesFor($driver)->pluck('id')->contains($route->id)
        );
    }

    public function test_release_fails_when_route_is_in_progress(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $routeService = app(RouteService::class);
        $routeService->claimRoute($route, $driver, (int) $user->id);
        $routeService->start($route->fresh(), (int) $user->id);

        $this->expectException(RuntimeException::class);

        $routeService->release($route->fresh(), $driver, (int) $user->id);
    }

    public function test_release_fails_when_route_does_not_belong_to_the_driver(): void
    {
        [$ownerUser, $ownerDriver] = $this->createDriverUser();
        [, $otherDriver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $routeService = app(RouteService::class);
        $routeService->claimRoute($route, $ownerDriver, (int) $ownerUser->id);

        $this->expectException(RuntimeException::class);

        try {
            $routeService->release($route->fresh(), $otherDriver, (int) $ownerUser->id);
        } finally {
            $route->refresh();
            $this->assertSame(Route::STATUS_ASSIGNED, $route->status);
            $this->assertSame($ownerDriver->id, $route->driver_id);
        }
    }

    public function test_release_fails_when_route_is_still_a_draft(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $this->expectException(RuntimeException::class);

        app(RouteService::class)->release($route, $driver, (int) $user->id);
    }

    public function test_driver_can_release_the_route_from_the_dashboard(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        app(RouteService::class)->claimRoute($route, $driver, (int) $user->id);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Liberar ruta')
            ->call('releaseRoute');

        $route->refresh();
        $this->assertSame(Route::STATUS_DRAFT, $route->status);
        $this->assertNull($route->driver_id);
    }

    public function test_dashboard_release_route_flashes_error_when_no_assigned_route(): void
    {
        [$user] = $this->createDriverUser();

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('releaseRoute')
            ->assertSee('No tienes una ruta asignada para liberar.');
    }

    public function test_release_button_is_not_shown_once_route_is_in_progress(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $routeService = app(RouteService::class);
        $routeService->claimRoute($route, $driver, (int) $user->id);
        $routeService->start($route->fresh(), (int) $user->id);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertDontSee('Liberar ruta');
    }
}
