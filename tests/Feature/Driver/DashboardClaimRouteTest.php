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
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la integración del Dashboard web del Driver con el
 * autoservicio de rutas (RouteService::availableRoutesFor() /
 * claimRoute()), reemplazando el mensaje obsoleto de "el administrador
 * te asignará una ruta".
 */
class DashboardClaimRouteTest extends TestCase
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

    public function test_driver_sees_compatible_available_routes_when_no_active_route(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();

        $compatible = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);
        $this->createDraftRoute(Route::TYPE_DELIVERY, $ally);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Rutas disponibles')
            ->assertSee($compatible->name)
            ->assertDontSee('el administrador te asignará')
            ->assertDontSee('Cuando el administrador te asigne');
    }

    public function test_driver_can_claim_a_route_from_the_dashboard(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('claimRoute', $route->id);

        $route->refresh();
        $this->assertSame(Route::STATUS_ASSIGNED, $route->status);
        $this->assertSame($driver->id, $route->driver_id);

        $stillAvailable = app(RouteService::class)
            ->availableRoutesFor($driver)
            ->pluck('id');

        $this->assertFalse($stillAvailable->contains($route->id));
    }

    public function test_claimed_route_stops_appearing_as_available_and_shows_as_active(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('claimRoute', $route->id);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee($route->name)
            ->assertSee('Iniciar ruta')
            ->assertDontSee('Rutas disponibles');
    }

    public function test_shows_neutral_message_when_no_routes_are_available(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);

        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->assertSee('Sin rutas disponibles')
            ->assertDontSee('el administrador');
    }

    public function test_driver_can_start_the_route_after_claiming_it(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $component = Livewire::actingAs($user)->test(Dashboard::class);
        $component->call('claimRoute', $route->id);
        $component->call('startRoute');

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);
    }
}
