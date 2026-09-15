<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoutesManager;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Buscador de paradas (Admin\RoutesManager): filtro jerárquico
 * Estado -> Ciudad, combinable con el buscador de texto existente
 * (stopSearch). Ninguno de los dos restringe qué se puede agregar a
 * la ruta — solo acotan la lista de "Agencias/Almacenes disponibles".
 *
 * No confundir con state/city de la RUTA (arriba en el formulario):
 * esos son solo referenciales desde Fase 2 y no se tocan aquí.
 */
class RoutesManagerStopFilterTest extends TestCase
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

    private function allyNames($component): array
    {
        return $component->viewData('availableAllies')->pluck('business_name')->all();
    }

    private function warehouseNames($component): array
    {
        return $component->viewData('availableWarehouses')->pluck('name')->all();
    }

    public function test_without_filters_lists_every_active_ally_and_warehouse_as_before(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Aliado Caracas', 'city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->createAlly(['business_name' => 'Aliado Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);

        Warehouse::create(['name' => 'HUB Caracas', 'city' => 'Caracas', 'state' => 'Distrito Capital', 'is_active' => true]);
        Warehouse::create(['name' => 'HUB Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia', 'is_active' => true]);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating');

        $allyNames = $this->allyNames($component);
        $warehouseNames = $this->warehouseNames($component);

        $this->assertContains('Aliado Caracas', $allyNames);
        $this->assertContains('Aliado Maracaibo', $allyNames);
        $this->assertContains('HUB Caracas', $warehouseNames);
        $this->assertContains('HUB Maracaibo', $warehouseNames);
    }

    public function test_state_filter_narrows_available_allies(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Aliado Caracas', 'city' => 'Caracas', 'state' => 'Distrito Capital']);
        $this->createAlly(['business_name' => 'Aliado Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterState', 'Zulia');

        $names = $this->allyNames($component);

        $this->assertContains('Aliado Maracaibo', $names);
        $this->assertNotContains('Aliado Caracas', $names);
    }

    public function test_state_filter_narrows_available_warehouses(): void
    {
        $admin = $this->createAdmin();

        Warehouse::create(['name' => 'HUB Caracas', 'city' => 'Caracas', 'state' => 'Distrito Capital', 'is_active' => true]);
        Warehouse::create(['name' => 'HUB Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia', 'is_active' => true]);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('routeType', Route::TYPE_HUB_DISTRIBUTION)
            ->set('stopFilterState', 'Zulia');

        $names = $this->warehouseNames($component);

        $this->assertContains('HUB Maracaibo', $names);
        $this->assertNotContains('HUB Caracas', $names);
    }

    public function test_state_and_city_filter_narrows_to_exact_location(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Aliado Valencia', 'city' => 'Valencia', 'state' => 'Carabobo']);
        $this->createAlly(['business_name' => 'Aliado Naguanagua', 'city' => 'Naguanagua', 'state' => 'Carabobo']);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterState', 'Carabobo')
            ->set('stopFilterCity', 'Valencia');

        $names = $this->allyNames($component);

        $this->assertSame(['Aliado Valencia'], $names);
    }

    public function test_changing_state_clears_the_selected_city(): void
    {
        $admin = $this->createAdmin();

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterState', 'Carabobo')
            ->set('stopFilterCity', 'Valencia')
            ->assertSet('stopFilterCity', 'Valencia')
            ->set('stopFilterState', 'Zulia')
            ->assertSet('stopFilterCity', '')
            ->assertViewHas('stopFilterCities', function ($cities) {
                return in_array('Maracaibo', $cities, true)
                    && ! in_array('Valencia', $cities, true);
            });
    }

    public function test_city_without_state_does_not_produce_invalid_results(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Aliado Valencia', 'city' => 'Valencia', 'state' => 'Carabobo']);
        $this->createAlly(['business_name' => 'Aliado Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);

        // stopFilterCity se fija directamente (saltándose el select,
        // que en la UI queda deshabilitado sin Estado) para comprobar
        // que el filtro sigue siendo seguro y coherente incluso sin
        // Estado seleccionado — no debe romperse ni devolver todo.
        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterCity', 'Valencia');

        $names = $this->allyNames($component);

        $this->assertContains('Aliado Valencia', $names);
        $this->assertNotContains('Aliado Maracaibo', $names);
    }

    public function test_text_search_combined_with_state_filter(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Farmatodo Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);
        $this->createAlly(['business_name' => 'Aliado Cabimas', 'city' => 'Cabimas', 'state' => 'Zulia']);
        $this->createAlly(['business_name' => 'Farmatodo Valencia', 'city' => 'Valencia', 'state' => 'Carabobo']);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterState', 'Zulia')
            ->set('stopSearch', 'Farmatodo');

        $names = $this->allyNames($component);

        $this->assertSame(['Farmatodo Maracaibo'], $names);
    }

    public function test_text_search_combined_with_state_and_city_filter(): void
    {
        $admin = $this->createAdmin();

        $this->createAlly(['business_name' => 'Farmatodo Valencia Centro', 'city' => 'Valencia', 'state' => 'Carabobo']);
        $this->createAlly(['business_name' => 'Aliado Valencia Sur', 'city' => 'Valencia', 'state' => 'Carabobo']);
        $this->createAlly(['business_name' => 'Farmatodo Naguanagua', 'city' => 'Naguanagua', 'state' => 'Carabobo']);

        $component = Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('stopFilterState', 'Carabobo')
            ->set('stopFilterCity', 'Valencia')
            ->set('stopSearch', 'Farmatodo');

        $names = $this->allyNames($component);

        $this->assertSame(['Farmatodo Valencia Centro'], $names);
    }

    /**
     * Seleccionar una parada mientras el filtro está activo sigue
     * creando el RouteStop exactamente igual que sin filtro — el
     * filtro no participa en absoluto en la creación de la ruta.
     */
    public function test_selecting_a_stop_while_filtered_still_creates_the_route_stop_correctly(): void
    {
        $admin = $this->createAdmin();

        $ally = $this->createAlly(['business_name' => 'Aliado Maracaibo', 'city' => 'Maracaibo', 'state' => 'Zulia']);
        $this->createAlly(['business_name' => 'Aliado Caracas', 'city' => 'Caracas', 'state' => 'Distrito Capital']);

        Livewire::actingAs($admin)
            ->test(RoutesManager::class)
            ->call('startCreating')
            ->set('name', 'Ruta Filtrada Zulia')
            ->set('routeType', Route::TYPE_HUB_TRANSFER)
            ->set('stopFilterState', 'Zulia')
            ->call('toggleStop', $ally->id)
            ->call('saveRoute')
            ->assertHasNoErrors();

        $route = Route::where('name', 'Ruta Filtrada Zulia')->firstOrFail();

        $this->assertSame(1, $route->stops()->count());
        $this->assertSame(
            $ally->id,
            $route->stops()->first()->ally_id
        );
        $this->assertSame(
            RouteStop::STATUS_PENDING,
            $route->stops()->first()->status
        );
    }
}
