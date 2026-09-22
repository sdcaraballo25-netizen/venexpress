<div>
    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                @if ($tiendaEmprendedor)
                    <a href="{{ route('public.marketplace') }}" wire:navigate class="text-xs font-semibold text-blue-700 hover:text-blue-950">
                        ← Ver toda la tienda
                    </a>
                    <span class="block mt-3 inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                        Tienda del emprendedor
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">{{ $tiendaEmprendedor->business_name }}</h1>
                    <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                        Productos de este emprendedor, con envío por Venexpress. Tú acuerdas el pago directamente con él.
                    </p>
                @else
                    <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                        Marketplace
                    </span>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">Tienda de Emprendedores</h1>
                    <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                        Productos de emprendedores venezolanos, con envío por Venexpress. Tú acuerdas el pago
                        directamente con el emprendedor.
                    </p>
                @endif
            </div>

            {{-- =========================================================
                 BÚSQUEDA
            ========================================================== --}}
            <div class="max-w-xl mx-auto mb-10">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" />
                    </svg>
                    <input type="search" wire:model.live.debounce.400ms="busqueda"
                           placeholder="Buscar productos por nombre o descripción..."
                           class="w-full rounded-xl border-gray-200 pl-11 pr-4 py-3 text-sm focus:ring-blue-950 focus:border-blue-950">
                </div>
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
                        por {{ $pedidoCreado->cantidad }} unidad(es) de "{{ $pedidoCreado->producto->nombre }}".
                        Una vez pagues, el emprendedor confirmará tu pedido y te llegará la guía de envío.
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
            @if ($productoViendo && ! $productoSeleccionado && ! $pedidoCreado)

                <div class="max-w-2xl mx-auto mb-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h2 class="text-lg font-bold text-blue-950">{{ $productoViendo->nombre }}</h2>
                        <button wire:click="cerrarDetalle" class="text-sm text-gray-400 hover:text-gray-700">✕</button>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        @if ($productoViendo->foto_path)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($productoViendo->foto_path) }}"
                                 class="w-full h-56 object-cover rounded-xl" alt="{{ $productoViendo->nombre }}">
                        @else
                            <div class="w-full h-56 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-200 text-6xl">📦</div>
                        @endif

                        <div>
                            <a href="{{ route('public.marketplace.store', $productoViendo->emprendedor_id) }}" wire:navigate
                               class="text-xs font-semibold text-blue-700 hover:text-blue-950">
                                Vendido por {{ $productoViendo->emprendedor->business_name }} — ver tienda
                            </a>

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

                            <button wire:click="pedirProducto({{ $productoViendo->id }})"
                                    class="mt-4 w-full bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                                Pedir
                            </button>
                        </div>
                    </div>
                </div>

            @endif

            {{-- =========================================================
                 FORMULARIO DE PEDIDO (producto seleccionado)
            ========================================================== --}}
            @if ($productoSeleccionado && ! $pedidoCreado)

                <div class="max-w-xl mx-auto mb-10 rounded-2xl border border-gray-200 bg-gray-50 p-6">

                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-blue-950">
                            Pedir "{{ $productoSeleccionado->nombre }}"
                        </h2>
                        <button wire:click="cancelarPedido" class="text-sm text-gray-400 hover:text-gray-700">✕</button>
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

                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Cantidad</label>
                            <input type="number" min="1" max="{{ $productoSeleccionado->stock }}" wire:model.live="cantidad"
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                            <p class="mt-1 text-xs text-gray-400">Disponibles: {{ $productoSeleccionado->stock }}</p>
                            @error('cantidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        @php
                            $totalEstimadoUsd = (float) $productoSeleccionado->precio_usd * max(1, (int) $cantidad);
                            $totalEstimadoVes = $productoSeleccionado->precio_ves !== null
                                ? $productoSeleccionado->precio_ves * max(1, (int) $cantidad)
                                : null;
                        @endphp
                        <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                            Total estimado: <strong class="text-blue-950">${{ number_format($totalEstimadoUsd, 2) }}</strong>
                            @if ($totalEstimadoVes !== null)
                                <span class="text-gray-400">(Bs. {{ number_format($totalEstimadoVes, 2) }})</span>
                            @endif
                            <br>
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
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">

                @forelse ($productos as $producto)

                    <div class="group flex flex-col border border-gray-200 rounded-lg bg-white hover:shadow-lg hover:border-gray-300 transition-all">

                        <button wire:click="verProducto({{ $producto->id }})" class="block w-full aspect-square p-4 bg-white">
                            @if ($producto->foto_path)
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->foto_path) }}"
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

                            <button wire:click="pedirProducto({{ $producto->id }})"
                                    class="mt-auto pt-3 w-full bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                                Pedir
                            </button>
                        </div>

                    </div>

                @empty

                    <div class="col-span-full text-center py-16 text-gray-400">
                        @if (trim($busqueda) !== '')
                            No encontramos productos para "{{ $busqueda }}".
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
