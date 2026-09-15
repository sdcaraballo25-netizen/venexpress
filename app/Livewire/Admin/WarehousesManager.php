<?php

namespace App\Livewire\Admin;

use App\Models\Warehouse;
use App\Models\WarehouseCoverage;
use App\Services\VenezuelaLocationService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * CRUD mínimo de almacenes propios de Venexpress (destino de las
 * rutas de HUB Distribución). Deliberadamente simple: sin búsqueda ni
 * paginación, porque se esperan pocos almacenes.
 */
#[Layout('layouts.admin')]
#[Title('Almacenes')]
class WarehousesManager extends Component
{
    public bool $showForm = false;

    public ?int $editingWarehouseId = null;

    public string $name = '';

    public string $city = '';

    public string $state = '';

    public string $address = '';

    public ?string $successMessage = null;

    /**
     * Catálogo de estados, servido por VenezuelaLocationService
     * (database/data/venezuela.json) — fuente única geográfica del
     * sistema. Estado/Ciudad dejan de ser texto libre: la ciudad se
     * elige de una lista dependiente del estado seleccionado.
     */
    public array $states = [];

    public array $cities = [];

    /*
    |--------------------------------------------------------------------------
    | COBERTURA (Fase 4 — Resolución logística)
    |--------------------------------------------------------------------------
    |
    | Gestión mínima de qué zonas (estado, o estado + ciudad) atiende
    | cada almacén. Se integra aquí en vez de un módulo nuevo porque
    | la cobertura es un atributo del almacén, no una entidad
    | independiente con su propio ciclo de vida.
    */

    public ?int $coverageWarehouseId = null;

    public string $coverageState = '';

    public string $coverageCity = '';

    public bool $coverageWholeState = false;

    public array $coverageCities = [];

    public ?string $coverageError = null;

    public function mount(VenezuelaLocationService $locationService): void
    {
        $this->states = $locationService->states();
    }

    /**
     * Al cambiar el Estado seleccionado, se recarga la lista de
     * ciudades dependientes y se limpia la ciudad elegida (mismo
     * patrón que ya usa Admin\RoutesManager).
     */
    public function updatedState(VenezuelaLocationService $locationService): void
    {
        $this->city = '';

        $this->cities = $this->state !== ''
            ? $locationService->citiesByState($this->state)
            : [];
    }

    public function startCreating(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function editWarehouse(int $warehouseId, VenezuelaLocationService $locationService): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        $this->editingWarehouseId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->state = $warehouse->state;
        $this->cities = $this->state !== ''
            ? $locationService->citiesByState($this->state)
            : [];
        $this->city = $warehouse->city;
        $this->address = $warehouse->address ?? '';

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;

        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingWarehouseId',
            'name',
            'city',
            'state',
            'address',
            'cities',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', Rule::in($this->states)],
            'city' => ['required', 'string', Rule::in($this->cities)],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $data = [
            'name' => $this->name,
            'city' => $this->city,
            'state' => $this->state,
            'address' => $this->address !== '' ? $this->address : null,
        ];

        if ($this->editingWarehouseId) {
            Warehouse::findOrFail($this->editingWarehouseId)->update($data);

            $this->successMessage = 'Almacén actualizado correctamente.';
        } else {
            Warehouse::create([...$data, 'is_active' => true]);

            $this->successMessage = 'Almacén creado correctamente.';
        }

        $this->cancelForm();
    }

    public function toggleActive(int $warehouseId): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        $warehouse->update([
            'is_active' => ! $warehouse->is_active,
        ]);

        $this->successMessage = $warehouse->is_active
            ? 'Almacén activado.'
            : 'Almacén desactivado.';
    }

    /*
    |--------------------------------------------------------------------------
    | COBERTURA
    |--------------------------------------------------------------------------
    */

    public function toggleCoveragePanel(int $warehouseId): void
    {
        $this->coverageWarehouseId = $this->coverageWarehouseId === $warehouseId
            ? null
            : $warehouseId;

        $this->resetCoverageForm();
    }

    public function updatedCoverageState(VenezuelaLocationService $locationService): void
    {
        $this->coverageCity = '';

        $this->coverageCities = $this->coverageState !== ''
            ? $locationService->citiesByState($this->coverageState)
            : [];
    }

    public function updatedCoverageWholeState(): void
    {
        $this->coverageCity = '';
    }

    /**
     * Agrega una cobertura al almacén cuyo panel está abierto.
     *
     * Antes de crearla, rechaza dos casos:
     * - Duplicado exacto: el mismo almacén ya tiene esa misma zona
     *   activa.
     * - Ambigüedad: OTRO almacén ya tiene activa esa misma zona
     *   exacta (mismo estado + misma ciudad, o mismo estado con
     *   ambos como "todo el estado"). Una cobertura de estado en un
     *   almacén y una cobertura de ciudad específica en otro NO es
     *   ambiguo — es el caso normal de "ciudad específica tiene
     *   prioridad sobre el fallback estatal" (Regla de resolución).
     */
    public function addCoverage(): void
    {
        $this->coverageError = null;

        $this->validate([
            'coverageState' => ['required', 'string', Rule::in($this->states)],
            'coverageCity' => $this->coverageWholeState
                ? ['nullable']
                : ['required', 'string', Rule::in($this->coverageCities)],
        ]);

        $warehouse = Warehouse::findOrFail($this->coverageWarehouseId);
        $city = $this->coverageWholeState ? null : $this->coverageCity;

        $duplicate = WarehouseCoverage::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('state', $this->coverageState)
            ->where('city', $city)
            ->where('is_active', true)
            ->exists();

        if ($duplicate) {
            $this->coverageError = 'Este almacén ya tiene esa cobertura activa.';

            return;
        }

        $conflict = WarehouseCoverage::query()
            ->where('warehouse_id', '!=', $warehouse->id)
            ->where('state', $this->coverageState)
            ->where('city', $city)
            ->where('is_active', true)
            ->exists();

        if ($conflict) {
            $this->coverageError = 'Ya existe otro almacén con cobertura activa para exactamente ese destino. '
                .'Desactívala primero si quieres reasignarla.';

            return;
        }

        WarehouseCoverage::create([
            'warehouse_id' => $warehouse->id,
            'state' => $this->coverageState,
            'city' => $city,
            'is_active' => true,
        ]);

        $this->resetCoverageForm();

        $this->successMessage = 'Cobertura agregada correctamente.';
    }

    /**
     * Activa/desactiva una cobertura existente. Al activar, se
     * repite la validación de ambigüedad (otro almacén podría haber
     * tomado esa misma zona mientras esta estaba inactiva).
     */
    public function toggleCoverageActive(int $coverageId): void
    {
        $this->coverageError = null;

        $coverage = WarehouseCoverage::findOrFail($coverageId);
        $activating = ! $coverage->is_active;

        if ($activating) {
            $conflict = WarehouseCoverage::query()
                ->where('id', '!=', $coverage->id)
                ->where('warehouse_id', '!=', $coverage->warehouse_id)
                ->where('state', $coverage->state)
                ->where('city', $coverage->city)
                ->where('is_active', true)
                ->exists();

            if ($conflict) {
                $this->coverageError = 'No se puede activar: otro almacén ya tiene cobertura activa para esa misma zona.';

                return;
            }
        }

        $coverage->update(['is_active' => $activating]);

        $this->successMessage = $activating
            ? 'Cobertura activada.'
            : 'Cobertura desactivada.';
    }

    protected function resetCoverageForm(): void
    {
        $this->reset([
            'coverageState',
            'coverageCity',
            'coverageWholeState',
            'coverageCities',
        ]);

        $this->coverageError = null;
    }

    public function render()
    {
        return view('livewire.admin.warehouses-manager', [
            'warehouses' => Warehouse::query()
                ->with(['coverages' => fn ($query) => $query->orderBy('state')->orderBy('city')])
                ->orderBy('name')
                ->get(),
        ]);
    }
}
