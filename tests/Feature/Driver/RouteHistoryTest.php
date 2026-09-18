<?php

namespace Tests\Feature\Driver;

use App\Livewire\Driver\RouteHistory;
use App\Models\Driver;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Historial de rutas del repartidor (/repartidor/historial-rutas),
 * separado de "Mi ruta". Reutiliza driver_id + los 5 estados reales de
 * Route — no agrega tablas, columnas ni estados nuevos.
 */
class RouteHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createDriverUser(): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $driver = Driver::factory()->create([
            'user_id' => $user->id,
            'status' => Driver::STATUS_ACTIVE,
        ]);

        return [$user, $driver];
    }

    private function createRoute(
        string $name,
        string $status,
        ?int $driverId,
        ?int $createdBy
    ): Route {
        return Route::create([
            'name' => $name,
            'status' => $status,
            'route_type' => Route::TYPE_DELIVERY,
            'driver_id' => $driverId,
            'created_by' => $createdBy,
        ]);
    }

    private function routeNames($component): array
    {
        return $component->viewData('routes')->pluck('name')->all();
    }

    public function test_driver_sees_their_routes_in_every_taken_status(): void
    {
        [$user, $driver] = $this->createDriverUser();

        $this->createRoute('Ruta asignada', Route::STATUS_ASSIGNED, $driver->id, $user->id);
        $this->createRoute('Ruta en curso', Route::STATUS_IN_PROGRESS, $driver->id, $user->id);
        $this->createRoute('Ruta completada', Route::STATUS_COMPLETED, $driver->id, $user->id);
        $this->createRoute('Ruta cancelada', Route::STATUS_CANCELLED, $driver->id, $user->id);

        $component = Livewire::actingAs($user)->test(RouteHistory::class);

        $this->assertEqualsCanonicalizing(
            ['Ruta asignada', 'Ruta en curso', 'Ruta completada', 'Ruta cancelada'],
            $this->routeNames($component)
        );
    }

    public function test_driver_does_not_see_another_drivers_routes(): void
    {
        [$owner, $ownerDriver] = $this->createDriverUser();
        [, $otherDriver] = $this->createDriverUser();

        $this->createRoute('Ruta propia', Route::STATUS_COMPLETED, $ownerDriver->id, $owner->id);
        $this->createRoute('Ruta ajena', Route::STATUS_COMPLETED, $otherDriver->id, $owner->id);

        $component = Livewire::actingAs($owner)->test(RouteHistory::class);

        $this->assertSame(['Ruta propia'], $this->routeNames($component));
    }

    public function test_a_route_released_before_starting_no_longer_appears(): void
    {
        [$user, $driver] = $this->createDriverUser();

        // Simula el resultado de RouteService::release(): vuelve a
        // draft con driver_id null. No es un caso a resolver aquí.
        $this->createRoute('Ruta liberada', Route::STATUS_DRAFT, null, $user->id);

        $component = Livewire::actingAs($user)->test(RouteHistory::class);

        $this->assertSame([], $this->routeNames($component));
    }

    public function test_history_links_to_the_existing_route_detail_screen(): void
    {
        [$user, $driver] = $this->createDriverUser();

        $route = $this->createRoute('Ruta completada', Route::STATUS_COMPLETED, $driver->id, $user->id);

        $this->actingAs($user)
            ->get(route('repartidor.route-history'))
            ->assertOk()
            ->assertSee($route->name)
            ->assertSee(route('repartidor.route-detail', $route->id), false);
    }

    /**
     * Un usuario con rol repartidor pero sin Driver asociado ahora lo
     * bloquea EnsureAccountIsApproved (lo trata como PENDIENTE, no
     * como aprobado) antes de que la petición llegue a este
     * componente — ver EnsureAccountIsApprovedTest.
     */
    public function test_route_history_requires_a_driver_profile(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_REPARTIDOR,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('repartidor.route-history'))
            ->assertRedirect(route('account.pending'));
    }
}
