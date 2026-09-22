@php
    use App\Models\MensajePedido;
    use App\Models\Pedido;
@endphp

<div>
    <section class="bg-white">
        <div class="max-w-2xl mx-auto px-6 py-14">

            <div class="mb-8">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Tu pedido
                </span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-blue-950">
                    {{ $pedido->producto->nombre }}
                </h1>
                <p class="text-gray-500 mt-2">
                    {{ $pedido->cantidad }} unidad(es) · ${{ number_format((float) $pedido->precio_total_usd, 2) }}
                    @if ($pedido->precio_total_ves !== null)
                        (Bs. {{ number_format($pedido->precio_total_ves, 2) }})
                    @endif
                    ·
                    Vendedor: <strong>{{ $pedido->emprendedor->business_name }}</strong>
                    @if ($pedido->emprendedor->user?->phone)
                        ({{ $pedido->emprendedor->user->phone }})
                    @endif
                </p>

                <span class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 rounded-lg text-xs font-semibold
                    {{ $pedido->status === Pedido::STATUS_CONFIRMADO ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $pedido->status === Pedido::STATUS_CONFIRMADO ? 'Confirmado' : 'Pendiente de pago' }}
                </span>

                @if ($pedido->package)
                    <p class="mt-2 text-sm text-gray-600">
                        Guía de envío: <strong>{{ $pedido->package->tracking_number }}</strong> —
                        <a href="{{ route('tracking.show', ['guia' => $pedido->package->tracking_number]) }}" class="text-blue-700 hover:text-blue-950 font-semibold">
                            rastrear
                        </a>
                    </p>
                @endif
            </div>

            {{-- =========================================================
                 REPORTAR PRODUCTO DEFECTUOSO (garantía)
            ========================================================== --}}
            @if ($pedido->status === Pedido::STATUS_CONFIRMADO && $pedido->package_id)

                @if ($garantiaMensaje)
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ $garantiaMensaje }}
                    </div>
                @endif

                @if ($showReportarProblema)
                    <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <p class="text-sm font-semibold text-blue-950 mb-2">¿Llegó defectuoso o dañado?</p>
                        <p class="text-xs text-gray-500 mb-3">
                            Solo aplica cambio, no devolución de dinero (eso lo acuerdas con el emprendedor).
                            Venexpress cubre el flete del cambio.
                        </p>
                        <textarea wire:model="problemaDescripcion" rows="3"
                                  placeholder="Cuéntanos qué le pasó al producto..."
                                  class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950"></textarea>
                        @error('problemaDescripcion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        <div class="mt-3 flex gap-3">
                            <button wire:click="reportarProblema" class="text-sm font-semibold text-white bg-blue-950 hover:bg-blue-900 px-4 py-2 rounded-lg transition">
                                Enviar reporte
                            </button>
                            <button wire:click="$set('showReportarProblema', false)" class="text-sm text-gray-500 hover:text-gray-800">
                                Cancelar
                            </button>
                        </div>
                    </div>
                @else
                    <button wire:click="$set('showReportarProblema', true)" class="mb-6 text-sm font-semibold text-red-600 hover:text-red-800 underline">
                        ¿Producto defectuoso o dañado? Reportarlo
                    </button>
                @endif

            @endif

            {{-- =========================================================
                 CONVERSACIÓN
            ========================================================== --}}
            <div class="border border-gray-100 rounded-2xl bg-gray-50 p-5 space-y-3 mb-6 max-h-96 overflow-y-auto">
                @forelse ($mensajes as $mensaje)
                    <div class="flex {{ $mensaje->autor === MensajePedido::AUTOR_CLIENTE ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm
                            {{ $mensaje->autor === MensajePedido::AUTOR_CLIENTE
                                ? 'bg-blue-950 text-white'
                                : 'bg-white border border-gray-200 text-gray-800' }}">
                            <p>{{ $mensaje->texto }}</p>
                            <p class="mt-1 text-[10px] opacity-60">{{ $mensaje->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">
                        Todavía no hay mensajes. Escríbele al vendedor para coordinar el pago.
                    </p>
                @endforelse
            </div>

            <form wire:submit.prevent="enviarMensaje" class="flex items-start gap-3">
                <div class="flex-1">
                    <textarea wire:model="texto" rows="2" placeholder="Escribe un mensaje..."
                              class="w-full rounded-xl border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950"></textarea>
                    @error('texto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="bg-blue-950 hover:bg-blue-900 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    Enviar
                </button>
            </form>

        </div>
    </section>
</div>
