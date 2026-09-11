<div class="min-h-screen">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#0F172A]">Remuneración de Repartidores</h1>
        <p class="mt-1 text-sm text-[#64748B]">Define cuánto se paga a cada repartidor por cada paquete entregado. Es una tarifa global, aplica a todos por igual.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_360px]">

        {{-- ================= HISTORIAL ================= --}}
        <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
            <div class="border-b border-[#E2E8F0] p-6">
                <h2 class="font-display text-lg font-bold text-[#0F172A]">Histórico de tarifas</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-[#E2E8F0] bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Vigente desde</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Monto (USD)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $rate)
                            <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-[#0F172A]">
                                    {{ $rate->effective_at->format('d/m/Y h:i A') }}
                                    @if ($current && $current->id === $rate->id)
                                        <span class="ml-2 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800">Vigente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-display font-bold text-[#0F172A]">
                                    ${{ number_format((float) $rate->amount_usd, 2) }}
                                </td>
                                <td class="px-6 py-4 text-[#64748B]">
                                    {{ $rate->createdBy?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-[#64748B]">
                                    Aún no hay tarifas registradas. El sistema está usando el valor de respaldo del archivo .env.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($history->hasPages())
                <div class="border-t border-[#E2E8F0] px-6 py-4">
                    {{ $history->links() }}
                </div>
            @endif
        </div>

        {{-- ================= FORMULARIO ================= --}}
        <div class="h-fit rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-[#0F172A] mb-5">
                Actualizar tarifa
            </h2>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Monto por paquete entregado (USD)</label>
                    <input type="number" step="0.01" wire:model="amount_usd"
                           placeholder="1.00"
                           class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('amount_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <p class="text-xs text-[#64748B]">
                    Este cambio no afecta remuneraciones ya generadas para entregas pasadas — solo aplica a partir de la próxima entrega que se complete.
                </p>

                <div class="pt-2">
                    <button type="submit"
                            class="rounded-xl bg-[#0F172A] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        Guardar tarifa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
