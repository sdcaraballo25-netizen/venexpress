<div>
    <section class="bg-white">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-10 py-10">

            @if ($tiendaEmprendedor)

                {{-- =========================================================
                     PERFIL DE LA TIENDA — información de confianza para
                     que el comprador vea que es un negocio real antes de
                     pagarle por fuera de la plataforma.
                ========================================================== --}}
                <div class="mb-10">
                    <a href="{{ route('public.marketplace') }}" wire:navigate class="text-xs font-semibold text-blue-700 hover:text-blue-950">
                        ← Ver toda la tienda
                    </a>

                    <div class="mt-3 rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="relative h-40 sm:h-48 bg-gradient-to-br from-blue-950 to-blue-900">
                            @if ($tiendaEmprendedor->cover_photo_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($tiendaEmprendedor->cover_photo_path) }}"
                                     class="w-full h-full object-cover" alt="Portada de {{ $tiendaEmprendedor->business_name }}">
                            @endif

                            <div class="absolute -bottom-10 left-5 h-20 w-20 rounded-2xl bg-white border-4 border-white shadow-md overflow-hidden">
                                @if ($tiendaEmprendedor->logo_path)
                                    <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($tiendaEmprendedor->logo_path) }}"
                                         class="w-full h-full object-cover" alt="Logo de {{ $tiendaEmprendedor->business_name }}">
                                @else
                                    <div class="w-full h-full bg-amber-100 flex items-center justify-center text-amber-700 text-2xl font-bold">
                                        {{ strtoupper(substr($tiendaEmprendedor->business_name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white pt-12 px-5 pb-5 text-left">
                            <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-2">
                                Tienda verificada por Venexpress
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-blue-950">{{ $tiendaEmprendedor->business_name }}</h1>

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
                <div class="mb-8 flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-3">
                            Marketplace
                        </span>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-blue-950">Tienda de Emprendedores</h1>
                        <p class="text-gray-500 mt-2 max-w-2xl">
                            Productos de emprendedores venezolanos, con envío por Venexpress. Tú acuerdas el pago
                            directamente con el emprendedor.
                        </p>
                    </div>
                </div>
            @endif

            {{-- =========================================================
                 CARRITO FLOTANTE
            ========================================================== --}}
            <button wire:click="toggleCarrito"
                    class="fixed bottom-6 right-6 z-40 flex items-center gap-2 bg-blue-950 hover:bg-blue-900 text-white font-semibold px-5 py-3.5 rounded-full shadow-lg transition">
                🛒 Carrito
                @if ($carritoCount > 0)
                    <span class="inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-amber-400 text-[#111111] text-xs font-bold">
                        {{ $carritoCount }}
                    </span>
                @endif
            </button>

            @if ($showCarrito)
                <div class="fixed inset-0 z-50 flex items-start justify-end" wire:click.self="toggleCarrito">
                    <div class="absolute inset-0 bg-black/30"></div>
                    <div class="relative h-full w-full max-w-md bg-white shadow-xl flex flex-col">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-bold text-blue-950">Tu carrito</h2>
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
                                        <p class="text-sm font-semibold text-blue-950">${{ number_format($linea->subtotal, 2) }}</p>
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
                                    <span class="text-lg font-bold text-blue-950">${{ number_format($carritoTotal, 2) }}</span>
                                </div>
                                <button wire:click="abrirCheckout"
                                        class="w-full bg-blue-950 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg transition">
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
                                    class="flex-1 rounded-lg bg-blue-950 hover:bg-blue-900 text-white py-2.5 text-sm font-semibold">
                                Vaciar y agregar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- =========================================================
                 BÚSQUEDA + CATEGORÍA
            ========================================================== --}}
            <div class="mb-10 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1 max-w-2xl">
                    <input type="search" wire:model.live.debounce.400ms="busqueda"
                           placeholder="Buscar productos, marcas y más..."
                           class="w-full rounded-xl border border-gray-300 pl-5 pr-14 py-3.5 text-sm shadow-sm focus:ring-2 focus:ring-blue-950 focus:border-blue-950">
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 flex items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                        </svg>
                    </div>
                </div>

                <select wire:model.live="categoriaId"
                        class="rounded-xl border border-gray-300 text-sm shadow-sm focus:ring-2 focus:ring-blue-950 focus:border-blue-950 sm:w-56">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

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
                           class="text-sm font-semibold text-white bg-blue-950 hover:bg-blue-900 px-4 py-2 rounded-lg transition">
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
                        <h2 class="text-lg font-bold text-blue-950">{{ $productoViendo->nombre }}</h2>
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
                               class="text-xs font-semibold text-blue-700 hover:text-blue-950">
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

                            <p class="text-2xl font-extrabold text-blue-950 mt-4">
                                ${{ number_format((float) $productoViendo->precio_usd, 2) }}
                            </p>
                            @if ($productoViendo->precio_ves !== null)
                                <p class="text-sm text-gray-500">Bs. {{ number_format($productoViendo->precio_ves, 2) }}</p>
                            @endif

                            <p class="text-xs text-gray-400 mt-2">Disponibles: {{ $productoViendo->stock }}</p>

                            <button wire:click="agregarAlCarrito({{ $productoViendo->id }})"
                                    class="mt-4 w-full bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold py-2.5 rounded-lg transition">
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
                        <h2 class="text-lg font-bold text-blue-950">Finalizar pedido</h2>
                        <button wire:click="cancelarCheckout" class="text-sm text-gray-400 hover:text-gray-700">✕</button>
                    </div>

                    <div class="mb-4 rounded-lg bg-white border border-gray-200 divide-y divide-gray-100">
                        @foreach ($carritoDetalle as $linea)
                            <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                                <span class="text-gray-700">{{ $linea->cantidad }} × {{ $linea->producto->nombre }}</span>
                                <span class="font-semibold text-blue-950">${{ number_format($linea->subtotal, 2) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between px-4 py-2.5 text-sm font-bold">
                            <span class="text-blue-950">Total</span>
                            <span class="text-blue-950">${{ number_format($carritoTotal, 2) }}</span>
                        </div>
                    </div>

                    <form wire:submit.prevent="confirmarPedido" class="space-y-4">

                        @if ($clienteAutenticadoConDatos)
                            <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                                Pedido a nombre de <strong class="text-blue-950">{{ $cliente_nombre }}</strong>
                                ({{ $cliente_id_doc }}) · {{ $cliente_telefono }}
                            </div>
                        @else
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tu nombre</label>
                                <input type="text" wire:model="cliente_nombre"
                                       class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                @error('cliente_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Cédula</label>
                                    <input type="text" wire:model="cliente_id_doc"
                                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                    @error('cliente_id_doc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Teléfono</label>
                                    <input type="text" wire:model="cliente_telefono"
                                           class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                    @error('cliente_telefono') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Correo (opcional)</label>
                            <input type="email" wire:model="cliente_email" placeholder="para avisarte del estado de tu pedido"
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                            @error('cliente_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Estado de destino</label>
                                <select wire:model.live="destino_estado"
                                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
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
                                        class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950 disabled:bg-gray-100">
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
                                      class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950"></textarea>
                            @error('direccion_entrega') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Punto de referencia (opcional)</label>
                            <input type="text" wire:model="referencia_entrega" placeholder="Cerca de..."
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                            @error('referencia_entrega') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @if ($carritoError)
                            <p class="text-xs text-red-600">{{ $carritoError }}</p>
                        @endif

                        <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                            <span class="text-xs text-gray-400">El envío se coordina con el emprendedor una vez confirme tu pago.</span>
                        </div>

                        <button type="submit" class="w-full bg-blue-950 hover:bg-blue-900 text-white font-semibold py-3 rounded-lg transition">
                            Registrar pedido
                        </button>

                    </form>

                </div>

            @endif

            {{-- =========================================================
                 CATÁLOGO
            ========================================================== --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 sm:gap-5">

                @forelse ($productos as $producto)

                    <div class="group flex flex-col border border-gray-200 rounded-lg bg-white hover:shadow-lg hover:border-gray-300 transition-all">

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
                                   class="text-xs text-gray-400 hover:text-blue-700 truncate">
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

                            <p class="text-xs text-emerald-700 font-medium mt-1.5">
                                📦 Envío por Venexpress
                            </p>

                            <button wire:click="agregarAlCarrito({{ $producto->id }})"
                                    class="mt-auto pt-3 w-full bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold py-2.5 rounded-lg transition">
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
    </section>
</div>
