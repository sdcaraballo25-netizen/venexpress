<div>
    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Marketplace
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">Tienda de Emprendedores</h1>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                    Productos de emprendedores venezolanos, con envío por Venexpress. Tú acuerdas el pago
                    directamente con el emprendedor.
                </p>
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
                        para acordar el pago de <strong>${{ number_format((float) $pedidoCreado->precio_total_usd, 2) }}</strong>
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
                            <label class="block text-xs font-medium text-gray-500 mb-1">Cantidad</label>
                            <input type="number" min="1" max="{{ $productoSeleccionado->stock }}" wire:model="cantidad"
                                   class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                            <p class="mt-1 text-xs text-gray-400">Disponibles: {{ $productoSeleccionado->stock }}</p>
                            @error('cantidad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-lg bg-white border border-gray-200 px-4 py-3 text-sm text-gray-600">
                            Total estimado: <strong class="text-blue-950">${{ number_format((float) $productoSeleccionado->precio_usd * max(1, (int) $cantidad), 2) }}</strong>
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                @forelse ($productos as $producto)

                    <div class="border border-gray-100 rounded-2xl overflow-hidden bg-white shadow-sm">

                        @if ($producto->foto_path)
                            <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($producto->foto_path) }}"
                                 class="h-40 w-full object-cover" alt="{{ $producto->nombre }}">
                        @else
                            <div class="h-40 w-full bg-gray-50 flex items-center justify-center text-gray-200 text-5xl">📦</div>
                        @endif

                        <div class="p-4">
                            <p class="text-xs text-gray-400">{{ $producto->emprendedor->business_name }}</p>
                            <p class="font-semibold text-blue-950 mt-0.5">{{ $producto->nombre }}</p>
                            <p class="text-lg font-extrabold text-blue-950 mt-1">${{ number_format((float) $producto->precio_usd, 2) }}</p>

                            <button wire:click="pedirProducto({{ $producto->id }})"
                                    class="mt-3 w-full bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                                Pedir
                            </button>
                        </div>

                    </div>

                @empty

                    <div class="sm:col-span-2 lg:col-span-4 text-center py-16 text-gray-400">
                        Todavía no hay productos publicados. Vuelve pronto.
                    </div>

                @endforelse

            </div>

            <div class="mt-10">
                {{ $productos->links() }}
            </div>

        </div>
    </section>
</div>
