<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Warehouse;
use App\Services\RouteService;
use App\Services\VenezuelaLocationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Control de Rutas')]
class RoutesManager extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filtros
    |--------------------------------------------------------------------------
    */

    public string $filterState = '';

    public string $filterCity = '';

    public string $filterStatus = '';

    /*
    |--------------------------------------------------------------------------
    | Constructor de ruta
    |--------------------------------------------------------------------------
    */
    public array $citiesWithAllies = [];

    public bool $showBuilder = false;

    public ?int $editingRouteId = null;

    public string $name = '';

    public string $state = '';

    public string $city = '';

    public string $routeType = Route::TYPE_DELIVERY;

    public array $selectedStops = [];

    /*
    |--------------------------------------------------------------------------
    | Recolección
    |--------------------------------------------------------------------------
    */

    public bool $showCollectionModal = false;

    public ?int $collectingRouteId = null;

    public ?int $collectingStopId = null;

    public array $collectedPackageIds = [];

    /*
    |--------------------------------------------------------------------------
    | Ubicaciones
    |--------------------------------------------------------------------------
    */

    public array $states = [];

    public array $cities = [];

    /*
    |--------------------------------------------------------------------------
    | Inicialización
    |--------------------------------------------------------------------------
    */

    public function mount(VenezuelaLocationService $locationService): void
    {
        $this->states = $locationService->states();

        $this->refreshCitiesWithAllies();
    }

    /**
     * Ciudades donde hay al menos una agencia aliada activa. Alimenta el
     * filtro "Ciudad" del listado de rutas.
     */
    protected function refreshCitiesWithAllies(): void
    {
        $this->citiesWithAllies = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->whereNotNull('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->filter(fn ($city) => trim((string) $city) !== '')
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Cambio de estado
    |--------------------------------------------------------------------------
    */

    public function updatedState(VenezuelaLocationService $locationService): void
    {
        $this->city = '';

        $this->cities = $this->state !== ''
            ? $locationService->citiesByState($this->state)
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Cambio de filtro de estado
    |--------------------------------------------------------------------------
    */

    public function updatedFilterState(VenezuelaLocationService $locationService): void
    {
        $this->filterCity = '';

        $this->resetPage();

        $this->cities = $this->filterState !== ''
            ? $locationService->citiesByState($this->filterState)
            : [];
    }

    /*
    |--------------------------------------------------------------------------
    | Crear ruta
    |--------------------------------------------------------------------------
    */

    public function startCreating(): void
    {
        $this->reset([
            'editingRouteId',
            'name',
            'state',
            'city',
            'routeType',
            'selectedStops',
        ]);

        $this->routeType = Route::TYPE_DELIVERY;

        $this->cities = [];

        $this->showBuilder = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Editar ruta
    |--------------------------------------------------------------------------
    */

    public function editRoute(int $routeId): void
    {
        $route = Route::with('stops')->findOrFail($routeId);

        $this->editingRouteId = $route->id;
        $this->name = $route->name;
        $this->state = $route->state ?? '';
        $this->city = $route->city ?? '';
        $this->routeType = $route->route_type ?? Route::TYPE_DELIVERY;

        $locationColumn = $this->routeType === Route::TYPE_HUB_DISTRIBUTION
            ? 'warehouse_id'
            : 'ally_id';

        $this->selectedStops = $route->stops
            ->sortBy('sequence')
            ->pluck($locationColumn)
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($this->state !== '') {
            $locationService = app(VenezuelaLocationService::class);

            $this->cities = $locationService->citiesByState($this->state);
        }

        $this->showBuilder = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Cancelar constructor
    |--------------------------------------------------------------------------
    */

    public function cancelBuilder(): void
    {
        $this->showBuilder = false;

        $this->reset([
            'editingRouteId',
            'name',
            'state',
            'city',
            'routeType',
            'selectedStops',
        ]);

        $this->routeType = Route::TYPE_DELIVERY;
        $this->cities = [];
    }

    /*
    |--------------------------------------------------------------------------
    | Seleccionar / quitar agencia
    |--------------------------------------------------------------------------
    */

    public function toggleStop(int $allyId): void
    {
        if (in_array($allyId, $this->selectedStops, true)) {
            $this->selectedStops = array_values(
                array_filter(
                    $this->selectedStops,
                    fn ($id) => $id !== $allyId
                )
            );

            return;
        }

        $this->selectedStops[] = $allyId;
    }

    /*
    |--------------------------------------------------------------------------
    | Mover parada arriba
    |--------------------------------------------------------------------------
    */

    public function moveStopUp(int $index): void
    {
        if ($index <= 0 || ! isset($this->selectedStops[$index])) {
            return;
        }

        [$this->selectedStops[$index - 1], $this->selectedStops[$index]]
            = [$this->selectedStops[$index], $this->selectedStops[$index - 1]];
    }

    /*
    |--------------------------------------------------------------------------
    | Mover parada abajo
    |--------------------------------------------------------------------------
    */

    public function moveStopDown(int $index): void
    {
        if (
            $index < 0 ||
            ! isset($this->selectedStops[$index]) ||
            ! isset($this->selectedStops[$index + 1])
        ) {
            return;
        }

        [$this->selectedStops[$index], $this->selectedStops[$index + 1]]
            = [$this->selectedStops[$index + 1], $this->selectedStops[$index]];
    }

    /*
    |--------------------------------------------------------------------------
    | Guardar ruta
    |--------------------------------------------------------------------------
    */

    public function saveRoute(RouteService $routeService): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string'],
            'city' => ['required', 'string'],
            'routeType' => ['required', 'in:'.implode(',', Route::TYPES)],
            'selectedStops' => ['required', 'array', 'min:1'],
        ]);

        try {
            if ($this->editingRouteId) {
                $routeService->updateRoute(
                    routeId: $this->editingRouteId,
                    name: $this->name,
                    state: $this->state,
                    city: $this->city,
                    allyIds: $this->selectedStops,
                    actingUserId: Auth::id(),
                );

                session()->flash(
                    'success',
                    'Ruta actualizada correctamente.'
                );
            } else {
                $routeService->createRoute(
                    data: [
                        'name' => $this->name,
                        'state' => $this->state,
                        'city' => $this->city,
                        'route_type' => $this->routeType,
                    ],
                    allyIdsInOrder: $this->selectedStops,
                    createdByUserId: Auth::id(),
                );

                session()->flash(
                    'success',
                    'Ruta creada correctamente.'
                );
            }

            $this->cancelBuilder();
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Cancelar ruta
    |--------------------------------------------------------------------------
    */

    public function cancelRoute(int $routeId, RouteService $routeService): void
    {
        try {
            $routeService->cancelRoute(
                routeId: $routeId,
                actingUserId: Auth::id(),
            );

            session()->flash(
                'success',
                'Ruta cancelada correctamente.'
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicar ruta
    |--------------------------------------------------------------------------
    */

    public function duplicateRoute(int $routeId, RouteService $routeService): void
    {
        try {
            $routeService->duplicateRoute(
                routeId: $routeId,
                actingUserId: Auth::id(),
            );

            session()->flash(
                'success',
                'Ruta duplicada correctamente.'
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Recolección
    |--------------------------------------------------------------------------
    */

    public function openCollectionModal(
        int $routeId,
        int $stopId
    ): void {
        $this->collectingRouteId = $routeId;
        $this->collectingStopId = $stopId;
        $this->collectedPackageIds = [];

        $this->showCollectionModal = true;
    }

    public function registerCollection(RouteService $routeService): void
    {
        $this->validate([
            'collectingRouteId' => ['required', 'integer'],
            'collectingStopId' => ['required', 'integer'],
            'collectedPackageIds' => ['array'],
        ]);

        try {
            $route = Route::findOrFail($this->collectingRouteId);

            $stop = RouteStop::findOrFail($this->collectingStopId);

            $routeService->registerCollection(
                route: $route,
                stop: $stop,
                packageIds: $this->collectedPackageIds,
                actingUserId: Auth::id(),
            );

            session()->flash(
                'success',
                'Recolección registrada correctamente.'
            );

            $this->showCollectionModal = false;

            $this->reset([
                'collectingRouteId',
                'collectingStopId',
                'collectedPackageIds',
            ]);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render(RouteService $routeService)
    {
        $routes = Route::query()
            ->with([
                'driver.user',
                'stops.ally',
                'stops.warehouse',
            ])
            ->when(
                $this->filterState !== '',
                fn ($q) => $q->where('state', $this->filterState)
            )
            ->when(
                $this->filterCity !== '',
                fn ($q) => $q->where('city', $this->filterCity)
            )
            ->when(
                $this->filterStatus !== '',
                fn ($q) => $q->where('status', $this->filterStatus)
            )
            ->latest()
            ->paginate(10);

        $availableAllies = collect();

        if ($this->state !== '' && $this->city !== '') {
            $availableAllies = Ally::query()
                ->where('state', $this->state)
                ->where('city', $this->city)
                ->where('status', Ally::STATUS_ACTIVE)
                ->orderBy('business_name')
                ->get();
        }

        $availableWarehouses = collect();

        if ($this->routeType === Route::TYPE_HUB_DISTRIBUTION && $this->state !== '') {
            $availableWarehouses = Warehouse::query()
                ->where('state', $this->state)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        $collectiblePackages = $this->collectingStopId
            ? $routeService->collectiblePackagesFor(
                RouteStop::findOrFail($this->collectingStopId)
            )
            : collect();

        return view('livewire.admin.routes-manager', [
            'routes' => $routes,
            'availableAllies' => $availableAllies,
            'availableWarehouses' => $availableWarehouses,
            'collectiblePackages' => $collectiblePackages,
        ]);
    }
}
