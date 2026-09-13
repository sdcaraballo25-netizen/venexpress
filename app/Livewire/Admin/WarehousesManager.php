<?php

namespace App\Livewire\Admin;

use App\Models\Warehouse;
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

    public function startCreating(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function editWarehouse(int $warehouseId): void
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        $this->editingWarehouseId = $warehouse->id;
        $this->name = $warehouse->name;
        $this->city = $warehouse->city;
        $this->state = $warehouse->state;
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
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
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

    public function render()
    {
        return view('livewire.admin.warehouses-manager', [
            'warehouses' => Warehouse::query()
                ->orderBy('name')
                ->get(),
        ]);
    }
}
