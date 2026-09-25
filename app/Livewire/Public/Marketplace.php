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
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

/**
 * Vitrina pública del marketplace. Cualquier visitante puede navegar
 * el catálogo, armar un carrito y dejar un pedido; el pago lo acuerdan
 * comprador y vendedor por fuera de la plataforma (ver PedidoService)
 * — aquí solo se muestra el contacto del emprendedor una vez el
 * pedido queda registrado.
 *
 * El carrito solo admite productos de UN emprendedor a la vez: un
 * Pedido tiene un solo remitente/agencia de retiro (mismo criterio
 * que ya usaba el flujo de "Pedir" un solo producto). Se guarda en la
 * sesión (no en base de datos): es estado de compra, no un pedido
 * real todavía.
 */
class Marketplace extends Component
{
    use ResolvesLayoutForViewer;
    use WithPagination;

    protected const SESSION_KEY = 'marketplace_cart';

    public ?int $viewingProductoId = null;

    /**
     * Cuando se navega a la página de una tienda puntual
     * (public.marketplace.store), el catálogo se filtra a solo los
     * productos de este emprendedor y el encabezado muestra su
     * negocio en vez del genérico "Tienda de Emprendedores".
     */
    public ?Emprendedor $tiendaEmprendedor = null;

    /** @var array<int, int> [producto_id => cantidad] */
    public array $carrito = [];

    public bool $showCarrito = false;

    public bool $showCheckout = false;

    public ?string $carritoError = null;

    /**
     * Cuando se intenta agregar un producto de OTRO emprendedor con el
     * carrito no vacío: guarda el producto en conflicto para que la
     * vista pregunte "¿vaciar el carrito y agregar este?" en vez de
     * mezclar tiendas silenciosamente.
     */
    public ?int $conflictoProductoId = null;

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

        $this->carrito = array_filter(
            (array) session(self::SESSION_KEY, []),
            fn ($cantidad) => is_int($cantidad) && $cantidad > 0
        );

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

    protected function guardarCarritoEnSesion(): void
    {
        session([self::SESSION_KEY => $this->carrito]);
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
    | CARRITO
    |--------------------------------------------------------------------------
    */

    public function agregarAlCarrito(int $productoId, int $cantidad = 1): void
    {
        $this->carritoError = null;
        $this->conflictoProductoId = null;

        $producto = Producto::query()
            ->where('activo', true)
            ->where('stock', '>', 0)
            ->find($productoId);

        if (! $producto) {
            $this->carritoError = 'Este producto ya no está disponible.';

            return;
        }

        if ($this->carrito && ! isset($this->carrito[$productoId])) {
            $emprendedorActual = $this->carritoEmprendedorId();

            if ($emprendedorActual !== null && $emprendedorActual !== $producto->emprendedor_id) {
                $this->conflictoProductoId = $productoId;

                return;
            }
        }

        $cantidadDeseada = ($this->carrito[$productoId] ?? 0) + max(1, $cantidad);

        if ($cantidadDeseada > $producto->stock) {
            $this->carritoError = "Solo quedan {$producto->stock} unidad(es) de \"{$producto->nombre}\".";

            return;
        }

        $this->carrito[$productoId] = $cantidadDeseada;
        $this->guardarCarritoEnSesion();
        $this->viewingProductoId = null;
        $this->showCarrito = true;
    }

    /**
     * Producto de otra tienda distinta a la que ya está en el
     * carrito: el usuario confirmó explícitamente que quiere vaciarlo
     * y empezar de nuevo con este producto.
     */
    public function vaciarYAgregar(int $productoId): void
    {
        $this->carrito = [];
        $this->conflictoProductoId = null;
        $this->agregarAlCarrito($productoId);
    }

    public function cancelarConflicto(): void
    {
        $this->conflictoProductoId = null;
    }

    protected function carritoEmprendedorId(): ?int
    {
        if (! $this->carrito) {
            return null;
        }

        return Producto::whereIn('id', array_keys($this->carrito))->value('emprendedor_id');
    }

    public function actualizarCantidad(int $productoId, int $cantidad): void
    {
        $this->carritoError = null;

        if ($cantidad < 1) {
            $this->quitarDelCarrito($productoId);

            return;
        }

        $producto = Producto::find($productoId);

        if (! $producto) {
            $this->quitarDelCarrito($productoId);

            return;
        }

        if ($cantidad > $producto->stock) {
            $this->carritoError = "Solo quedan {$producto->stock} unidad(es) de \"{$producto->nombre}\".";
            $cantidad = $producto->stock;
        }

        $this->carrito[$productoId] = $cantidad;
        $this->guardarCarritoEnSesion();
    }

    public function quitarDelCarrito(int $productoId): void
    {
        unset($this->carrito[$productoId]);
        $this->guardarCarritoEnSesion();
    }

    public function vaciarCarrito(): void
    {
        $this->carrito = [];
        $this->guardarCarritoEnSesion();
        $this->showCarrito = false;
        $this->showCheckout = false;
    }

    public function toggleCarrito(): void
    {
        $this->showCarrito = ! $this->showCarrito;
    }

    public function abrirCheckout(): void
    {
        if (! $this->carrito) {
            return;
        }

        $this->showCheckout = true;
        $this->showCarrito = false;
        $this->resetValidation();
    }

    public function cancelarCheckout(): void
    {
        $this->showCheckout = false;
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{producto: Producto, cantidad: int, subtotal: float}>
     */
    protected function carritoDetalle(): \Illuminate\Support\Collection
    {
        if (! $this->carrito) {
            return collect();
        }

        $productos = Producto::whereIn('id', array_keys($this->carrito))->get()->keyBy('id');

        return collect($this->carrito)
            ->map(function (int $cantidad, int $productoId) use ($productos) {
                $producto = $productos->get($productoId);

                if (! $producto) {
                    return null;
                }

                return (object) [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'subtotal' => (float) $producto->precio_usd * $cantidad,
                ];
            })
            ->filter()
            ->values();
    }

    public function confirmarPedido(): void
    {
        $detalle = $this->carritoDetalle();

        if ($detalle->isEmpty()) {
            $this->carritoError = 'Tu carrito está vacío.';
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

        foreach ($detalle as $linea) {
            if ($linea->cantidad > $linea->producto->stock) {
                $this->carritoError = "Solo quedan {$linea->producto->stock} unidad(es) de \"{$linea->producto->nombre}\".";
                $this->showCheckout = false;

                return;
            }
        }

        $emprendedorId = $detalle->first()->producto->emprendedor_id;
        $totalUsd = $detalle->sum('subtotal');

        $pedido = DB::transaction(function () use ($detalle, $emprendedorId, $totalUsd) {
            $pedido = Pedido::create([
                'emprendedor_id' => $emprendedorId,
                // Nullable: el comprador nunca necesitó cuenta para pedir
                // (accede al chat por chat_token). Si sí tiene sesión
                // iniciada como Cliente, lo enlazamos para que aparezca en
                // su panel ("Mis Compras") sin depender de guardar el
                // enlace del chat.
                'user_id' => Auth::check() && Auth::user()->isCliente() ? Auth::id() : null,
                'precio_total_usd' => $totalUsd,
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

            foreach ($detalle as $linea) {
                PedidoItem::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $linea->producto->id,
                    'cantidad' => $linea->cantidad,
                    'precio_unitario_usd' => $linea->producto->precio_usd,
                    'subtotal_usd' => $linea->subtotal,
                ]);
            }

            return $pedido;
        });

        $this->notificarEmprendedor($pedido);

        $this->pedidoCreado = $pedido->load('emprendedor.user', 'items.producto');
        $this->vaciarCarrito();
        $this->showCheckout = false;
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
                        ->where('nombre', 'like', '%' . trim($this->busqueda) . '%')
                        ->orWhere('descripcion', 'like', '%' . trim($this->busqueda) . '%')
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

        $carritoDetalle = $this->carritoDetalle();

        return view('public.marketplace', [
            'productos' => $productos,
            'productoViendo' => $productoViendo,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'carritoDetalle' => $carritoDetalle,
            'carritoTotal' => $carritoDetalle->sum('subtotal'),
            'carritoCount' => $carritoDetalle->sum('cantidad'),
            'conflictoProducto' => $this->conflictoProductoId ? Producto::find($this->conflictoProductoId) : null,
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
