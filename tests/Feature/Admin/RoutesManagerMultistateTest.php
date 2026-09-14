<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoutesManager;
use App\Models\Ally;
use App\Models\Route;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Fase 2 — Rutas multiestado.
 *
 * El state/city de la ruta dejaron de restringir qué paradas puede
 * tener: antes el picker de agencias/almacenes solo mostraba
 * resultados que coincidieran exactamente con el state/city elegido
 * para la ruta. Ahora se listan todas las agencias/almacenes activos
 * (acotables con una búsqueda opcional que no restringe nada), y una
 * ruta puede combinar paradas de distintos estados y ciudades.
 *
 * También se agrega HUB de origen/retorno (Warehouse), opcional.
 */
class RoutesManagerMultistateTest extends TestCase
{
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_can_create_a_route_with_stops_from_different_states(): void
    {
        $admin = $this->createAdmin();

        $allyCaracas = $this->createAlly([
            'business_name' => 'Aliado Caracas',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        $allyMerida = $this->createAlly([
            'business_name' => 'Aliado Mérida',
            'city' => 'Mérida',
            'state' => 'Mérida',
        ]);

        $allyMaracaibo = $this->createAlly([
            'business_name' => 'Aliado Maracaibo',
            'city' => 'Maracaibo',
            'state' => 'Zulia',
        ]);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('name', 'Ruta Multiestado Occidente-Centro')
            ->set('routeType', Route::TYPE_HUB_TRANSFER)
            ->call('toggleStop', $allyCaracas->id)
            ->call('toggleStop', $allyMerida->id)
            ->call('toggleStop', $allyMaracaibo->id)
            ->call('saveRoute')
            ->assertHasNoErrors();

        $route = Route::where('name', 'Ruta Multiestado Occidente-Centro')->firstOrFail();

        $this->assertSame(3, $route->stops()->count());
        $this->assertEqualsCanonicalizing(
            [$allyCaracas->id, $allyMerida->id, $allyMaracaibo->id],
            $route->stops()->pluck('ally_id')->all()
        );
    }

    public function test_route_can_be_created_without_state_or_city(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('name', 'Ruta Sin Estado Definido')
            ->set('routeType', Route::TYPE_HUB_TRANSFER)
            ->call('toggleStop', $ally->id)
            ->call('saveRoute')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('routes', [
            'name' => 'Ruta Sin Estado Definido',
            'state' => null,
            'city' => null,
        ]);
    }

    public function test_admin_can_set_origin_and_return_warehouse(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        $origin = Warehouse::create([
            'name' => 'HUB Caracas',
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'is_active' => true,
        ]);

        $return = Warehouse::create([
            'name' => 'HUB Valencia',
            'city' => 'Valencia',
            'state' => 'Carabobo',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('name', 'Ruta Con HUB Definido')
            ->set('routeType', Route::TYPE_HUB_TRANSFER)
            ->set('originWarehouseId', $origin->id)
            ->set('returnWarehouseId', $return->id)
            ->call('toggleStop', $ally->id)
            ->call('saveRoute')
            ->assertHasNoErrors();

        $route = Route::where('name', 'Ruta Con HUB Definido')->firstOrFail();

        $this->assertSame($origin->id, $route->origin_warehouse_id);
        $this->assertSame($return->id, $route->return_warehouse_id);
        $this->assertSame('HUB Caracas', $route->originWarehouse->name);
        $this->assertSame('HUB Valencia', $route->returnWarehouse->name);
    }

    public function test_origin_warehouse_must_exist(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('name', 'Ruta Con HUB Inexistente')
            ->set('routeType', Route::TYPE_HUB_TRANSFER)
            ->set('originWarehouseId', 999999)
            ->call('toggleStop', $ally->id)
            ->call('saveRoute')
            ->assertHasErrors(['originWarehouseId']);
    }

    public function test_stop_search_narrows_the_ally_picker_without_restricting_selection(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Farmatodo Valencia', 'city' => 'Valencia', 'state' => 'Carabobo']);
        $this->createAlly(['business_name' => 'Aliado Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopSearch', 'Maracaibo')
            ->assertViewHas('availableAllies', function ($allies) {
                $names = $allies->pluck('business_name')->all();

                return in_array('Aliado Maracaibo', $names, true)
                    && ! in_array('Farmatodo Valencia', $names, true);
            });
    }

    public function test_existing_route_created_before_phase_2_still_lists_and_edits_correctly(): void
    {
        $admin = $this->createAdmin();
        $ally = $this->createAlly();
        $creator = User::factory()->create();

        // Simula una ruta creada antes de la Fase 2: sin
        // origin_warehouse_id/return_warehouse_id, solo con el
        // city/state únicos que antes eran obligatorios.
        $route = Route::create([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
            'name' => 'Ruta Legada',
            'created_by' => $creator->id,
            'status' => Route::STATUS_DRAFT,
            'route_type' => Route::TYPE_HUB_TRANSFER,
        ]);

        \App\Models\RouteStop::create([
            'route_id' => $route->id,
            'ally_id' => $ally->id,
            'sequence' => 1,
            'status' => \App\Models\RouteStop::STATUS_PENDING,
        ]);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->assertSee('Ruta Legada')
            ->call('editRoute', $route->id)
            ->assertSet('state', 'Distrito Capital')
            ->assertSet('city', 'Caracas')
            ->assertSet('originWarehouseId', null)
            ->assertSet('returnWarehouseId', null)
            ->call('saveRoute')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('routes', [
            'id' => $route->id,
            'name' => 'Ruta Legada',
        ]);
    }
}
