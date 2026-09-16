<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\Driver;
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

    /**
     * Historial por driver (Reglas de negocio no lo cambian: solo
     * acota el listado existente por driver_id, el mismo campo que ya
     * usa Route::driver()). Vacío = sin filtrar.
     */
    public string $filterDriverId = '';

    /**
     * Ciudades del estado elegido en el filtro del listado. Va aparte
     * de $cities (que alimenta el constructor de rutas): compartirlas
     * hacía que filtrar el listado reescribiera el select de ciudad
     * del formulario de creación/edición.
     */
    public array $filterCities = [];

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

    /**
     * HUB (Warehouse) desde el que el driver parte / al que regresa.
     * Ambos opcionales — Fase 2 (rutas multiestado, Reglas 32/41).
     */
    public ?int $originWarehouseId = null;

    public ?int $returnWarehouseId = null;

    /**
     * Búsqueda opcional para acotar la lista de paradas disponibles
     * (agencias o almacenes, según el tipo de ruta). Reemplaza el
     * filtro duro por state/city: ya no limita qué se puede agregar,
     * solo ayuda a encontrar algo en una lista larga.
     */
    public string $stopSearch = '';

    /**
     * Filtro jerárquico Estado -> Ciudad para el mismo buscador de
     * paradas, independiente de state/city de la ruta (esos son solo
     * referenciales, ver arriba). Igual que stopSearch, es solo una
     * ayuda para encontrar la agencia/almacén — no restringe qué se
     * puede agregar a la ruta.
     */
    public string $stopFilterState = '';

    public string $stopFilterCity = '';

    public array $stopFilterCities = [];

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
    | Cambio de estado del buscador de paradas
    |--------------------------------------------------------------------------
    */

    public function updatedStopFilterState(VenezuelaLocationService $locationService): void
    {
        $this->stopFilterCity = '';

        $this->stopFilterCities = $this->stopFilterState !== ''
            ? $locationService->citiesByState($this->stopFilterState)
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

        $this->filterCities = $this->filterState !== ''
            ? $locationService->citiesByState($this->filterState)
            : [];
    }

    public function updatedFilterCity(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDriverId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'filterState',
            'filterCity',
            'filterStatus',
            'filterCities',
            'filterDriverId',
        ]);

        $this->resetPage();
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
            'originWarehouseId',
            'returnWarehouseId',
            'stopSearch',
            'stopFilterState',
            'stopFilterCity',
            'stopFilterCities',
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
        $this->originWarehouseId = $route->origin_warehouse_id;
        $this->returnWarehouseId = $route->return_warehouse_id;
        $this->stopSearch = '';
        $this->stopFilterState = '';
        $this->stopFilterCity = '';
        $this->stopFilterCities = [];

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
            'originWarehouseId',
            'returnWarehouseId',
            'stopSearch',
            'stopFilterState',
            'stopFilterCity',
            'stopFilterCities',
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
            // Estado/Ciudad de la ruta son opcionales desde la Fase 2:
            // ya no restringen qué paradas puede tener la ruta, quedan
            // solo como metadato descriptivo/de búsqueda.
            'state' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'routeType' => ['required', 'in:'.implode(',', Route::TYPES)],
            'selectedStops' => ['required', 'array', 'min:1'],
            'originWarehouseId' => ['nullable', 'integer', 'exists:warehouses,id'],
            'returnWarehouseId' => ['nullable', 'integer', 'exists:warehouses,id'],
        ]);

        try {
            if ($this->editingRouteId) {
                $routeService->updateRoute(
                    routeId: $this->editingRouteId,
                    name: $this->name,
                    state: $this->state !== '' ? $this->state : null,
                    city: $this->city !== '' ? $this->city : null,
                    allyIds: $this->selectedStops,
                    actingUserId: Auth::id(),
                    originWarehouseId: $this->originWarehouseId,
                    returnWarehouseId: $this->returnWarehouseId,
                );

                session()->flash(
                    'success',
                    'Ruta actualizada correctamente.'
                );
            } else {
                $routeService->createRoute(
                    data: [
                        'name' => $this->name,
                        'state' => $this->state !== '' ? $this->state : null,
                        'city' => $this->city !== '' ? $this->city : null,
                        'route_type' => $this->routeType,
                        'origin_warehouse_id' => $this->originWarehouseId,
                        'return_warehouse_id' => $this->returnWarehouseId,
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
                'originWarehouse',
                'returnWarehouse',
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
            ->when(
                $this->filterDriverId !== '',
                fn ($q) => $q->where('driver_id', $this->filterDriverId)
            )
            ->latest()
            ->paginate(10);

        // Drivers con al menos una ruta, para el filtro "historial por
        // driver" — no todos los drivers activos, solo los que ya
        // tienen algo que mostrar en este listado.
        $driversWithRoutes = Driver::query()
            ->whereHas('routes')
            ->with('user')
            ->get()
            ->sortBy(fn (Driver $driver) => $driver->user?->name ?? '')
            ->values();

        /*
         * Fase 2: las paradas ya no se filtran por el state/city de la
         * ruta (Reglas 24/26/27) — se listan todas las agencias/
         * almacenes activos, acotables con un filtro jerárquico
         * Estado -> Ciudad y/o una búsqueda de texto, ninguno de los
         * cuales restringe qué se puede agregar (solo ayudan a
         * encontrar algo en una lista larga).
         */
        $availableAllies = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->when(
                $this->stopFilterState !== '',
                fn ($q) => $q->where('state', $this->stopFilterState)
            )
            ->when(
                $this->stopFilterCity !== '',
                fn ($q) => $q->where('city', $this->stopFilterCity)
            )
            ->when($this->stopSearch !== '', function ($query) {
                $search = $this->stopSearch;

                $query->where(function ($q) use ($search) {
                    $q->where('business_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            })
            ->orderBy('business_name')
            ->get();

        // Todos los almacenes activos, sin filtrar: fuente de los
        // selects de HUB de origen/retorno (cualquier tipo de ruta) —
        // esos nunca se acotan por el buscador de paradas.
        $allWarehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Fuente del picker de paradas (rutas hub_distribution), esta
        // sí acotada por el filtro Estado/Ciudad/texto del buscador.
        $availableWarehouses = Warehouse::query()
            ->where('is_active', true)
            ->when(
                $this->stopFilterState !== '',
                fn ($q) => $q->where('state', $this->stopFilterState)
            )
            ->when(
                $this->stopFilterCity !== '',
                fn ($q) => $q->where('city', $this->stopFilterCity)
            )
            ->when($this->stopSearch !== '', function ($query) {
                $search = $this->stopSearch;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get();

        $collectiblePackages = $this->collectingStopId
            ? $routeService->collectiblePackagesFor(
                RouteStop::findOrFail($this->collectingStopId)
            )
            : collect();

        return view('livewire.admin.routes-manager', [
            'routes' => $routes,
            'availableAllies' => $availableAllies,
            'availableWarehouses' => $availableWarehouses,
            'allWarehouses' => $allWarehouses,
            'collectiblePackages' => $collectiblePackages,
            'driversWithRoutes' => $driversWithRoutes,
        ]);
    }
}
