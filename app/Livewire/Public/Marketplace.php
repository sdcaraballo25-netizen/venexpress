<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\VenezuelaLocationService;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Vitrina pública del marketplace. Cualquier visitante puede navegar
 * el catálogo y dejar un pedido; el pago lo acuerdan comprador y
 * vendedor por fuera de la plataforma (ver PedidoService) — aquí solo
 * se muestra el contacto del emprendedor una vez el pedido queda
 * registrado.
 */
class Marketplace extends Component
{
    use ResolvesLayoutForViewer;
    use WithPagination;

    public ?int $selectedProductoId = null;

    public string $cliente_nombre = '';

    public string $cliente_id_doc = '';

    public string $cliente_telefono = '';

    public string $destino_estado = '';

    public string $destino_ciudad = '';

    public string $cantidad = '1';

    public array $states = [];

    public array $cities = [];

    public ?Pedido $pedidoCreado = null;

    public function mount(VenezuelaLocationService $locationService): void
    {
        $this->states = $locationService->states();
    }

    public function updatedDestinoEstado(VenezuelaLocationService $locationService): void
    {
        $this->destino_ciudad = '';

        $this->cities = $this->destino_estado !== ''
            ? $locationService->citiesByState($this->destino_estado)
            : [];
    }

    public function pedirProducto(int $productoId): void
    {
        $this->selectedProductoId = $productoId;
        $this->pedidoCreado = null;
        $this->resetValidation();
    }

    public function cancelarPedido(): void
    {
        $this->reset([
            'selectedProductoId',
            'cliente_nombre',
            'cliente_id_doc',
            'cliente_telefono',
            'destino_estado',
            'destino_ciudad',
            'cantidad',
            'cities',
        ]);
    }

    public function confirmarPedido(): void
    {
        $producto = Producto::query()
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->with('emprendedor')
            ->findOrFail($this->selectedProductoId);

        $this->validate([
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cliente_id_doc' => ['required', 'string', 'max:30'],
            'cliente_telefono' => ['required', 'string', 'max:30'],
            'destino_estado' => ['required', 'string'],
            'destino_ciudad' => ['required', 'string'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:' . $producto->stock],
        ]);

        $cantidad = (int) $this->cantidad;

        $pedido = Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $producto->emprendedor_id,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $producto->precio_usd,
            'precio_total_usd' => $producto->precio_usd * $cantidad,
            'cliente_nombre' => $this->cliente_nombre,
            'cliente_id_doc' => $this->cliente_id_doc,
            'cliente_telefono' => $this->cliente_telefono,
            'destino_ciudad' => $this->destino_ciudad,
            'destino_estado' => $this->destino_estado,
            'status' => Pedido::STATUS_PENDIENTE,
        ]);

        $this->pedidoCreado = $pedido->load('emprendedor.user', 'producto');
        $this->selectedProductoId = null;
    }

    public function render()
    {
        $productos = Producto::query()
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->whereHas('emprendedor', fn ($query) => $query->where('status', Emprendedor::STATUS_ACTIVE))
            ->with('emprendedor')
            ->latest()
            ->paginate(12);

        $productoSeleccionado = $this->selectedProductoId
            ? Producto::find($this->selectedProductoId)
            : null;

        return view('public.marketplace', [
            'productos' => $productos,
            'productoSeleccionado' => $productoSeleccionado,
        ])->layout(
            $this->resolveLayoutForViewer(),
            ['title' => 'Tienda — Venexpress']
        );
    }
}
