@php
    use App\Models\Pedido;
@endphp

<div class="space-y-6 font-sans">

    <div>
        <h1 class="font-display text-3xl font-bold tracking-tight text-[#111111]">Mis Compras</h1>
        <p class="text-sm text-[#6B6B66] mt-1">
            Pedidos que hiciste en la Tienda de Emprendedores. Entra al chat para coordinar el pago o ver novedades.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-[#E5E5E0] shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-[#E5E5E0]">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">Producto</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-[#6B6B66] uppercase">Vendedor</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">Total</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-[#6B6B66] uppercase">Estado</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-[#6B6B66] uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pedidos as $pedido)
                        <tr class="border-b border-[#F0F0EC] last:border-0 hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <p class="text-xs font-semibold text-[#6B6B66]">Pedido #{{ $pedido->id }}</p>
                                <p class="font-semibold text-[#111111]">{{ $pedido->resumen_items }}</p>
                            </td>
                            <td class="px-6 py-4 text-[#4A4A45]">
                                {{ $pedido->emprendedor->business_name }}
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-[#111111]">
                                ${{ number_format((float) $pedido->precio_total_usd, 2) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-lg
                                    {{ $pedido->status === Pedido::STATUS_CONFIRMADO ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $pedido->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('public.marketplace.pedido', $pedido->chat_token) }}" wire:navigate
                                   class="px-3 py-2 rounded-lg bg-blue-950 text-white hover:bg-blue-900 text-xs font-semibold transition">
                                    Ir al chat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-[#6B6B66]">
                                Todavía no has hecho compras en la tienda.
                                <a href="{{ route('public.marketplace') }}" wire:navigate class="text-blue-700 hover:text-blue-950 font-semibold">
                                    Ver la tienda
                                </a>
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
