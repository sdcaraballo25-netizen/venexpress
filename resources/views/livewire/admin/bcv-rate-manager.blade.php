<div class="min-h-screen">
    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-[#111111]">Tasa BCV</h1>
            <p class="mt-1 text-sm text-[#6B6B66]">Consulta la tasa vigente y registra el histórico de tasas del dólar oficial.</p>
        </div>

        <button
            wire:click="syncNow"
            wire:loading.attr="disabled"
            wire:target="syncNow"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 disabled:opacity-60">
            <span wire:loading.remove wire:target="syncNow">Sincronizar ahora</span>
            <span wire:loading wire:target="syncNow">Consultando…</span>
        </button>
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
        <div class="overflow-hidden rounded-2xl border border-[#E5E5E0] bg-white shadow-sm">
            <div class="border-b border-[#E5E5E0] p-6">
                <h2 class="font-display text-lg font-bold text-[#111111]">Histórico de tasas</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-[#E5E5E0] bg-slate-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#6B6B66]">Fecha de vigencia</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#6B6B66]">Tasa (Bs.)</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#6B6B66]">Origen</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#6B6B66]">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($history as $bcvRate)
                            <tr class="border-b border-[#F0F0EC] last:border-0 hover:bg-slate-50">
                                <td class="px-6 py-4 font-medium text-[#111111]">
                                    {{ $bcvRate->effective_date->format('d/m/Y') }}
                                    @if ($current && $current->id === $bcvRate->id)
                                        <span class="ml-2 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800">Vigente</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-display font-bold text-[#111111]">
                                    {{ number_format((float) $bcvRate->rate, 2) }}
                                </td>
                                <td class="px-6 py-4 text-[#6B6B66]">
                                    {{ $bcvRate->source === 'manual' ? 'Manual' : ($bcvRate->source ?? '—') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="edit({{ $bcvRate->id }})"
                                                class="text-sm font-medium text-blue-800 hover:text-blue-900">
                                            Editar
                                        </button>
                                        <button
                                                @click.prevent="$store.confirm.open({
                                                    message: '¿Eliminar esta tasa? Esta acción no se puede deshacer.',
                                                    confirmText: 'Eliminar',
                                                    variant: 'danger',
                                                    onConfirm: () => $wire.delete({{ $bcvRate->id }}),
                                                })"
                                                class="text-sm font-medium text-red-600 hover:text-red-700">
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-[#6B6B66]">
                                    Aún no hay tasas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($history->hasPages())
                <div class="border-t border-[#E5E5E0] px-6 py-4">
                    {{ $history->links() }}
                </div>
            @endif
        </div>

        {{-- ================= FORMULARIO ================= --}}
        <div class="h-fit rounded-2xl border border-[#E5E5E0] bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-[#111111] mb-5">
                {{ $editingId ? 'Editar tasa' : 'Registrar nueva tasa' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#6B6B66]">Tasa (Bs. por USD)</label>
                    <input type="number" step="0.000001" wire:model="rate"
                           placeholder="150.250000"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#6B6B66]">Fecha de vigencia</label>
                    <input type="date" wire:model="effective_date"
                           class="w-full rounded-xl border-[#E5E5E0] text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('effective_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-[#111111] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar tasa' }}
                    </button>

                    @if ($editingId)
                        <button type="button" wire:click="cancelEdit"
                                class="px-5 py-2.5 text-sm font-medium text-[#6B6B66] transition-colors hover:text-[#111111]">
                            Cancelar
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
