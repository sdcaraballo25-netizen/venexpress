<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\VenezuelaLocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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

    public ?int $viewingProductoId = null;

    /**
     * Cuando se navega a la página de una tienda puntual
     * (public.marketplace.store), el catálogo se filtra a solo los
     * productos de este emprendedor y el encabezado muestra su
     * negocio en vez del genérico "Tienda de Emprendedores".
     */
    public ?Emprendedor $tiendaEmprendedor = null;

    public string $cliente_nombre = '';

    public string $cliente_id_doc = '';

    public string $cliente_telefono = '';

    public string $destino_estado = '';

    public string $destino_ciudad = '';

    public string $direccion_entrega = '';

    public string $referencia_entrega = '';

    public string $cantidad = '1';

    public array $states = [];

    public array $cities = [];

    public ?Pedido $pedidoCreado = null;

    public function mount(VenezuelaLocationService $locationService, ?Emprendedor $emprendedor = null): void
    {
        $this->states = $locationService->states();

        if ($emprendedor && $emprendedor->status === Emprendedor::STATUS_ACTIVE) {
            $this->tiendaEmprendedor = $emprendedor;
        }
    }

    public function verProducto(int $productoId): void
    {
        $this->viewingProductoId = $productoId;
    }

    public function cerrarDetalle(): void
    {
        $this->viewingProductoId = null;
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
        $this->viewingProductoId = null;
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
            'direccion_entrega',
            'referencia_entrega',
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
            'direccion_entrega' => ['required', 'string', 'max:500'],
            'referencia_entrega' => ['nullable', 'string', 'max:255'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:' . $producto->stock],
        ]);

        $cantidad = (int) $this->cantidad;

        $pedido = Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $producto->emprendedor_id,
            // Nullable: el comprador nunca necesitó cuenta para pedir
            // (accede al chat por chat_token). Si sí tiene sesión
            // iniciada como Cliente, lo enlazamos para que aparezca en
            // su panel ("Mis Compras") sin depender de guardar el
            // enlace del chat.
            'user_id' => Auth::check() && Auth::user()->isCliente() ? Auth::id() : null,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $producto->precio_usd,
            'precio_total_usd' => $producto->precio_usd * $cantidad,
            'cliente_nombre' => $this->cliente_nombre,
            'cliente_id_doc' => $this->cliente_id_doc,
            'cliente_telefono' => $this->cliente_telefono,
            'destino_ciudad' => $this->destino_ciudad,
            'destino_estado' => $this->destino_estado,
            'direccion_entrega' => $this->direccion_entrega,
            'referencia_entrega' => $this->referencia_entrega !== '' ? $this->referencia_entrega : null,
            'status' => Pedido::STATUS_PENDIENTE,
            'chat_token' => Str::random(40),
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
            ->when(
                $this->tiendaEmprendedor,
                fn ($query) => $query->where('emprendedor_id', $this->tiendaEmprendedor->id)
            )
            ->with('emprendedor')
            ->latest()
            ->paginate(12);

        $productoSeleccionado = $this->selectedProductoId
            ? Producto::find($this->selectedProductoId)
            : null;

        $productoViendo = $this->viewingProductoId
            ? Producto::with('emprendedor')->find($this->viewingProductoId)
            : null;

        return view('public.marketplace', [
            'productos' => $productos,
            'productoSeleccionado' => $productoSeleccionado,
            'productoViendo' => $productoViendo,
        ])->layout(
            $this->resolveLayoutForViewer(),
            [
                'title' => $this->tiendaEmprendedor
                    ? "{$this->tiendaEmprendedor->business_name} — Venexpress"
                    : 'Tienda — Venexpress',
            ]
        );
    }
}
