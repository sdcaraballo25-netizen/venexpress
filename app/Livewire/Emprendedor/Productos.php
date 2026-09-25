<?php

namespace App\Livewire\Emprendedor;

use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

    /**
     * Límite de fotos de galería (además de la portada) por producto —
     * suficiente para mostrar el producto desde varios ángulos sin
     * convertir el formulario en un uploader ilimitado.
     */
    protected const MAX_FOTOS_GALERIA = 5;

    public bool $showForm = false;

    public ?int $editingProductoId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $categoria = '';

    public string $precio_usd = '';

    public string $peso_kg = '';

    public string $stock = '';

    public $foto = null;

    public ?string $existingFotoPath = null;

    /** @var TemporaryUploadedFile[] */
    public array $fotosNuevas = [];

    /** Rutas (storage/public) de la galería ya guardada, al editar. */
    public array $existingFotos = [];

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
        $this->categoria = $producto->categoria ?? '';
        $this->precio_usd = (string) $producto->precio_usd;
        $this->peso_kg = (string) $producto->peso_kg;
        $this->stock = (string) $producto->stock;
        $this->existingFotoPath = $producto->foto_path;
        $this->foto = null;
        $this->existingFotos = $producto->fotos ?? [];
        $this->fotosNuevas = [];

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
            'categoria',
            'precio_usd',
            'peso_kg',
            'stock',
            'foto',
            'existingFotoPath',
            'fotosNuevas',
            'existingFotos',
        ]);
    }

    /**
     * Quita una foto ya guardada de la galería (solo del formulario en
     * edición; se persiste al guardar, igual que el resto de los
     * campos). No borra el archivo del disco — mismo criterio que ya
     * usa este componente al reemplazar la portada.
     */
    public function eliminarFotoExistente(int $index): void
    {
        unset($this->existingFotos[$index]);

        $this->existingFotos = array_values($this->existingFotos);
    }

    public function eliminarFotoNueva(int $index): void
    {
        unset($this->fotosNuevas[$index]);

        $this->fotosNuevas = array_values($this->fotosNuevas);
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'categoria' => ['nullable', 'string', Rule::in(array_keys(Producto::CATEGORIAS))],
            'precio_usd' => ['required', 'numeric', 'min:0.01'],
            'peso_kg' => ['required', 'numeric', 'min:0.01'],
            'stock' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'fotosNuevas' => [
                'array',
                function ($attribute, $value, $fail) {
                    if (count($this->existingFotos) + count($value) > self::MAX_FOTOS_GALERIA) {
                        $fail('Puedes tener hasta '.self::MAX_FOTOS_GALERIA.' fotos de galería por producto.');
                    }
                },
            ],
            'fotosNuevas.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data = [
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion !== '' ? $this->descripcion : null,
            'categoria' => $this->categoria !== '' ? $this->categoria : null,
            'precio_usd' => $this->precio_usd,
            'peso_kg' => $this->peso_kg,
            'stock' => $this->stock,
            'fotos' => [
                ...$this->existingFotos,
                ...array_map(fn ($foto) => $foto->store('productos', 'public'), $this->fotosNuevas),
            ],
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
