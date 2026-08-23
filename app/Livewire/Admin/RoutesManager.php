<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
use App\Services\RouteService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Gestión de Rutas')]
class RoutesManager extends Component
{
    use WithPagination;

    // Filtros del listado
    public string $filterCity = '';

    public string $filterStatus = '';

    // --- Formulario de creación / edición de recorrido ---
    public bool $showBuilder = false;

    public ?int $editingRouteId = null;

    public string $city = '';

    public string $name = '';

    /**
     * IDs de allies seleccionadas, en orden de visita.
     *
     * @var list<int>
     */
    public array $selectedStops = [];

    // --- Asignación de repartidor ---
    public bool $showAssignModal = false;

    public ?int $assigningRouteId = null;

    public ?int $selectedDriverId = null;

    // --- Registro de recolección (mientras no exista el portal del repartidor) ---
    public bool $showCollectionModal = false;

    public ?int $collectingRouteId = null;

    public ?int $collectingStopId = null;

    /**
     * IDs de paquetes marcados como recolectados en el modal.
     *
     * @var list<int>
     */
    public array $collectedPackageIds = [];

    protected function rules(): array
    {
        return [
            'city' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'selectedStops' => ['required', 'array', 'min:1'],
        ];
    }

    protected $messages = [
        'city.required' => 'Selecciona la ciudad de la ruta.',
        'name.required' => 'Ingresa un nombre para la ruta.',
        'selectedStops.required' => 'Selecciona al menos una agencia para el recorrido.',
        'selectedStops.min' => 'Selecciona al menos una agencia para el recorrido.',
    ];

    /*
    |--------------------------------------------------------------------------
    | CREACIÓN / EDICIÓN DE RECORRIDO
    |--------------------------------------------------------------------------
    */

    public function startCreating(): void
    {
        $this->resetBuilder();
        $this->showBuilder = true;
    }

    /**
     * Al cambiar la ciudad, se limpia la selección de paradas
     * (las agencias disponibles cambian por completo).
     */
    public function updatedCity(): void
    {
        $this->selectedStops = [];
    }

    /**
     * Agrega o quita una agencia del recorrido (clic en el mapa o en la lista).
     */
    public function toggleStop(int $allyId): void
    {
        if (in_array($allyId, $this->selectedStops, true)) {
            $this->selectedStops = array_values(array_filter(
                $this->selectedStops,
                fn (int $id) => $id !== $allyId,
            ));

            return;
        }

        $this->selectedStops[] = $allyId;
    }

    public function moveStopUp(int $index): void
    {
        $this->swapStops($index, $index - 1);
    }

    public function moveStopDown(int $index): void
    {
        $this->swapStops($index, $index + 1);
    }

    protected function swapStops(int $a, int $b): void
    {
        if (! isset($this->selectedStops[$a], $this->selectedStops[$b])) {
            return;
        }

        [$this->selectedStops[$a], $this->selectedStops[$b]] = [$this->selectedStops[$b], $this->selectedStops[$a]];
    }

    public function editRoute(int $routeId): void
    {
        $route = Route::with('stops')->findOrFail($routeId);

        if (! $route->isEditable()) {
            session()->flash('error', 'Esta ruta ya está en curso o finalizada; no se puede editar su recorrido.');

            return;
        }

        $this->editingRouteId = $route->id;
        $this->city = $route->city;
        $this->name = $route->name;
        $this->selectedStops = $route->stops->pluck('ally_id')->all();
        $this->showBuilder = true;
    }

    public function saveRoute(RouteService $routeService): void
    {
        $this->validate();

        if ($this->editingRouteId) {
            $route = Route::findOrFail($this->editingRouteId);
            $route->update(['city' => $this->city, 'name' => $this->name]);
            $routeService->updateStops($route, $this->selectedStops, Auth::id());
            session()->flash('success', 'Ruta actualizada correctamente.');
        } else {
            $routeService->createRoute(
                data: ['city' => $this->city, 'name' => $this->name],
                allyIdsInOrder: $this->selectedStops,
                createdByUserId: Auth::id(),
            );
            session()->flash('success', 'Ruta creada correctamente.');
        }

        $this->resetBuilder();
    }

    public function cancelBuilder(): void
    {
        $this->resetBuilder();
    }

    protected function resetBuilder(): void
    {
        $this->reset(['editingRouteId', 'city', 'name', 'selectedStops', 'showBuilder']);
        $this->resetErrorBag();
    }

    /*
    |--------------------------------------------------------------------------
    | ASIGNACIÓN DE REPARTIDOR
    |--------------------------------------------------------------------------
    */

    public function openAssignModal(int $routeId): void
    {
        $this->assigningRouteId = $routeId;
        $this->selectedDriverId = Route::find($routeId)?->driver_id;
        $this->showAssignModal = true;
    }

    public function assignDriver(RouteService $routeService): void
    {
        $this->validate(['selectedDriverId' => ['required', 'exists:drivers,id']]);

        $route = Route::findOrFail($this->assigningRouteId);
        $driver = Driver::findOrFail($this->selectedDriverId);

        try {
            $routeService->assignDriver($route, $driver, Auth::id());
            session()->flash('success', 'Repartidor asignado correctamente.');
        } catch (RuntimeException $e) {
            $this->addError('selectedDriverId', $e->getMessage());

            return;
        }

        $this->showAssignModal = false;
        $this->reset(['assigningRouteId', 'selectedDriverId']);
    }

    /*
    |--------------------------------------------------------------------------
    | CICLO DE VIDA DE LA RUTA
    |--------------------------------------------------------------------------
    */

    public function startRoute(int $routeId, RouteService $routeService): void
    {
        try {
            $routeService->start(Route::findOrFail($routeId), Auth::id());
            session()->flash('success', 'La ruta inició su recorrido.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function completeRoute(int $routeId, RouteService $routeService): void
    {
        try {
            $routeService->complete(Route::findOrFail($routeId), Auth::id());
            session()->flash('success', 'Ruta finalizada. Las paradas pendientes quedaron marcadas como omitidas.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cancelRoute(int $routeId, RouteService $routeService): void
    {
        try {
            $routeService->cancel(Route::findOrFail($routeId), Auth::id());
            session()->flash('success', 'Ruta cancelada.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function duplicateRoute(int $routeId, RouteService $routeService): void
    {
        $routeService->duplicate(Route::findOrFail($routeId), Auth::id());
        session()->flash('success', 'Se creó un nuevo ciclo a partir de esta ruta.');
    }

    /*
    |--------------------------------------------------------------------------
    | REGISTRO DE RECOLECCIÓN (temporalmente operado por el admin)
    |--------------------------------------------------------------------------
    */

    public function openCollectionModal(int $routeId, int $stopId): void
    {
        $this->collectingRouteId = $routeId;
        $this->collectingStopId = $stopId;
        $this->collectedPackageIds = [];
        $this->showCollectionModal = true;
    }

    public function registerCollection(RouteService $routeService): void
    {
        $route = Route::findOrFail($this->collectingRouteId);
        $stop = RouteStop::findOrFail($this->collectingStopId);

        try {
            $routeService->registerCollection(
                route: $route,
                stop: $stop,
                packageIds: $this->collectedPackageIds,
                actingUserId: Auth::id(),
            );
            session()->flash('success', 'Recolección registrada. La agencia cambió a GRIS en el mapa.');
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->showCollectionModal = false;
        $this->reset(['collectingRouteId', 'collectingStopId', 'collectedPackageIds']);
    }

    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    public function render(RouteService $routeService)
    {
        $routes = Route::query()
            ->with(['driver.user', 'stops.ally'])
            ->when($this->filterCity !== '', fn ($q) => $q->where('city', $this->filterCity))
            ->when($this->filterStatus !== '', fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(10);

        $citiesWithAllies = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $availableAllies = $this->city !== ''
            ? Ally::query()
                ->where('city', $this->city)
                ->where('status', Ally::STATUS_ACTIVE)
                ->orderBy('business_name')
                ->get()
            : collect();

        $activeDrivers = Driver::query()
            ->with('user')
            ->where('status', Driver::STATUS_ACTIVE)
            ->orderBy('vehicle_plate')
            ->get();

        $collectiblePackages = $this->collectingStopId
            ? $routeService->collectiblePackagesFor(RouteStop::findOrFail($this->collectingStopId))
            : collect();

        return view('livewire.admin.routes-manager', [
            'routes' => $routes,
            'citiesWithAllies' => $citiesWithAllies,
            'availableAllies' => $availableAllies,
            'activeDrivers' => $activeDrivers,
            'collectiblePackages' => $collectiblePackages,
        ]);
    }
}
