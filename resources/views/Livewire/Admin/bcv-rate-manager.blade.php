<div class="min-h-screen">
    <div class="mb-8">
        <h1 class="font-display text-3xl font-bold text-[#0F172A]">Tasa BCV</h1>
        <p class="mt-1 text-sm text-[#64748B]">Consulta la tasa vigente y registra el histórico de tasas del dólar oficial.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-red-100">!</div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_360px]">

        {{-- ================= HISTORIAL ================= --}}
        <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
            <div class="border-b border-[#E2E8F0] p-6">
                <h2 class="font-display text-lg font-bold text-[#0F172A]">Histórico de tasas</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-[#E2E8F0] bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Fecha de vigencia</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Tasa (Bs.)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Origen</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $bcvRate)
                            <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-[#0F172A]">
                                    {{ $bcvRate->effective_date->format('d/m/Y') }}
                                    @if ($current && $current->id === $bcvRate->id)
                                        <span class="ml-2 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800">Vigente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-display font-bold text-[#0F172A]">
                                    {{ number_format((float) $bcvRate->rate, 2) }}
                                </td>
                                <td class="px-6 py-4 text-[#64748B]">
                                    {{ $bcvRate->source === 'manual' ? 'Manual' : ($bcvRate->source ?? '—') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="edit({{ $bcvRate->id }})"
                                                class="text-sm font-medium text-blue-800 hover:text-blue-900">
                                            Editar
                                        </button>
                                        <button wire:click="delete({{ $bcvRate->id }})"
                                                wire:confirm="¿Eliminar esta tasa? Esta acción no se puede deshacer."
                                                class="text-sm font-medium text-red-600 hover:text-red-700">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-[#64748B]">
                                    Aún no hay tasas registradas.
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
                {{ $editingId ? 'Editar tasa' : 'Registrar nueva tasa' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Tasa (Bs. por USD)</label>
                    <input type="number" step="0.000001" wire:model="rate"
                           placeholder="150.250000"
                           class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Fecha de vigencia</label>
                    <input type="date" wire:model="effective_date"
                           class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('effective_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-[#0F172A] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar tasa' }}
                    </button>

                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit"
                                class="px-5 py-2.5 text-sm font-medium text-[#64748B] transition-colors hover:text-[#0F172A]">
                            Cancelar
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
