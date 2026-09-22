<?php

namespace App\Livewire\Emprendedor;

use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * CRUD mínimo del catálogo del emprendedor. Mismo patrón simple que
 * Admin\WarehousesManager (sin búsqueda ni paginación): un emprendedor
 * individual no maneja cientos de productos en esta etapa.
 */
#[Layout('layouts.emprendedor')]
#[Title('Mis Productos')]
class Productos extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingProductoId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $precio_usd = '';

    public string $peso_kg = '';

    public string $stock = '';

    public $foto = null;

    public ?string $existingFotoPath = null;

    public ?string $successMessage = null;

    protected function emprendedor()
    {
        return Auth::user()->emprendedor;
    }

    public function startCreating(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function editProducto(int $productoId): void
    {
        $producto = Producto::where('emprendedor_id', $this->emprendedor()->id)
            ->findOrFail($productoId);

        $this->editingProductoId = $producto->id;
        $this->nombre = $producto->nombre;
        $this->descripcion = $producto->descripcion ?? '';
        $this->precio_usd = (string) $producto->precio_usd;
        $this->peso_kg = (string) $producto->peso_kg;
        $this->stock = (string) $producto->stock;
        $this->existingFotoPath = $producto->foto_path;
        $this->foto = null;

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
            'editingProductoId',
            'nombre',
            'descripcion',
            'precio_usd',
            'peso_kg',
            'stock',
            'foto',
            'existingFotoPath',
        ]);
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'precio_usd' => ['required', 'numeric', 'min:0.01'],
            'peso_kg' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'precio_usd' => $this->precio_usd,
            'peso_kg' => $this->peso_kg,
            'stock' => $this->stock,
        ];

        if ($this->foto) {
            $data['foto_path'] = $this->foto->store('productos', 'public');
        }

        if ($this->editingProductoId) {
            Producto::where('emprendedor_id', $this->emprendedor()->id)
                ->findOrFail($this->editingProductoId)
                ->update($data);

            $this->successMessage = 'Producto actualizado correctamente.';
        } else {
            Producto::create([
                ...$data,
                'emprendedor_id' => $this->emprendedor()->id,
                'activo' => true,
            ]);

            $this->successMessage = 'Producto creado correctamente.';
        }

        $this->cancelForm();
    }

    public function toggleActivo(int $productoId): void
    {
        $producto = Producto::where('emprendedor_id', $this->emprendedor()->id)
            ->findOrFail($productoId);

        $producto->update([
            'activo' => ! $producto->activo,
        ]);

        $this->successMessage = $producto->activo
            ? 'Producto activado.'
            : 'Producto desactivado.';
    }

    public function render()
    {
        return view('livewire.emprendedor.productos', [
            'productos' => Producto::query()
                ->where('emprendedor_id', $this->emprendedor()->id)
                ->latest()
                ->get(),
        ]);
    }
}
