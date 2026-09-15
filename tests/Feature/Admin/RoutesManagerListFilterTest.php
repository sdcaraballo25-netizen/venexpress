<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\RoutesManager;
use App\Models\Route;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filtros del LISTADO de rutas (Admin\RoutesManager): Estado, Ciudad y
 * Estatus de la ruta.
 *
 * No confundir con RoutesManagerStopFilterTest, que cubre el buscador
 * de paradas dentro del constructor de rutas.
 */
class RoutesManagerListFilterTest extends TestCase
{
    use RefreshDatabase;

    private ?int $creator = null;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN_PRINCIPAL,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
    }

    private function createRoute(string $name, ?string $state, ?string $city, string $status): Route
    {
        return Route::create([
            'name' => $name,
            'state' => $state,
            'city' => $city,
            'status' => $status,
            'route_type' => Route::TYPE_DELIVERY,
            'created_by' => $this->creator ??= $this->createAdmin()->id,
        ]);
    }

    private function routeNames($component): array
    {
        return $component->viewData('routes')->pluck('name')->all();
    }

    public function test_routes_can_be_filtered_by_state(): void
    {
        $this->createRoute('Ruta Valencia', 'Carabobo', 'Valencia', Route::STATUS_DRAFT);
        $this->createRoute('Ruta Maracaibo', 'Zulia', 'Maracaibo', Route::STATUS_DRAFT);

        $component = Livewire::actingAs($this->createAdmin())
            ->test(RoutesManager::class)
            ->set('filterState', 'Zulia');

        $this->assertSame(['Ruta Maracaibo'], $this->routeNames($component));
    }

    public function test_routes_can_be_filtered_by_city(): void
    {
        $this->createRoute('Ruta Valencia', 'Carabobo', 'Valencia', Route::STATUS_DRAFT);
        $this->createRoute('Ruta Guacara', 'Carabobo', 'Guacara', Route::STATUS_DRAFT);

        $component = Livewire::actingAs($this->createAdmin())
            ->test(RoutesManager::class)
            ->set('filterState', 'Carabobo')
            ->set('filterCity', 'Guacara');

        $this->assertSame(['Ruta Guacara'], $this->routeNames($component));
    }

    public function test_routes_can_be_filtered_by_status(): void
    {
        $this->createRoute('Ruta borrador', 'Carabobo', 'Valencia', Route::STATUS_DRAFT);
        $this->createRoute('Ruta en curso', 'Carabobo', 'Valencia', Route::STATUS_IN_PROGRESS);
        $this->createRoute('Ruta finalizada', 'Carabobo', 'Valencia', Route::STATUS_COMPLETED);

        $component = Livewire::actingAs($this->createAdmin());

        foreach ([
            Route::STATUS_DRAFT => 'Ruta borrador',
            Route::STATUS_IN_PROGRESS => 'Ruta en curso',
            Route::STATUS_COMPLETED => 'Ruta finalizada',
        ] as $status => $expected) {
            $this->assertSame(
                [$expected],
                $this->routeNames(
                    $component->test(RoutesManager::class)->set('filterStatus', $status)
                ),
                "El filtro de estatus {$status} no devolvió la ruta esperada."
            );
        }
    }

    /**
     * Elegir un estado en el filtro del listado no debe reescribir el
     * select de ciudad del constructor de rutas ($cities): son dos
     * listas distintas que antes compartían la misma propiedad.
     */
    public function test_list_state_filter_does_not_overwrite_builder_cities(): void
    {
        $component = Livewire::actingAs($this->createAdmin())
            ->test(RoutesManager::class)
            ->set('state', 'Carabobo');

        $builderCities = $component->get('cities');

        $this->assertNotEmpty($builderCities);

        $component->set('filterState', 'Zulia');

        $this->assertSame($builderCities, $component->get('cities'));
        $this->assertNotEmpty($component->get('filterCities'));
        $this->assertNotSame($builderCities, $component->get('filterCities'));
    }

    public function test_clear_filters_resets_every_list_filter(): void
    {
        $this->createRoute('Ruta Valencia', 'Carabobo', 'Valencia', Route::STATUS_DRAFT);
        $this->createRoute('Ruta Maracaibo', 'Zulia', 'Maracaibo', Route::STATUS_COMPLETED);

        $component = Livewire::actingAs($this->createAdmin())
            ->test(RoutesManager::class)
            ->set('filterState', 'Zulia')
            ->set('filterStatus', Route::STATUS_COMPLETED)
            ->call('clearFilters');

        $component->assertSet('filterState', '')
            ->assertSet('filterCity', '')
            ->assertSet('filterStatus', '')
            ->assertSet('filterCities', []);

        $this->assertCount(2, $component->viewData('routes'));
    }
}
