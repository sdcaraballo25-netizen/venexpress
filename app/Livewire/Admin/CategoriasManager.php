<?php

namespace App\Livewire\Admin;

use App\Models\Categoria;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * CRUD mínimo de categorías del marketplace. Mismo patrón simple que
 * Admin\WarehousesManager: sin búsqueda ni paginación, se esperan
 * pocas categorías.
 */
#[Layout('layouts.admin')]
#[Title('Categorías')]
class CategoriasManager extends Component
{
    public bool $showForm = false;

    public ?int $editingCategoriaId = null;

    public string $nombre = '';

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function startCreating(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function editCategoria(int $categoriaId): void
    {
        $categoria = Categoria::findOrFail($categoriaId);

        $this->editingCategoriaId = $categoria->id;
        $this->nombre = $categoria->nombre;

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->showForm = false;

        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingCategoriaId', 'nombre']);
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => [
                'required', 'string', 'max:255',
                Rule::unique('categorias', 'nombre')->ignore($this->editingCategoriaId),
            ],
        ]);

        $data = [
            'nombre' => $this->nombre,
            'slug' => str($this->nombre)->slug(),
        ];

        if ($this->editingCategoriaId) {
            Categoria::findOrFail($this->editingCategoriaId)->update($data);

            $this->successMessage = 'Categoría actualizada correctamente.';
        } else {
            Categoria::create($data);

            $this->successMessage = 'Categoría creada correctamente.';
        }

        $this->cancelForm();
    }

    public function eliminar(int $categoriaId): void
    {
        $this->errorMessage = null;

        $categoria = Categoria::withCount('productos')->findOrFail($categoriaId);

        if ($categoria->productos_count > 0) {
            $this->errorMessage = 'No puedes eliminar una categoría con productos asignados. '
                .'Cámbiales la categoría primero.';

            return;
        }

        $categoria->delete();

        $this->successMessage = 'Categoría eliminada.';
    }

    public function render()
    {
        return view('livewire.admin.categorias-manager', [
            'categorias' => Categoria::withCount('productos')->orderBy('nombre')->get(),
        ]);
    }
}
