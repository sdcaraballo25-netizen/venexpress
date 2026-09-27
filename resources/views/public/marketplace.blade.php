<div>

    {{-- =============================================================
         HEADER DEL MARKETPLACE
         Propio de /tienda (no es el navbar público general). Vive
         dentro del componente Livewire porque necesita datos en vivo:
         busqueda (wire:model), carritoCount, sesión de usuario.
    ============================================================== --}}
    <header class="sticky top-0 z-40 bg-white border-b border-gray-100">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-10">

            <div class="flex items-center gap-4 py-4">

                <a href="{{ route('public.marketplace') }}" wire:navigate class="shrink-0 flex items-center gap-2">
                    <img src="{{ asset('images/venexpress-logo.png') }}" alt="Venexpress" class="h-7 sm:h-8 w-auto">
                    <span class="hidden sm:inline text-sm font-bold text-gray-400 border-l border-gray-200 pl-2">
                        Tienda
                    </span>
                </a>

                {{-- BUSCADOR — desktop, dentro del header --}}
                <div class="hidden lg:block flex-1 max-w-2xl relative">
                    <input type="search" wire:model.live.debounce.400ms="busqueda"
                           placeholder="Buscar productos, marcas y más..."
                           class="w-full rounded-xl border border-gray-200 bg-gray-50 focus:bg-white pl-5 pr-12 py-3 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 ml-auto">

                    <a href="{{ route('public.help') }}"
                       class="hidden sm:inline-flex items-center justify-center h-10 w-10 rounded-full text-gray-500 hover:bg-gray-50 hover:text-[#111111] transition"
                       aria-label="Ayuda">
                        <i class="fa-regular fa-circle-question text-lg"></i>
                    </a>

                    <button wire:click="toggleCarrito" type="button"
                            class="relative inline-flex items-center justify-center h-10 w-10 rounded-full text-[#111111] hover:bg-gray-50 transition"
                            aria-label="Ver carrito">
                        <i class="fa-solid fa-cart-shopping text-lg"></i>
                        @if ($carritoCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-amber-400 text-[#111111] text-[0.65rem] font-bold">
                                {{ $carritoCount }}
                            </span>
                        @endif
                    </button>

                    @guest
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center h-10 w-10 rounded-full text-gray-500 hover:bg-gray-50 hover:text-[#111111] transition"
                           aria-label="Iniciar sesión">
                            <i class="fa-regular fa-user text-lg"></i>
                        </a>
                        <a href="{{ route('register') }}"
                           class="hidden sm:inline-flex items-center justify-center bg-amber-400 hover:bg-amber-500 text-[#111111] font-semibold text-sm px-4 py-2.5 rounded-lg transition whitespace-nowrap">
                            Regístrate
                        </a>
                    @else
                        <a href="{{ route('profile') }}"
                           class="inline-flex items-center justify-center h-10 w-10 rounded-full text-gray-500 hover:bg-gray-50 hover:text-[#111111] transition"
                           aria-label="Mi cuenta">
                            <i class="fa-regular fa-user text-lg"></i>
                        </a>
                    @endguest

                </div>

            </div>

            {{-- BUSCADOR — móvil/tablet, fila propia debajo del header --}}
            <div class="lg:hidden pb-4 relative">
                <input type="search" wire:model.live.debounce.400ms="busqueda"
                       placeholder="Buscar productos, marcas y más..."
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 focus:bg-white pl-5 pr-12 py-3 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition">
                <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
            </div>

            {{-- NAVEGACIÓN — solo escritorio; en móvil las categorías se
                 acceden con el botón encima de la grilla de productos. --}}
            <nav class="hidden lg:flex items-center gap-3 pb-4">

                <a href="{{ route('public.marketplace') }}" wire:navigate
                   class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold {{ ! $tiendaEmprendedor ? 'text-[#111111]' : 'text-gray-500 hover:text-[#111111]' }} transition">
                    Inicio
                </a>

                <x-marketplace-mega-menu :categorias="$categorias" :categoria-id="$categoriaId" />

                <a href="{{ route('public.help') }}"
                   class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-semibold text-gray-500 hover:text-[#111111] transition">
                    Ayuda
                </a>

            </nav>

        </div>
    </header>

    <section class="bg-white">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-10 py-8">

            @if ($tiendaEmprendedor)

                {{-- =========================================================
                     PERFIL DE LA TIENDA — información de confianza para
                     que el comprador vea que es un negocio real antes de
                     pagarle por fuera de la plataforma.
                ========================================================== --}}
                <div class="mb-10">
                    <a href="{{ route('public.marketplace') }}" wire:navigate class="text-xs font-semibold text-gray-500 hover:text-[#111111]">
                        ← Ver toda la tienda
                    </a>

                    <div class="mt-3 rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="relative h-40 sm:h-48 bg-gray-100">
                            @if ($tiendaEmprendedor->cover_photo_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($tiendaEmprendedor->cover_photo_path) }}"
                                     class="w-full h-full object-cover" alt="Portada de {{ $tiendaEmprendedor->business_name }}">
                            @endif

                            <div class="absolute -bottom-10 left-5 h-20 w-20 rounded-2xl bg-white border-4 border-white shadow-sm overflow-hidden">
                                @if ($tiendaEmprendedor->logo_path)
                                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($tiendaEmprendedor->logo_path) }}"
                                         class="w-full h-full object-cover" alt="Logo de {{ $tiendaEmprendedor->business_name }}">
                                @else
                                    <div class="w-full h-full bg-amber-50 flex items-center justify-center text-amber-600 text-2xl font-bold">
                                        {{ strtoupper(substr($tiendaEmprendedor->business_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white pt-12 px-5 pb-5 text-left">
                            <span class="inline-block bg-amber-100 text-amber-800 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-2">
                                Tienda verificada por Venexpress
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-[#111111]">{{ $tiendaEmprendedor->business_name }}</h1>

                            @if ($tiendaEmprendedor->descripcion)
                                <p class="text-gray-600 mt-2 max-w-2xl">{{ $tiendaEmprendedor->descripcion }}</p>
                            @endif

                            <div class="flex flex-wrap gap-x-5 gap-y-1.5 mt-4 text-sm text-gray-500">
                                @if ($tiendaEmprendedor->address)
                                    <span class="inline-flex items-center gap-1.5">📍 {{ $tiendaEmprendedor->address }}</span>
                                @endif
                                @if ($tiendaEmprendedor->user?->phone)
                                    <span class="inline-flex items-center gap-1.5">📞 {{ $tiendaEmprendedor->user->phone }}</span>
                                @endif
                                @if ($tiendaEmprendedor->user?->email)
                                    <span class="inline-flex items-center gap-1.5">✉️ {{ $tiendaEmprendedor->user->email }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            @else
                <div class="mb-8">
                    <span class="inline-block bg-amber-100 text-amber-800 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-3">
                        Marketplace
                    </span>
                    <h1 class="text-2xl md:text-3xl font-extrabold text-[#111111]">Tienda de Emprendedores</h1>
                    <p class="text-gray-500 mt-2 max-w-2xl">
                        Productos de emprendedores venezolanos, con envío por Venexpress. Tú acuerdas el pago
                        directamente con el emprendedor.
                    </p>
                </div>
            @endif

            {{-- =========================================================
                 CARRITO (drawer)
            ========================================================== --}}
            @if ($showCarrito)
                <div class="fixed inset-0 z-50 flex items-start justify-end" wire:click.self="toggleCarrito">
                    <div class="absolute inset-0 bg-black/30"></div>
                    <div class="relative h-full w-full max-w-md bg-white shadow-xl flex flex-col">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-[#111111]">Tu carrito</h2>
                            <button wire:click="toggleCarrito" class="text-gray-400 hover:text-gray-700">✕</button>
                        </div>

                        @if ($carritoError)
                            <div class="mx-5 mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                                {{ $carritoError }}
                            </div>
                        @endif

                        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                            @forelse ($carritoDetalle as $linea)
                                <div class="flex items-center gap-3">
                                    @if ($linea->producto->foto_principal_path)
                                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($linea->producto->foto_principal_path) }}"
                                             class="h-14 w-14 object-cover rounded-lg border border-gray-200" alt="{{ $linea->producto->nombre }}">
                                    @else
                                        <div class="h-14 w-14 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center text-gray-200 text-xl">📦</div>
                                    @endif

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-800 truncate">{{ $linea->producto->nombre }}</p>
                                        <p class="text-xs text-gray-400">${{ number_format((float) $linea->producto->precio_usd, 2) }} c/u</p>

                                        <div class="mt-1 flex items-center gap-2">
                                            <button wire:click="actualizarCantidad({{ $linea->producto->id }}, {{ $linea->cantidad - 1 }})"
                                                    class="h-6 w-6 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50">−</button>
                                            <span class="text-sm w-5 text-center">{{ $linea->cantidad }}</span>
                                            <button wire:click="actualizarCantidad({{ $linea->producto->id }}, {{ $linea->cantidad + 1 }})"
                                                    class="h-6 w-6 rounded-md border border-gray-200 text-gray-500 hover:bg-gray-50">+</button>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-[#111111]">${{ number_format($linea->subtotal, 2) }}</p>
                                        <button wire:click="quitarDelCarrito({{ $linea->producto->id }})"
                                                class="text-xs text-red-500 hover:text-red-700">Quitar</button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 text-center py-10">Tu carrito está vacío.</p>
                            @endforelse
                        </div>

                        @if ($carritoDetalle->isNotEmpty())
                            <div class="border-t border-gray-200 px-5 py-4">
                                <div class="flex items-center justify-between text-sm mb-3">
                                    <span class="text-gray-500">Total</span>
                                    <span class="text-lg font-bold text-[#111111]">${{ number_format($carritoTotal, 2) }}</span>
                                </div>
                                <button wire:click="abrirCheckout"
                                        class="w-full bg-[#111111] hover:bg-[#2a2a2a] text-white font-semibold py-3 rounded-lg transition">
                                    Finalizar pedido
                                </button>
                                <button wire:click="vaciarCarrito" class="w-full mt-2 text-xs text-gray-400 hover:text-red-600">
                                    Vaciar carrito
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- =========================================================
                 CONFLICTO: producto de otra tienda
            ========================================================== --}}
            @if ($conflictoProducto)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div class="max-w-sm w-full bg-white rounded-2xl p-6 text-center">
                        <p class="text-sm text-gray-700">
                            Tu carrito tiene productos de otra tienda. Un pedido solo puede tener productos de un mismo
                            emprendedor — ¿vaciarlo y agregar <strong>"{{ $conflictoProducto->nombre }}"</strong>?
                        </p>
                        <div class="mt-5 flex gap-3">
                            <button wire:click="cancelarConflicto"
                                    class="flex-1 rounded-lg border border-gray-200 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button wire:click="vaciarYAgregar({{ $conflictoProducto->id }})"
                                    class="flex-1 rounded-lg bg-[#111111] hover:bg-[#2a2a2a] text-white py-2.5 text-sm font-semibold">
                                Vaciar y agregar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =========================================================
                 CONFIRMACIÓN DE PEDIDO
            ========================================================== --}}
            @if ($pedidoCreado)

                <div class="max-w-xl mx-auto mb-10 rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
                    <p class="text-emerald-700 font-semibold text-lg">¡Pedido registrado!</p>
                    <p class="text-emerald-700 text-sm mt-2">
                        Contacta a <strong>{{ $pedidoCreado->emprendedor->business_name }}</strong>
                        @if ($pedidoCreado->emprendedor->user?->phone)
                            al <strong>{{ $pedidoCreado->emprendedor->user->phone }}</strong>
                        @endif
                        para acordar el pago de
                        <strong>${{ number_format((float) $pedidoCreado->precio_total_usd, 2) }}</strong>
                        @if ($pedidoCreado->precio_total_ves !== null)
                            (Bs. {{ number_format($pedidoCreado->precio_total_ves, 2) }})
                        @endif
                        por
                        @foreach ($pedidoCreado->items as $item)
                            {{ $item->cantidad }} unidad(es) de "{{ $item->producto?->nombre }}"@if (! $loop->last), @endif
                        @endforeach
                        . Una vez pagues, el emprendedor confirmará tu pedido y te llegará la guía de envío.
                    </p>

                    <div class="mt-4 flex items-center justify-center gap-4">
                        <a href="{{ route('public.marketplace.pedido', $pedidoCreado->chat_token) }}"
                           class="text-sm font-semibold text-[#111111] bg-amber-400 hover:bg-amber-500 px-4 py-2 rounded-lg transition">
                            Chatear con el vendedor
                        </a>
                        <button wire:click="$set('pedidoCreado', null)" class="text-sm font-semibold text-emerald-800 underline">
                            Seguir comprando
                        </button>
                    </div>

                    <p class="mt-3 text-xs text-emerald-600">
                        Guarda este enlace: es la única forma de volver a esta conversación.
                    </p>
                </div>

            @endif

            {{-- =========================================================
                 DETALLE DEL PRODUCTO
            ========================================================== --}}
            @if ($productoViendo && ! $pedidoCreado)

                <div class="max-w-2xl mx-auto mb-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h2 class="text-lg font-bold text-[#111111]">{{ $productoViendo->nombre }}</h2>
                        <button wire:click="cerrarDetalle" class="text-sm text-gray-400 hover:text-gray-700">✕</button>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            @if ($productoViendo->foto_principal_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($productoViendo->foto_principal_path) }}"
                                     class="w-full h-56 object-cover rounded-xl" alt="{{ $productoViendo->nombre }}">
                            @else
                                <div class="w-full h-56 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-200 text-6xl">📦</div>
                            @endif

                            @if ($productoViendo->fotos->count() > 1)
                                <div class="mt-2 flex gap-2 overflow-x-auto">
                                    @foreach ($productoViendo->fotos as $foto)
                                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($foto->path) }}"
                                             class="h-14 w-14 object-cover rounded-lg border border-gray-200 shrink-0" alt="{{ $productoViendo->nombre }}">
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div>
                            <a href="{{ route('public.marketplace.store', $productoViendo->emprendedor_id) }}" wire:navigate
                               class="text-xs font-semibold text-gray-500 hover:text-[#111111]">
                                Vendido por {{ $productoViendo->emprendedor->business_name }} — ver tienda
                            </a>

                            @if ($productoViendo->categoria)
                                <p class="mt-1 text-xs text-gray-400">{{ $productoViendo->categoria->nombre }}</p>
                            @endif

                            <div class="mt-2">
                                <x-star-rating :rating="$productoViendo->resenas_avg_estrellas" :count="$productoViendo->resenas_count" />
                            </div>

                            @if ($productoViendo->descripcion)
                                <p class="text-sm text-gray-600 mt-3">{{ $productoViendo->descripcion }}</p>
                            @endif

                            <p class="text-2xl font-extrabold text-[#111111] mt-4">
                                ${{ number_format((float) $productoViendo->precio_usd, 2) }}
                            </p>
                            @if ($productoViendo->precio_ves !== null)
                                <p class="text-sm text-gray-500">Bs. {{ number_format($productoViendo->precio_ves, 2) }}</p>
                            @endif

                            <p class="text-xs text-gray-400 mt-2">Disponibles: {{ $productoViendo->stock }}</p>

                            <button wire:click="agregarAlCarrito({{ $productoViendo->id }})"
                                    class="mt-4 w-full bg-amber-400 hover:bg-amber-500 text-[#111111] text-sm font-semibold py-2.5 rounded-lg transition">
                                Agregar al carrito
                            </button>
                        </div>
                    </div>
                </div>

            @endif

            {{-- =========================================================
                 FORMULARIO DE CHECKOUT (datos de entrega)
            ========================================================== --}}
            @if ($showCheckout && ! $pedidoCreado)

                <div class="max-w-xl mx-auto mb-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">

                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-[#111111]">Finalizar pedido</h2>
                        <button wire:click="cancelarCheckout" class="text-sm text-gray-400 hover:text-gray-700">✕</button>
                    </div>

                    <div class="mb-4 rounded-lg bg-white border border-gray-200 divide-y divide-gray-100">
                        @foreach ($carritoDetalle as $linea)
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span class="text-gray-700">{{ $linea->cantidad }} × {{ $linea->producto->nombre }}</span>
                                <span class="font-semibold text-[#111111]">${{ number_format($linea->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm font-bold">
                            <span class="text-[#111111]">Total</span>
                            <span class="text-[#111111]">${{ number_format($carritoTotal, 2) }}</span>
                        </div>
                    </div>

                    <form wire:submit.prevent="confirmarPedido" class="space-y-4">

                        @if ($clienteAutenticadoConDatos)
                            <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                                Pedido a nombre de <strong class="text-[#111111]">{{ $cliente_nombre }}</strong>
                                ({{ $cliente_id_doc }}) · {{ $cliente_telefono }}
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tu nombre</label>
                                <input type="text" wire:model="cliente_nombre"
                                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                                @error('cliente_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Cédula</label>
                                    <input type="text" wire:model="cliente_id_doc"
                                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                                    @error('cliente_id_doc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Teléfono</label>
                                    <input type="text" wire:model="cliente_telefono"
                                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                                    @error('cliente_telefono') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Correo (opcional)</label>
                            <input type="email" wire:model="cliente_email" placeholder="para avisarte del estado de tu pedido"
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                            @error('cliente_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Estado de destino</label>
                                <select wire:model.live="destino_estado"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                                    <option value="">Selecciona...</option>
                                    @foreach ($states as $stateOption)
                                        <option value="{{ $stateOption }}">{{ $stateOption }}</option>
                                    @endforeach
                                </select>
                                @error('destino_estado') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Ciudad de destino</label>
                                <select wire:model="destino_ciudad" @disabled($destino_estado === '')
                                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400 disabled:bg-gray-100">
                                    <option value="">Selecciona...</option>
                                    @foreach ($cities as $cityOption)
                                        <option value="{{ $cityOption }}">{{ $cityOption }}</option>
                                    @endforeach
                                </select>
                                @error('destino_ciudad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Dirección exacta de entrega</label>
                            <textarea wire:model="direccion_entrega" rows="2" placeholder="Calle, casa/edificio, urbanización..."
                                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400"></textarea>
                            @error('direccion_entrega') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Punto de referencia (opcional)</label>
                            <input type="text" wire:model="referencia_entrega" placeholder="Cerca de..."
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-amber-400 focus:border-amber-400">
                            @error('referencia_entrega') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if ($carritoError)
                            <p class="text-xs text-red-600">{{ $carritoError }}</p>
                        @endif

                        <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                            <span class="text-xs text-gray-400">El envío se coordina con el emprendedor una vez confirme tu pago.</span>
                        </div>

                        <button type="submit" class="w-full bg-[#111111] hover:bg-[#2a2a2a] text-white font-semibold py-3 rounded-lg transition">
                            Registrar pedido
                        </button>

                    </form>

                </div>

            @endif

            {{-- =========================================================
                 CATÁLOGO — sidebar de categorías (escritorio) + grilla
            ========================================================== --}}
            <div class="lg:grid lg:grid-cols-[220px_1fr] lg:gap-8">

                {{-- SIDEBAR — solo escritorio --}}
                <aside class="hidden lg:block">
                    <p class="px-3 mb-2 text-xs font-bold uppercase tracking-wide text-gray-400">
                        Categorías
                    </p>
                    @include('public.partials.marketplace-categories', ['categorias' => $categorias, 'categoriaId' => $categoriaId])
                </aside>

                <div class="min-w-0">

                    {{-- Filtro de categorías — solo móvil/tablet (drawer) --}}
                    <div class="lg:hidden mb-5" x-data="{ open: false }">
                        <button type="button" @click="open = true"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-[#111111]">
                            <i class="fa-solid fa-sliders text-xs text-gray-400"></i>
                            Categorías
                        </button>

                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center sm:justify-center"
                             @keydown.escape.window="open = false">
                            <div class="absolute inset-0 bg-black/30" @click="open = false"></div>
                            <div class="relative w-full sm:max-w-sm bg-white rounded-t-2xl sm:rounded-2xl max-h-[80vh] flex flex-col">
                                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                                    <p class="text-sm font-bold text-[#111111]">Categorías</p>
                                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-700">✕</button>
                                </div>
                                <div class="overflow-y-auto px-3 py-3" @click="open = false">
                                    @include('public.partials.marketplace-categories', ['categorias' => $categorias, 'categoriaId' => $categoriaId])
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Encabezado de resultados --}}
                    @php
                        $categoriaActiva = $categorias->firstWhere('id', (int) $categoriaId);
                    @endphp
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-sm font-semibold text-gray-500">
                            @if ($categoriaActiva)
                                {{ $categoriaActiva->nombre }}
                            @elseif (trim($busqueda) !== '')
                                Resultados para "{{ trim($busqueda) }}"
                            @else
                                Todos los productos
                            @endif
                        </h2>

                        @if ($categoriaActiva)
                            <button type="button" wire:click="$set('categoriaId', '')" class="text-xs font-semibold text-gray-400 hover:text-[#111111]">
                                Quitar filtro
                            </button>
                        @endif
                    </div>

                    {{-- GRILLA DE PRODUCTOS --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">

                        @forelse ($productos as $producto)

                            <div class="group flex flex-col rounded-xl border border-gray-100 bg-white hover:border-gray-200 transition-colors">

                                <button wire:click="verProducto({{ $producto->id }})" class="block w-full aspect-square p-4 bg-white">
                                    @if ($producto->foto_principal_path)
                                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->foto_principal_path) }}"
                                             class="w-full h-full object-contain group-hover:scale-105 transition-transform" alt="{{ $producto->nombre }}">
                                    @else
                                        <div class="w-full h-full bg-gray-50 rounded flex items-center justify-center text-gray-200 text-5xl">📦</div>
                                    @endif
                                </button>

                                <div class="flex flex-col flex-1 px-4 pb-4">
                                    @if (! $tiendaEmprendedor)
                                        <a href="{{ route('public.marketplace.store', $producto->emprendedor_id) }}" wire:navigate
                                           class="text-xs text-gray-400 hover:text-[#111111] truncate">
                                            {{ $producto->emprendedor->business_name }}
                                        </a>
                                    @endif

                                    <button wire:click="verProducto({{ $producto->id }})" class="block text-left w-full mt-0.5">
                                        <p class="text-sm text-gray-800 line-clamp-2 leading-snug">{{ $producto->nombre }}</p>
                                    </button>

                                    <div class="mt-1.5">
                                        <x-star-rating :rating="$producto->resenas_avg_estrellas" :count="$producto->resenas_count" size="text-xs" />
                                    </div>

                                    <div class="mt-2">
                                        <p class="text-xl font-bold text-gray-900">${{ number_format((float) $producto->precio_usd, 2) }}</p>
                                        @if ($producto->precio_ves !== null)
                                            <p class="text-xs text-gray-400">Bs. {{ number_format($producto->precio_ves, 2) }}</p>
                                        @endif
                                    </div>

                                    <p class="text-xs text-gray-400 font-medium mt-1.5">
                                        <i class="fa-solid fa-box text-[0.65rem]"></i>
                                        Envío por Venexpress
                                    </p>

                                    <button wire:click="agregarAlCarrito({{ $producto->id }})"
                                            class="mt-auto pt-3 w-full bg-amber-400 hover:bg-amber-500 text-[#111111] text-sm font-semibold py-2.5 rounded-lg transition">
                                        Agregar al carrito
                                    </button>
                                </div>

                            </div>

                        @empty

                            <div class="col-span-full text-center py-16 text-gray-400">
                                @if (trim($busqueda) !== '' || $categoriaId !== '')
                                    No encontramos productos con esos filtros.
                                @else
                                    Todavía no hay productos publicados. Vuelve pronto.
                                @endif
                            </div>

                        @endforelse

                    </div>

                    <div class="mt-10">
                        {{ $productos->links() }}
                    </div>

                </div>

            </div>

        </div>
    </section>

</div>
