@php
    use App\Models\MensajePedido;
    use App\Models\Pedido;
@endphp

<div class="space-y-6 font-sans max-w-5xl">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('emprendedor.pedidos') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                ← Volver a Pedidos
            </a>
            <p class="text-xs font-semibold text-[#6B6B66] mt-1">Pedido #{{ $pedido->id }}</p>
            <h1 class="font-display text-2xl font-bold tracking-tight text-[#111111]">
                {{ $pedido->resumen_items }}
            </h1>
        </div>

        <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
            {{ $pedido->status === Pedido::STATUS_CONFIRMADO ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
            {{ $pedido->status }}
        </span>
    </div>

    @if ($successMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ $successMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ $errorMessage }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-[#E5E5E0] p-5 shadow-sm">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-[#6B6B66]">Cliente</p>
                <p class="font-medium text-[#111111]">{{ $pedido->cliente_nombre }}</p>
                <p class="text-xs text-[#6B6B66]">{{ $pedido->cliente_id_doc }} · {{ $pedido->cliente_telefono }}</p>
            </div>
            <div>
                <p class="text-xs text-[#6B6B66]">Destino</p>
                <p class="font-medium text-[#111111]">{{ $pedido->destino_ciudad }}, {{ $pedido->destino_estado }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-[#6B6B66]">Productos</p>
                <ul class="mt-1 space-y-0.5">
                    @foreach ($pedido->items as $item)
                        <li class="font-medium text-[#111111]">
                            {{ $item->cantidad }} × {{ $item->producto?->nombre }}
                            <span class="text-[#6B6B66] font-normal">(${{ number_format((float) $item->subtotal_usd, 2) }})</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-xs text-[#6B6B66]">Total</p>
                <p class="font-medium text-[#111111]">${{ number_format((float) $pedido->precio_total_usd, 2) }}</p>
            </div>
        </div>

        @if ($pedido->package)
            <p class="mt-4 text-sm text-[#6B6B66]">
                Guía generada: <strong class="text-[#111111]">{{ $pedido->package->tracking_number }}</strong>
            </p>
        @elseif ($pedido->status === Pedido::STATUS_PAGADO)
            <p class="mt-4 text-sm text-[#6B6B66]">
                Pago confirmado. Lleva el paquete a tu agencia aliada para que generen la guía.
            </p>
        @elseif ($pedido->status === Pedido::STATUS_PENDIENTE)
            <button
                @click.prevent="$store.confirm.open({
                    message: '¿Ya recibiste el pago de este cliente? Después de confirmar, lleva el paquete a tu agencia aliada para generar la guía.',
                    confirmText: 'Confirmar pedido',
                    variant: 'primary',
                    onConfirm: () => $wire.confirmar(),
                })"
                class="mt-4 px-4 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold transition"
            >
                Confirmar pedido
            </button>
        @endif

        @if ($pedido->resena)
            <div class="mt-4 pt-4 border-t border-[#E5E5E0]">
                <p class="text-xs text-[#6B6B66] mb-1">Reseña del cliente</p>
                <div class="flex items-center gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="text-base {{ $i <= $pedido->resena->estrellas ? 'text-amber-400' : 'text-gray-200' }}">★</span>
                    @endfor
                </div>
                @if ($pedido->resena->comentario)
                    <p class="text-sm text-[#4A4A45] mt-1">{{ $pedido->resena->comentario }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- =========================================================
         CONVERSACIÓN
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-[#E5E5E0] bg-slate-50">
            <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider">Chat con el cliente</p>
        </div>

        <div class="p-5 space-y-4 max-h-96 overflow-y-auto" x-ref="hilo" x-init="$refs.hilo.scrollTop = $refs.hilo.scrollHeight">
            @forelse ($mensajes as $mensaje)
                @php $esEmprendedor = $mensaje->autor === MensajePedido::AUTOR_EMPRENDEDOR; @endphp
                <div class="flex items-end gap-2 {{ $esEmprendedor ? 'flex-row-reverse' : 'flex-row' }}">
                    <div class="shrink-0 h-7 w-7 rounded-full flex items-center justify-center text-[11px] font-bold
                        {{ $esEmprendedor ? 'bg-blue-600 text-white' : 'bg-amber-400 text-[#111111]' }}">
                        {{ $esEmprendedor ? strtoupper(substr(auth()->user()->emprendedor->business_name, 0, 1)) : 'C' }}
                    </div>

                    <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm
                        {{ $esEmprendedor ? 'bg-blue-600 text-white rounded-br-sm' : 'bg-slate-100 text-[#111111] rounded-bl-sm' }}">

                        @if ($mensaje->archivo_path)
                            @if ($mensaje->esImagen())
                                <img src="{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}"
                                     @click="$store.lightbox.open('{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}')"
                                     class="rounded-lg max-h-48 object-cover mb-1.5 cursor-pointer" alt="Adjunto">
                            @else
                                <a href="{{ Illuminate\Support\Facades\Storage::disk('public')->url($mensaje->archivo_path) }}" target="_blank"
                                   class="flex items-center gap-2 rounded-lg px-3 py-2 mb-1.5
                                       {{ $esEmprendedor ? 'bg-blue-700' : 'bg-white border border-[#E5E5E0]' }}">
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
                <p class="text-sm text-[#6B6B66] text-center py-6">Todavía no hay mensajes.</p>
            @endforelse
        </div>

        <form wire:submit.prevent="enviarMensaje" class="border-t border-[#E5E5E0] p-3">
            @if ($archivo)
                <div class="flex items-center gap-2 bg-slate-50 border border-[#E5E5E0] rounded-lg px-3 py-2 mb-2 text-xs text-[#6B6B66]">
                    <span>📎</span>
                    <span class="truncate flex-1">{{ $archivo->getClientOriginalName() }}</span>
                    <button type="button" wire:click="$set('archivo', null)" class="text-[#6B6B66] hover:text-[#111111]">✕</button>
                </div>
            @endif
            @error('archivo') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="flex items-end gap-2">
                <label class="shrink-0 cursor-pointer text-[#6B6B66] hover:text-blue-600 p-2" title="Adjuntar archivo">
                    <input type="file" wire:model="archivo" class="hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                    </svg>
                </label>

                <textarea wire:model="texto" rows="1" placeholder="Escribe un mensaje..."
                          class="flex-1 rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>

                <button type="submit" class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                    Enviar
                </button>
            </div>
            @error('texto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </form>
    </div>

</div>
