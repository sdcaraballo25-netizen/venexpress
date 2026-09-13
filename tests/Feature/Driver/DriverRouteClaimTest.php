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
 * Cubre el autoservicio de "tomar ruta" (Fase 2 de la auditoría del
 * módulo Driver HUB): el Admin ya no asigna repartidores — el Driver
 * ve rutas disponibles y las toma él mismo, con las validaciones de
 * compatibilidad y concurrencia acordadas.
 */
class DriverRouteClaimTest extends TestCase
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

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test', ['driver'])->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function createDraftRoute(string $routeType, Ally $ally, ?string $state = null): Route
    {
        $creator = User::factory()->create();

        $route = Route::create([
            'city' => 'Caracas',
            'state' => $state ?? 'Distrito Capital',
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

    public function test_hub_driver_can_claim_a_compatible_draft_route(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $this->postJson(
            "/api/driver/route/{$route->id}/claim",
            [],
            $this->authHeaders($user)
        )
            ->assertOk()
            ->assertJsonPath('route.status', Route::STATUS_ASSIGNED);

        $route->refresh();
        $this->assertSame($driver->id, $route->driver_id);
        $this->assertSame(Route::STATUS_ASSIGNED, $route->status);
    }

    public function test_available_routes_only_lists_compatible_unclaimed_drafts(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();

        $compatible = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);
        $this->createDraftRoute(Route::TYPE_DELIVERY, $ally);

        $alreadyTaken = $this->createDraftRoute(Route::TYPE_HUB_DISTRIBUTION, $ally);
        $alreadyTaken->update(['status' => Route::STATUS_ASSIGNED, 'driver_id' => $driver->id]);

        $response = $this->getJson('/api/driver/route/available', $this->authHeaders($user))
            ->assertOk();

        $ids = collect($response->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($compatible->id));
        $this->assertCount(1, $ids);
    }

    public function test_delivery_driver_cannot_claim_a_hub_route(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_DELIVERY);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $this->postJson(
            "/api/driver/route/{$route->id}/claim",
            [],
            $this->authHeaders($user)
        )->assertStatus(422);

        $this->assertNull($route->fresh()->driver_id);
    }

    public function test_cannot_claim_a_route_already_taken_by_another_driver(): void
    {
        [$firstUser, $firstDriver] = $this->createDriverUser(Driver::TYPE_HUB);
        [$secondUser] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        // El primer driver toma la ruta.
        $this->postJson(
            "/api/driver/route/{$route->id}/claim",
            [],
            $this->authHeaders($firstUser)
        )->assertOk();

        // El segundo driver la intenta tomar después: debe fallar
        // porque ya no está disponible (re-chequeo dentro del lock).
        $this->postJson(
            "/api/driver/route/{$route->id}/claim",
            [],
            $this->authHeaders($secondUser)
        )->assertStatus(422);

        $this->assertSame($firstDriver->id, $route->fresh()->driver_id);
    }

    public function test_driver_cannot_claim_a_second_route_while_one_is_active(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();

        $firstRoute = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);
        $secondRoute = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);

        $headers = $this->authHeaders($user);

        $this->postJson("/api/driver/route/{$firstRoute->id}/claim", [], $headers)
            ->assertOk();

        $this->postJson("/api/driver/route/{$secondRoute->id}/claim", [], $headers)
            ->assertStatus(422);

        $this->assertNull($secondRoute->fresh()->driver_id);
    }

    public function test_cannot_claim_a_route_that_is_not_in_draft(): void
    {
        [$user, $driver] = $this->createDriverUser(Driver::TYPE_HUB);
        $ally = $this->createAlly();
        $route = $this->createDraftRoute(Route::TYPE_HUB_TRANSFER, $ally);
        $route->update(['status' => Route::STATUS_CANCELLED]);

        $this->postJson(
            "/api/driver/route/{$route->id}/claim",
            [],
            $this->authHeaders($user)
        )->assertStatus(422);
    }
}
