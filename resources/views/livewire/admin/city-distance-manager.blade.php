<div class="min-h-screen">
    <div class="mb-8 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-[#0F172A]">Distancias entre ciudades</h1>
            <p class="mt-1 text-sm text-[#64748B]">Estas distancias alimentan el cálculo de precio por kilómetro de cada guía.</p>
        </div>

        @unless ($showForm)
            <button wire:click="create"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
                <span class="text-lg leading-none">+</span>
                Nueva distancia
            </button>
        @endunless
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100">✓</div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($showForm)
        <div class="mb-6 rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-bold text-[#0F172A] mb-5">
                {{ $editingId ? 'Editar distancia' : 'Registrar distancia' }}
            </h2>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Ciudad A</label>
                        <input type="text" wire:model="city_one" placeholder="Caracas"
                               class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('city_one') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Ciudad B</label>
                        <input type="text" wire:model="city_two" placeholder="Valencia"
                               class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('city_two') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-[#64748B]">Distancia (km)</label>
                        <input type="number" wire:model="distance_km" placeholder="180"
                               class="w-full rounded-xl border-[#E2E8F0] text-sm focus:border-blue-500 focus:ring-blue-500">
                        @error('distance_km') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="rounded-xl bg-[#0F172A] px-5 py-2.5 text-sm font-medium text-white transition-colors hover:bg-slate-800">
                        {{ $editingId ? 'Guardar cambios' : 'Registrar distancia' }}
                    </button>
                    <button type="button" wire:click="cancelEdit"
                            class="px-5 py-2.5 text-sm font-medium text-[#64748B] transition-colors hover:text-[#0F172A]">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-[#E2E8F0] bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Ciudad A</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase text-[#64748B]">Ciudad B</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Distancia</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase text-[#64748B]">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($distances as $distance)
                        <tr class="border-b border-[#F1F5F9] last:border-0 hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-[#0F172A]">{{ $distance->city_a }}</td>
                            <td class="px-6 py-4 font-medium text-[#0F172A]">{{ $distance->city_b }}</td>
                            <td class="px-6 py-4 text-right font-display font-bold text-[#0F172A]">
                                {{ number_format($distance->distance_km) }} km
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <button wire:click="edit({{ $distance->id }})"
                                            class="text-sm font-medium text-blue-800 hover:text-blue-900">
                                        Editar
                                    </button>
                                    <button wire:click="delete({{ $distance->id }})"
                                            wire:confirm="¿Eliminar esta distancia?"
                                            class="text-sm font-medium text-red-600 hover:text-red-700">
                                        Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-[#64748B]">
                                Aún no hay distancias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($distances->hasPages())
            <div class="border-t border-[#E2E8F0] px-6 py-4">
                {{ $distances->links() }}
            </div>
        @endif
    </div>
</div>
