<?php

namespace App\Livewire\Emprendedor;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\ProductoFoto;
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

    public string $categoria_id = '';

    public string $precio_usd = '';

    public string $peso_kg = '';

    public string $stock = '';

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $fotos = [];

    /** @var \Illuminate\Support\Collection<int, ProductoFoto> */
    public $existingFotos = [];

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
            ->with('fotos')
            ->findOrFail($productoId);

        $this->editingProductoId = $producto->id;
        $this->nombre = $producto->nombre;
        $this->descripcion = $producto->descripcion ?? '';
        $this->categoria_id = $producto->categoria_id ? (string) $producto->categoria_id : '';
        $this->precio_usd = (string) $producto->precio_usd;
        $this->peso_kg = (string) $producto->peso_kg;
        $this->stock = (string) $producto->stock;
        $this->existingFotoPath = $producto->foto_path;
        $this->existingFotos = $producto->fotos;
        $this->fotos = [];

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
            'categoria_id',
            'precio_usd',
            'peso_kg',
            'stock',
            'fotos',
            'existingFotoPath',
            'existingFotos',
        ]);

        $this->existingFotos = collect();
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'categoria_id' => ['nullable', 'integer', 'exists:categorias,id'],
            'precio_usd' => ['required', 'numeric', 'min:0.01'],
            'peso_kg' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'fotos' => ['nullable', 'array', 'max:6'],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'categoria_id' => $this->categoria_id !== '' ? (int) $this->categoria_id : null,
            'precio_usd' => $this->precio_usd,
            'peso_kg' => $this->peso_kg,
            'stock' => $this->stock,
        ];

        if ($this->editingProductoId) {
            $producto = Producto::where('emprendedor_id', $this->emprendedor()->id)
                ->findOrFail($this->editingProductoId);

            $producto->update($data);

            $this->successMessage = 'Producto actualizado correctamente.';
        } else {
            $producto = Producto::create([
                ...$data,
                'emprendedor_id' => $this->emprendedor()->id,
                'activo' => true,
            ]);

            $this->successMessage = 'Producto creado correctamente.';
        }

        if ($this->fotos) {
            $siguienteOrden = 1 + (int) $producto->fotos()->max('orden');

            foreach ($this->fotos as $foto) {
                ProductoFoto::create([
                    'producto_id' => $producto->id,
                    'path' => $foto->store('productos', 'public'),
                    'orden' => $siguienteOrden++,
                ]);
            }
        }

        $this->cancelForm();
    }

    /**
     * Quita una foto de la galería mientras se edita el formulario
     * (sin necesitar guardar aparte: es una acción directa, igual que
     * toggleActivo()).
     */
    public function eliminarFoto(int $fotoId): void
    {
        $foto = ProductoFoto::whereHas(
            'producto',
            fn ($query) => $query->where('emprendedor_id', $this->emprendedor()->id)
        )->findOrFail($fotoId);

        $foto->delete();

        $this->existingFotos = $this->existingFotos->reject(fn (ProductoFoto $f) => $f->id === $fotoId);
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
                ->with('fotos')
                ->latest()
                ->get(),
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }
}
