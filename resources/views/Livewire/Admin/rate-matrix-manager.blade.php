<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-[#0B1220]">Matrices de tarifas</h1>
            <p class="text-sm text-[#64748B]">Precio base y por kg facturable para cada ruta origen → destino.</p>
        </div>
        @unless ($showForm)
            <button wire:click="create"
                class="rounded-lg bg-[#FF6A1A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e65f16]">
                + Nueva ruta
            </button>
        @endunless
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-[#0B1220]">
                {{ $editingId ? 'Editar ruta' : 'Nueva ruta' }}
            </h2>

            <form wire:submit="save" class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold text-[#64748B]">Ciudad origen</label>
                    <input type="text" wire:model="origin_city"
                        class="mt-1 w-full rounded-lg border-[#E2E8F0] text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                    @error('origin_city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-[#64748B]">Ciudad destino</label>
                    <input type="text" wire:model="destination_city"
                        class="mt-1 w-full rounded-lg border-[#E2E8F0] text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                    @error('destination_city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-[#64748B]">Precio base (USD)</label>
                    <input type="number" step="0.01" wire:model="base_price_usd"
                        class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                    @error('base_price_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-[#64748B]">Precio por kg (USD)</label>
                    <input type="number" step="0.01" wire:model="price_per_kg_usd"
                        class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                    @error('price_per_kg_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit"
                        class="rounded-lg bg-[#FF6A1A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e65f16]">
                        {{ $editingId ? 'Guardar cambios' : 'Crear ruta' }}
                    </button>
                    <button type="button" wire:click="cancelEdit"
                        class="rounded-lg border border-[#E2E8F0] px-4 py-2 text-sm font-semibold text-[#64748B] hover:bg-slate-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-[#E2E8F0] text-left text-xs font-semibold uppercase tracking-wide text-[#94A3B8]">
                    <th class="px-6 py-3">Origen</th>
                    <th class="px-6 py-3">Destino</th>
                    <th class="px-6 py-3">Base (USD)</th>
                    <th class="px-6 py-3">Por kg (USD)</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($matrices as $matrix)
                    <tr class="border-b border-[#F1F5F9] last:border-0">
                        <td class="px-6 py-3">{{ $matrix->origin_city }}</td>
                        <td class="px-6 py-3">{{ $matrix->destination_city }}</td>
                        <td class="px-6 py-3 font-tracking">${{ number_format($matrix->base_price_usd, 2) }}</td>
                        <td class="px-6 py-3 font-tracking">${{ number_format($matrix->price_per_kg_usd, 2) }}</td>
                        <td class="px-6 py-3 text-right">
                            <button wire:click="edit({{ $matrix->id }})" class="text-xs font-semibold text-[#FF6A1A] hover:underline">Editar</button>
                            <button wire:click="delete({{ $matrix->id }})"
                                wire:confirm="¿Eliminar esta ruta?"
                                class="ml-3 text-xs font-semibold text-red-500 hover:underline">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-6 text-center text-[#94A3B8]">Sin rutas configuradas.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $matrices->links() }}
        </div>