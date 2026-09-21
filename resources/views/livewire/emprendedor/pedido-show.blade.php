@php
    use App\Models\MensajePedido;
    use App\Models\Pedido;
@endphp

<div class="space-y-6 font-sans max-w-3xl">

    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('emprendedor.pedidos') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-800">
                ← Volver a Pedidos
            </a>
            <h1 class="font-display text-2xl font-bold tracking-tight text-[#111111] mt-1">
                {{ $pedido->producto?->nombre }}
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
            <div>
                <p class="text-xs text-[#6B6B66]">Cantidad</p>
                <p class="font-medium text-[#111111]">{{ $pedido->cantidad }} unidad(es)</p>
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
        @elseif ($pedido->status === Pedido::STATUS_PENDIENTE)
            <button
                @click.prevent="$store.confirm.open({
                    message: '¿Ya recibiste el pago de este cliente? Al confirmar se genera la guía de envío.',
                    confirmText: 'Confirmar pedido',
                    variant: 'primary',
                    onConfirm: () => $wire.confirmar(),
                })"
                class="mt-4 px-4 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold transition"
            >
                Confirmar pedido
            </button>
        @endif
    </div>

    {{-- =========================================================
         CONVERSACIÓN
    ========================================================== --}}
    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm p-5">
        <p class="text-xs font-bold text-[#6B6B66] uppercase tracking-wider mb-4">Chat con el cliente</p>

        <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
            @forelse ($mensajes as $mensaje)
                <div class="flex {{ $mensaje->autor === MensajePedido::AUTOR_EMPRENDEDOR ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm
                        {{ $mensaje->autor === MensajePedido::AUTOR_EMPRENDEDOR
                            ? 'bg-blue-600 text-white'
                            : 'bg-slate-100 text-[#111111]' }}">
                        <p>{{ $mensaje->texto }}</p>
                        <p class="mt-1 text-[10px] opacity-60">{{ $mensaje->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-[#6B6B66] text-center py-6">Todavía no hay mensajes.</p>
            @endforelse
        </div>

        <form wire:submit.prevent="enviarMensaje" class="flex items-start gap-3">
            <div class="flex-1">
                <textarea wire:model="texto" rows="2" placeholder="Escribe un mensaje..."
                          class="w-full rounded-xl border-[#E5E5E0] text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                @error('texto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                Enviar
            </button>
        </form>
    </div>

</div>
