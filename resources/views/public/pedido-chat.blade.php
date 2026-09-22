@php
    use App\Models\MensajePedido;
    use App\Models\Pedido;
@endphp

<div>
    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-6 py-14">

            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full">
                        Tu pedido
                    </span>
                    <span class="text-xs font-semibold text-gray-400">
                        Pedido #{{ $pedido->id }}
                    </span>
                </div>
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

                {{-- =========================================================
                     RESEÑA DEL PRODUCTO
                ========================================================== --}}
                @if ($resenaMensaje)
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ $resenaMensaje }}
                    </div>
                @endif

                @if ($pedido->resena)
                    <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <p class="text-sm font-semibold text-blue-950 mb-2">Tu reseña</p>
                        <div class="flex items-center gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="text-lg {{ $i <= $pedido->resena->estrellas ? 'text-amber-400' : 'text-gray-200' }}">★</span>
                            @endfor
                        </div>
                        @if ($pedido->resena->comentario)
                            <p class="text-sm text-gray-600 mt-2">{{ $pedido->resena->comentario }}</p>
                        @endif
                    </div>
                @else
                    <div class="mb-6 rounded-2xl border border-gray-200 bg-gray-50 p-5" x-data="{ hover: 0 }">
                        <p class="text-sm font-semibold text-blue-950 mb-2">Califica tu compra</p>

                        <div class="flex items-center gap-1 mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button"
                                        wire:click="$set('estrellas', {{ $i }})"
                                        @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0"
                                        :class="(hover || {{ $estrellas }}) >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                                        class="text-3xl leading-none transition-colors">
                                    ★
                                </button>
                            @endfor
                        </div>
                        @error('estrellas') <p class="text-xs text-red-600 mb-2">{{ $message }}</p> @enderror

                        <textarea wire:model="comentario" rows="3" placeholder="¿Qué te pareció el producto? (opcional)"
                                  class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950"></textarea>
                        @error('comentario') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        <button wire:click="enviarResena" class="mt-3 text-sm font-semibold text-white bg-blue-950 hover:bg-blue-900 px-4 py-2 rounded-lg transition">
                            Enviar reseña
                        </button>
                    </div>
                @endif

            @endif

            {{-- =========================================================
                 CONVERSACIÓN
            ========================================================== --}}
            <div class="border border-gray-200 rounded-2xl bg-white overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <p class="text-sm font-semibold text-blue-950">Chat con {{ $pedido->emprendedor->business_name }}</p>
                </div>

                <div class="p-5 space-y-4 max-h-96 overflow-y-auto" x-ref="hilo" x-init="$refs.hilo.scrollTop = $refs.hilo.scrollHeight">
                    @forelse ($mensajes as $mensaje)
                        @php $esCliente = $mensaje->autor === MensajePedido::AUTOR_CLIENTE; @endphp
                        <div class="flex items-end gap-2 {{ $esCliente ? 'flex-row-reverse' : 'flex-row' }}">
                            <div class="shrink-0 h-7 w-7 rounded-full flex items-center justify-center text-[11px] font-bold
                                {{ $esCliente ? 'bg-amber-400 text-[#111111]' : 'bg-blue-950 text-white' }}">
                                {{ $esCliente ? 'T' : strtoupper(substr($pedido->emprendedor->business_name, 0, 1)) }}
                            </div>

                            <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm
                                {{ $esCliente ? 'bg-blue-950 text-white rounded-br-sm' : 'bg-gray-100 text-gray-800 rounded-bl-sm' }}">

                                @if ($mensaje->archivo_path)
                                    @if ($mensaje->esImagen())
                                        <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}"
                                             @click="$store.lightbox.open('{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}')"
                                             class="rounded-lg max-h-48 object-cover mb-1.5 cursor-pointer" alt="Adjunto">
                                    @else
                                        <a href="{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}" target="_blank"
                                           class="flex items-center gap-2 rounded-lg px-3 py-2 mb-1.5
                                               {{ $esCliente ? 'bg-blue-900' : 'bg-white border border-gray-200' }}">
                                            <span>📎</span>
                                            <span class="text-xs underline truncate">{{ $mensaje->archivo_nombre ?? 'Archivo adjunto' }}</span>
                                        </a>
                                    @endif
                                @endif

                                @if ($mensaje->texto !== '')
                                    <p>{{ $mensaje->texto }}</p>
                                @endif

                                <p class="mt-1 text-[10px] opacity-60">{{ $mensaje->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-6">
                            Todavía no hay mensajes. Escríbele al vendedor para coordinar el pago.
                        </p>
                    @endforelse
                </div>

                <form wire:submit.prevent="enviarMensaje" class="border-t border-gray-100 p-3">
                    @if ($archivo)
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 mb-2 text-xs text-gray-600">
                            <span>📎</span>
                            <span class="truncate flex-1">{{ $archivo->getClientOriginalName() }}</span>
                            <button type="button" wire:click="$set('archivo', null)" class="text-gray-400 hover:text-gray-700">✕</button>
                        </div>
                    @endif
                    @error('archivo') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="flex items-end gap-2">
                        <label class="shrink-0 cursor-pointer text-gray-400 hover:text-blue-950 p-2" title="Adjuntar comprobante de pago u otro archivo">
                            <input type="file" wire:model="archivo" class="hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                        </label>

                        <textarea wire:model="texto" rows="1" placeholder="Escribe un mensaje..."
                                  class="flex-1 rounded-xl border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950 resize-none"></textarea>

                        <button type="submit" class="shrink-0 bg-blue-950 hover:bg-blue-900 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                            Enviar
                        </button>
                    </div>
                    @error('texto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </form>
            </div>

        </div>
    </section>
</div>
