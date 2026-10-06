<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoutesManager;
use App\Models\AuditLog;
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
 * Admin asigna una ruta disponible a un repartidor
 * (RouteService::assignDriver()), con las mismas reglas que cuando el
 * repartidor la toma él mismo: tipo compatible y una sola ruta activa.
 */
class RoutesManagerAssignDriverTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    private function draftRoute(string $name = 'Reparto Valencia', string $type = Route::TYPE_DELIVERY): Route
    {
        $route = Route::create([
            'name' => $name,
            'state' => 'Carabobo',
            'city' => 'Valencia',
            'status' => Route::STATUS_DRAFT,
            'route_type' => $type,
            'created_by' => $this->admin->id,
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $this->createAlly()->id,
            'sequence' => 1,
            'status' => RouteStop::STATUS_PENDING,
        ]);

        return $route;
    }

    private function deliveryDriver(array $overrides = []): Driver
    {
        return Driver::factory()->create(array_merge([
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
            'driver_type' => Driver::TYPE_DELIVERY,
        ], $overrides));
    }

    public function test_admin_assigns_a_route_to_a_driver(): void
    {
        $route = $this->draftRoute();
        $driver = $this->deliveryDriver();

        Livewire::actingAs($this->admin)
            ->test(RoutesManager::class)
            ->assertSee('Asignar repartidor')
            ->call('startAssigningDriver', $route->id)
            ->assertSee($driver->user->name)
            ->set('assignDriverId', (string) $driver->id)
            ->call('assignDriver')
            ->assertSet('assigningRouteId', null);

        $route->refresh();
        $this->assertSame($driver->id, $route->driver_id);
        $this->assertSame(Route::STATUS_ASSIGNED, $route->status);
        $this->assertTrue(
            AuditLog::where('action', 'route.assigned')->where('target_id', $route->id)->exists()
        );
    }

    public function test_a_driver_with_an_active_route_is_not_offered_nor_assignable(): void
    {
        $busy = $this->deliveryDriver();
        $free = $this->deliveryDriver();

        $active = $this->draftRoute('Ruta en curso');
        $active->update(['driver_id' => $busy->id, 'status' => Route::STATUS_IN_PROGRESS]);

        $route = $this->draftRoute();

        $drivers = Livewire::actingAs($this->admin)
            ->test(RoutesManager::class)
            ->call('startAssigningDriver', $route->id)
            ->viewData('assignableDrivers')
            ->pluck('id')
            ->all();

        $this->assertSame([$free->id], $drivers);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Este repartidor ya tiene una ruta activa.');

        app(RouteService::class)->assignDriver($route, $busy, $this->admin->id);
    }

    public function test_an_incompatible_or_unverified_driver_cannot_be_assigned(): void
    {
        $route = $this->draftRoute();

        try {
            app(RouteService::class)->assignDriver($route, $this->deliveryDriver(['driver_type' => Driver::TYPE_HUB]), $this->admin->id);
            $this->fail('Se asignó una ruta de reparto a un chofer de HUB.');
        } catch (RuntimeException $e) {
            $this->assertSame('Esta ruta no es compatible con el tipo de este repartidor.', $e->getMessage());
        }

        try {
            app(RouteService::class)->assignDriver(
                $route,
                $this->deliveryDriver(['verification_status' => Driver::VERIFICATION_PENDING]),
                $this->admin->id
            );
            $this->fail('Se asignó una ruta a un repartidor sin verificar.');
        } catch (RuntimeException $e) {
            $this->assertSame('Este repartidor no está activo y verificado: no se le pueden asignar rutas.', $e->getMessage());
        }

        $this->assertNull($route->fresh()->driver_id);
    }

    public function test_a_route_that_already_has_a_driver_cannot_be_reassigned(): void
    {
        $route = $this->draftRoute();
        app(RouteService::class)->assignDriver($route, $this->deliveryDriver(), $this->admin->id);

        Livewire::actingAs($this->admin)
            ->test(RoutesManager::class)
            ->assertDontSee('Asignar repartidor');

        $this->expectExceptionMessage('Esta ruta ya no está disponible.');

        app(RouteService::class)->assignDriver($route->fresh(), $this->deliveryDriver(), $this->admin->id);
    }

    public function test_the_driver_can_still_claim_routes_on_their_own(): void
    {
        $route = $this->draftRoute();
        $driver = $this->deliveryDriver();

        $claimed = app(RouteService::class)->claimRoute($route, $driver, $driver->user_id);

        $this->assertSame($driver->id, $claimed->driver_id);
        $this->assertTrue(
            AuditLog::where('action', 'route.claimed')->where('target_id', $route->id)->exists()
        );

        $this->expectExceptionMessage('Ya tienes una ruta activa. Finalízala antes de tomar otra.');

        app(RouteService::class)->claimRoute($this->draftRoute('Otra ruta'), $driver, $driver->user_id);
    }
}
