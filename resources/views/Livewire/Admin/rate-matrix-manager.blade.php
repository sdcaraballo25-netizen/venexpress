<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-[#0B1220]">Tarifa</h1>
        <p class="text-sm text-[#64748B]">
            Tarifa única para todas las rutas. El costo de una guía se calcula como:
            precio base + (peso facturable × precio por kg) + (distancia × precio por km).
            Las distancias entre ciudades se configuran aparte, en
            <a href="{{ route('admin.city-distances') }}" class="font-semibold text-[#FF6A1A] hover:underline">Distancias</a>.
        </p>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <form wire:submit="save" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="text-xs font-semibold text-[#64748B]">Precio base (USD)</label>
                <input type="number" step="0.01" wire:model="base_price_usd"
                    class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                @error('base_price_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-[#64748B]">Precio por kg facturable (USD)</label>
                <input type="number" step="0.01" wire:model="price_per_kg_usd"
                    class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                @error('price_per_kg_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="text-xs font-semibold text-[#64748B]">Precio por km (USD)</label>
                <input type="number" step="0.01" wire:model="price_per_km_usd"
                    class="mt-1 w-full rounded-lg border-[#E2E8F0] font-tracking text-sm focus:border-[#FF6A1A] focus:ring-[#FF6A1A]">
                @error('price_per_km_usd') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-3">
                <button type="submit"
                    class="rounded-lg bg-[#FF6A1A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#e65f16]">
                    Guardar tarifa
                </button>
            </div>
        </form>
    </div>
</div>
