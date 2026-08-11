<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-[#0B1220]">Tasa BCV</h1>
            <p class="text-sm text-[#64748B]">Gestiona la tasa oficial del dólar usada para el cálculo de tarifas.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#94A3B8]">Tasa vigente</p>
        @if ($current)
            <div class="mt-2 flex items-baseline gap-3">
                <span class="font-tracking text-3xl font-bold text-[#0B1220]">
                    {{ number_format($current->rate, 4) }}
                </span>
                <span class="text-sm text-[#64748B]">VES por USD</span>
            </div>
            <p class="mt-1 text-xs text-[#94A3B8]">
                Vigente desde {{ $current->effective_date->format('d/m/Y') }}
            </p>
        @else
            <p class="mt-2 text-sm text-[#64748B]">Aún no hay ninguna tasa registrada.</p>
        @endif
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <h2 class="font-display text-lg font-semibold text-[#0B1220]">
            {{ $editingId ? 'Editar tasa' : 'Registrar nueva tasa' }}
        </h2>

        <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="text-xs font-semibold text-[#64748B]">Valor (VES)</label>
                <input type="number" step="0.000001" wire:model="rate"
                    class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                @error('rate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-[#64748B]">Fecha de vigencia</label>
                <input type="date" wire:model="effective_date"
                    class="mt-1 w-full rounded-lg border-[#E2E8F0] text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                @error('effective_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="rounded-lg bg-[#FF6A1A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e65f16]">
                    {{ $editingId ? 'Guardar cambios' : 'Registrar tasa' }}
                </button>
                @if ($editingId)
                    <button type="button" wire:click="cancelEdit"
                        class="rounded-lg border border-[#E2E8F0] px-4 py-2 text-sm font-semibold text-[#64748B] hover:bg-slate-50">
                        Cancelar
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <div class="border-b border-[#E2E8F0] px-6 py-4">
            <h2 class="font-display text-lg font-semibold text-[#0B1220]">Historial</h2>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#E2E8F0] text-left text-xs font-semibold uppercase tracking-wide text-[#94A3B8]">
                    <th class="px-6 py-3">Fecha</th>
                    <th class="px-6 py-3">Tasa (VES)</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $item)
                    <tr class="border-b border-[#F1F5F9] last:border-0">
                        <td class="px-6 py-3">{{ $item->effective_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-3 font-tracking">{{ number_format($item->rate, 6) }}</td>
                        <td class="px-6 py-3 text-right">
                            <button wire:click="edit({{ $item->id }})" class="text-xs font-semibold text-[#FF6A1A] hover:underline">Editar</button>
                            <button wire:click="delete({{ $item->id }})"
                                wire:confirm="¿Eliminar esta tasa?"
                                class="ml-3 text-xs font-semibold text-red-500 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-6 text-center text-[#94A3B8]">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $history->links() }}
        </div>
    </div>