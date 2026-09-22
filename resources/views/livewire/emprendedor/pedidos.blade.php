@php
    use App\Models\Pedido;
@endphp

<div class="space-y-6 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Pedidos</h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            Confirma un pedido solo después de que el cliente te haya pagado por fuera de la plataforma.
        </p>
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

    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-[#E5E5E0]">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">Producto</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">Destino</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-[#6B6B66] uppercase">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr class="border-b border-[#F0F0EC] last:border-0 hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-[#111111]">{{ $pedido->producto?->nombre }}</p>
                                <p class="text-xs text-[#6B6B66]">{{ $pedido->cantidad }} unidad(es)</p>
                            </td>
                            <td class="px-6 py-4 text-[#4A4A45]">
                                <p>{{ $pedido->cliente_nombre }}</p>
                                <p class="text-xs text-[#6B6B66]">{{ $pedido->cliente_id_doc }} · {{ $pedido->cliente_telefono }}</p>
                            </td>
                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $pedido->destino_ciudad }}, {{ $pedido->destino_estado }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-[#111111]">
                                ${{ number_format((float) $pedido->precio_total_usd, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
                                    {{ $pedido->status === Pedido::STATUS_CONFIRMADO ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $pedido->status }}
                                </span>
                                @if ($pedido->package)
                                    <p class="mt-1 text-xs text-[#6B6B66]">Guía: {{ $pedido->package->tracking_number }}</p>
                                @elseif ($pedido->status === Pedido::STATUS_PAGADO)
                                    <p class="mt-1 text-xs text-[#6B6B66]">Llévalo a tu agencia para la guía</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('emprendedor.pedidos.show', $pedido->id) }}" wire:navigate
                                       class="px-3 py-2 rounded-lg border border-[#E5E5E0] text-xs font-semibold text-[#111111] hover:bg-slate-50 transition">
                                        Chat
                                    </a>

                                    @if ($pedido->status === Pedido::STATUS_PENDIENTE)
                                        <button
                                            @click.prevent="$store.confirm.open({
                                                message: '¿Ya recibiste el pago de este cliente? Después de confirmar, lleva el paquete a tu agencia aliada para generar la guía.',
                                                confirmText: 'Confirmar pedido',
                                                variant: 'primary',
                                                onConfirm: () => $wire.confirmar({{ $pedido->id }}),
                                            })"
                                            class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-xs font-semibold transition"
                                        >
                                            Confirmar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-[#6B6B66]">
                                Todavía no tienes pedidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pedidos->hasPages())
            <div class="px-6 py-4 border-t border-[#E5E5E0]">
                {{ $pedidos->links() }}
            </div>
        @endif
    </div>

</div>
