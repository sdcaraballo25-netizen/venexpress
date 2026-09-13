<?php

namespace Tests\Feature\Driver;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Cubre la finalización de ruta desde el Driver (Fase 3 de la
 * auditoría del módulo Driver HUB): el Admin ya no finaliza rutas.
 */
class DriverRouteCompleteTest extends TestCase
{
    use CreatesTestPackages;
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
            'driver_type' => Driver::TYPE_HUB,
        ]);

        return [$user, $driver];
    }

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test', ['driver'])->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function createInProgressRoute(Driver $driver, Ally $ally): Route
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
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return $route;
    }

    public function test_driver_can_complete_their_own_in_progress_route(): void
    {
        [$user, $driver] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createInProgressRoute($driver, $ally);

        $this->postJson('/api/driver/route/complete', [], $this->authHeaders($user))
            ->assertOk()
            ->assertJsonPath('route.status', Route::STATUS_COMPLETED);

        $this->assertSame(Route::STATUS_COMPLETED, $route->fresh()->status);
    }

    public function test_driver_without_an_in_progress_route_cannot_complete(): void
    {
        [$user, $driver] = $this->createDriverUser();

        $this->postJson('/api/driver/route/complete', [], $this->authHeaders($user))
            ->assertStatus(422);
    }

    public function test_a_driver_cannot_complete_another_drivers_route(): void
    {
        [$ownerUser, $ownerDriver] = $this->createDriverUser();
        [$otherUser] = $this->createDriverUser();
        $ally = $this->createAlly();
        $route = $this->createInProgressRoute($ownerDriver, $ally);

        $this->postJson('/api/driver/route/complete', [], $this->authHeaders($otherUser))
            ->assertStatus(422);

        $this->assertSame(Route::STATUS_IN_PROGRESS, $route->fresh()->status);
    }
}
