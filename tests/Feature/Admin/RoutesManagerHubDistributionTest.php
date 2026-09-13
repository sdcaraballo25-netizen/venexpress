<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoutesManager;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verificación de humo: el builder de Rutas debe renderizar sin
 * errores cuando se selecciona el tipo hub_distribution (muestra
 * almacenes en vez de agencias) y debe poder crear una ruta completa
 * de ese tipo de punta a punta a través del componente real.
 */
class RoutesManagerHubDistributionTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_builder_renders_warehouse_picker_for_hub_distribution_type(): void
    {
        $admin = $this->createAdmin();
        Warehouse::create([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('routeType', Route::TYPE_HUB_DISTRIBUTION)
            ->set('state', 'Carabobo')
            ->assertOk()
            ->assertSee('Almacén Valencia')
            ->assertSee('Almacenes disponibles');
    }

    public function test_admin_can_create_a_full_hub_distribution_route(): void
    {
        $admin = $this->createAdmin();
        $warehouse = Warehouse::create([
            'name' => 'Almacén Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('routeType', Route::TYPE_HUB_DISTRIBUTION)
            ->set('name', 'Distribución Valencia')
            ->set('state', 'Carabobo')
            ->set('city', 'Valencia')
            ->call('toggleStop', $warehouse->id)
            ->call('saveRoute')
            ->assertOk();

        $route = Route::where('name', 'Distribución Valencia')->firstOrFail();

        $this->assertSame(Route::TYPE_HUB_DISTRIBUTION, $route->route_type);
        $this->assertSame(1, $route->stops()->count());
        $this->assertSame($warehouse->id, $route->stops()->first()->warehouse_id);
        $this->assertNull($route->stops()->first()->ally_id);
    }
}
