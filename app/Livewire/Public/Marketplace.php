<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Categoria;
use App\Models\Customer;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Notifications\NuevoPedidoRecibido;
use App\Services\VenezuelaLocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Vitrina pública del marketplace. Cualquier visitante puede navegar
 * el catálogo y comprar UN producto a la vez; el pago lo acuerdan
 * comprador y vendedor por fuera de la plataforma (ver PedidoService)
 * — aquí solo se muestra el contacto del emprendedor una vez el
 * pedido queda registrado.
 *
 * Sin carrito: "Comprar" en un producto va directo al checkout de
 * ese único producto (Pedido + un solo PedidoItem). Pedido/PedidoItem
 * siguen soportando varias líneas por pedido (no se limitó el
 * esquema) para no bloquear un futuro carrito real, pero este flujo
 * nunca crea más de una línea.
 */
class Marketplace extends Component
{
    use ResolvesLayoutForViewer;
    use WithPagination;

    public ?int $viewingProductoId = null;

    /**
     * Cuando se navega a la página de una tienda puntual
     * (public.marketplace.store), el catálogo se filtra a solo los
     * productos de este emprendedor y el encabezado muestra su
     * negocio en vez del genérico "Tienda de Emprendedores".
     */
    public ?Emprendedor $tiendaEmprendedor = null;

    public ?int $productoComprarId = null;

    public int $cantidadComprar = 1;

    public bool $showCheckout = false;

    public ?string $compraError = null;

    public string $cliente_nombre = '';

    public string $cliente_id_doc = '';

    public string $cliente_telefono = '';

    public string $cliente_email = '';

    public string $destino_estado = '';

    public string $destino_ciudad = '';

    public string $direccion_entrega = '';

    public string $referencia_entrega = '';

    public array $states = [];

    public array $cities = [];

    public ?Pedido $pedidoCreado = null;

    /**
     * #[Url]: permite compartir/guardar un enlace de búsqueda tipo
     * /tienda?buscar=mango, igual que el criterio ya usado en
     * Admin\UsersManager.
     */
    #[Url(as: 'buscar')]
    public string $busqueda = '';

    #[Url(as: 'categoria')]
    public string $categoriaId = '';

    /**
     * True cuando quien pide está logueado como Cliente y ya tiene un
     * registro Customer con su cédula (viene de su registro en la
     * plataforma) — en ese caso no tiene sentido volver a pedirle
     * nombre/cédula/teléfono: se prellenan y el campo queda oculto,
     * solo pide la dirección de entrega. Un invitado, o un Cliente sin
     * ese registro todavía, sigue viendo los campos editables.
     */
    public bool $clienteAutenticadoConDatos = false;

    public function mount(VenezuelaLocationService $locationService, ?Emprendedor $emprendedor = null): void
    {
        $this->states = $locationService->states();

        // $emprendedor->exists (no solo truthy): cuando no hay
        // {emprendedor} en la ruta actual, la inyección de dependencias
        // puede resolver este parámetro tipado como un Emprendedor
        // "fantasma" (new Emprendedor() vacío, nunca guardado) en vez
        // de null — ->exists distingue eso de un modelo real
        // enlazado por la ruta.
        if ($emprendedor?->exists) {
            // Antes, una tienda suspendida/pendiente caía en silencio al
            // catálogo genérico (el filtro de $emprendedor no
            // coincidía con nada), mostrando "Tienda de Emprendedores"
            // en vez de un error — confuso para quien abre un enlace de
            // tienda compartido. Se trata igual que un id inexistente.
            abort_unless($emprendedor->status === Emprendedor::STATUS_ACTIVE, 404);

            $this->tiendaEmprendedor = $emprendedor->loadMissing('user');
        }

        $this->prefillDatosCliente();
    }

    protected function prefillDatosCliente(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->isCliente()) {
            return;
        }

        $customer = Customer::where('user_id', $user->id)->first();

        if (! $customer) {
            return;
        }

        $this->cliente_nombre = $user->name;
        $this->cliente_id_doc = $customer->id_doc;
        $this->cliente_telefono = $customer->phone ?: ($user->phone ?? '');
        $this->cliente_email = $user->email;
        $this->clienteAutenticadoConDatos = true;
    }

    public function updatedBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatedCategoriaId(): void
    {
        $this->resetPage();
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

    /*
    |--------------------------------------------------------------------------
    | COMPRA (un solo producto)
    |--------------------------------------------------------------------------
    */

    public function comprarProducto(int $productoId, int $cantidad = 1): void
    {
        $this->compraError = null;

        $producto = Producto::query()
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->find($productoId);

        if (! $producto) {
            $this->compraError = 'Este producto ya no está disponible.';

            return;
        }

        $cantidad = max(1, $cantidad);

        if ($cantidad > $producto->stock) {
            $this->compraError = "Solo quedan {$producto->stock} unidad(es) de \"{$producto->nombre}\".";

            return;
        }

        $this->productoComprarId = $productoId;
        $this->cantidadComprar = $cantidad;
        $this->viewingProductoId = null;
        $this->showCheckout = true;
        $this->resetValidation();
    }

    public function cancelarCheckout(): void
    {
        $this->showCheckout = false;
        $this->productoComprarId = null;
        $this->cantidadComprar = 1;
    }

    /**
     * Línea de la compra en curso (el único producto seleccionado), o
     * null si no hay ninguno todavía.
     */
    protected function productoComprarDetalle(): ?object
    {
        if (! $this->productoComprarId) {
            return null;
        }

        $producto = Producto::find($this->productoComprarId);

        if (! $producto) {
            return null;
        }

        return (object) [
            'producto' => $producto,
            'cantidad' => $this->cantidadComprar,
            'subtotal' => (float) $producto->precio_usd * $this->cantidadComprar,
        ];
    }

    public function confirmarPedido(): void
    {
        $linea = $this->productoComprarDetalle();

        if (! $linea) {
            $this->compraError = 'Este producto ya no está disponible.';
            $this->showCheckout = false;

            return;
        }

        $this->validate([
            'cliente_nombre' => ['required', 'string', 'max:255'],
            'cliente_id_doc' => ['required', 'string', 'max:30'],
            'cliente_telefono' => ['required', 'string', 'max:30'],
            'cliente_email' => ['nullable', 'email', 'max:255'],
            'destino_estado' => ['required', 'string'],
            'destino_ciudad' => ['required', 'string'],
            'direccion_entrega' => ['required', 'string', 'max:500'],
            'referencia_entrega' => ['nullable', 'string', 'max:255'],
        ]);

        if ($linea->cantidad > $linea->producto->stock) {
            $this->compraError = "Solo quedan {$linea->producto->stock} unidad(es) de \"{$linea->producto->nombre}\".";
            $this->showCheckout = false;

            return;
        }

        $pedido = DB::transaction(function () use ($linea) {
            $pedido = Pedido::create([
                'emprendedor_id' => $linea->producto->emprendedor_id,
                // Nullable: el comprador nunca necesitó cuenta para pedir
                // (accede al chat por chat_token). Si sí tiene sesión
                // iniciada como Cliente, lo enlazamos para que aparezca en
                // su panel ("Mis Compras") sin depender de guardar el
                // enlace del chat.
                'user_id' => Auth::check() && Auth::user()->isCliente() ? Auth::id() : null,
                'precio_total_usd' => $linea->subtotal,
                'cliente_nombre' => $this->cliente_nombre,
                'cliente_id_doc' => $this->cliente_id_doc,
                'cliente_telefono' => $this->cliente_telefono,
                'cliente_email' => $this->cliente_email !== '' ? $this->cliente_email : null,
                'destino_ciudad' => $this->destino_ciudad,
                'destino_estado' => $this->destino_estado,
                'direccion_entrega' => $this->direccion_entrega,
                'referencia_entrega' => $this->referencia_entrega !== '' ? $this->referencia_entrega : null,
                'status' => Pedido::STATUS_PENDIENTE,
                'chat_token' => Str::random(40),
            ]);

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $linea->producto->id,
                'cantidad' => $linea->cantidad,
                'precio_unitario_usd' => $linea->producto->precio_usd,
                'subtotal_usd' => $linea->subtotal,
            ]);

            return $pedido;
        });

        $this->notificarEmprendedor($pedido);

        $this->pedidoCreado = $pedido->load('emprendedor.user', 'items.producto');
        $this->cancelarCheckout();
    }

    /**
     * Avisa por correo al emprendedor que le llegó un pedido nuevo.
     * Antes no había ninguna notificación de esto. Protegido en
     * try/catch, mismo criterio que PackageService::notifyPackageCreated:
     * un fallo de correo nunca revierte un pedido ya guardado.
     */
    protected function notificarEmprendedor(Pedido $pedido): void
    {
        try {
            $email = $pedido->emprendedor?->user?->email;

            if (! $email) {
                return;
            }

            Notification::route('mail', $email)
                ->notify(new NuevoPedidoRecibido($pedido->id));
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar la notificación de pedido nuevo al emprendedor.', [
                'pedido_id' => $pedido->id,
                'error' => $e->getMessage(),
            ]);
        }
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
            ->when(
                $this->categoriaId !== '',
                fn ($query) => $query->where('categoria_id', $this->categoriaId)
            )
            ->when(
                trim($this->busqueda) !== '',
                fn ($query) => $query->where(
                    fn ($sub) => $sub
                        ->where('nombre', 'like', '%'.trim($this->busqueda).'%')
                        ->orWhere('descripcion', 'like', '%'.trim($this->busqueda).'%')
                )
            )
            ->with(['emprendedor', 'fotos'])
            ->withAvg('resenas', 'estrellas')
            ->withCount('resenas')
            ->latest()
            ->paginate(12);

        $productoViendo = $this->viewingProductoId
            ? Producto::with(['emprendedor', 'fotos'])->withAvg('resenas', 'estrellas')->withCount('resenas')->find($this->viewingProductoId)
            : null;

        return view('public.marketplace', [
            'productos' => $productos,
            'productoViendo' => $productoViendo,
            'categorias' => Categoria::withCount(['productos' => fn ($query) => $query
                ->where('activo', true)
                ->where('stock', '>', 0)
                ->whereHas('emprendedor', fn ($sub) => $sub->where('status', Emprendedor::STATUS_ACTIVE)),
            ])->orderBy('nombre')->get(),
            'productoComprar' => $this->productoComprarDetalle(),
        ])->layout(
            $this->resolveLayoutForViewer('layouts.marketplace'),
            [
                'title' => $this->tiendaEmprendedor
                    ? "{$this->tiendaEmprendedor->business_name} — Venexpress"
                    : 'Tienda — Venexpress',
            ]
        );
    }
}
