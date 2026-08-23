<div class="min-h-screen bg-white font-sans">
    <main class="flex-1 p-8 lg:p-10">

        {{-- Cabecera --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold tracking-tight text-[#0F172A]">Tarifas</h1>
                <p class="text-sm text-[#64748B] mt-1">Configura los precios de envío y simula una venta antes de aplicar cambios.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 px-4 py-3 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- ================= TARIFAS VIGENTES / EDICIÓN ================= --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <div class="flex justify-between items-center mb-5">
                    <h2 class="font-display text-lg font-bold text-[#0F172A]">Precios actuales</h2>
                    @unless ($editing)
                        <button wire:click="startEditing"
                                class="bg-[#0F172A] hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition-colors">
                            Modificar
                        </button>
                    @endunless
                </div>

                @unless ($editing)
                    {{-- Solo lectura --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Precio por volumen</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">${{ number_format((float) $base_price_usd, 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Por kg facturable</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">${{ number_format((float) $price_per_kg_usd, 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Por km</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">${{ number_format((float) $price_per_km_usd, 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Sobre (fijo)</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">${{ number_format((float) $envelope_price_usd, 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Recargo frágil</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">${{ number_format((float) $fragile_surcharge_usd, 2) }}</p>
                        </div>
                        <div class="bg-[#F8FAFC] rounded-xl p-4">
                            <p class="text-xs font-bold text-[#64748B] uppercase tracking-wider">Seguro</p>
                            <p class="font-display text-xl font-bold text-[#0F172A] mt-1">{{ number_format((float) $insurance_percentage, 2) }}%</p>
                        </div>
                    </div>
                @else
                    {{-- Formulario de edición --}}
                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Precio por volumen (USD)</label>
                                <input type="number" step="0.01" wire:model="base_price_usd"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('base_price_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Por kg (USD)</label>
                                <input type="number" step="0.01" wire:model="price_per_kg_usd"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('price_per_kg_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Por km (USD)</label>
                                <input type="number" step="0.01" wire:model="price_per_km_usd"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('price_per_km_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Sobre, fijo (USD)</label>
                                <input type="number" step="0.01" wire:model="envelope_price_usd"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('envelope_price_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Recargo frágil (USD)</label>
                                <input type="number" step="0.01" wire:model="fragile_surcharge_usd"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('fragile_surcharge_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Seguro (%)</label>
                                <input type="number" step="0.01" wire:model="insurance_percentage"
                                       class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                                @error('insurance_percentage') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E2E8F0]">
                            <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">
                                Confirma tu contraseña para guardar
                            </label>
                            <input type="password" wire:model="confirm_password"
                                   placeholder="Tu contraseña de administrador"
                                   class="w-full rounded-xl border-[#E2E8F0] focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('confirm_password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                                Guardar cambios
                            </button>
                            <button type="button" wire:click="cancelEditing"
                                    class="text-[#64748B] hover:text-[#0F172A] px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                                Cancelar
                            </button>
                        </div>
                    </form>
                @endunless
            </div>

            {{-- ================= SIMULADOR DE VENTA ================= --}}
            <div class="bg-white rounded-2xl border border-[#E2E8F0] p-6 shadow-sm">
                <h2 class="font-display text-lg font-bold text-[#0F172A] mb-5">Simular venta</h2>

                <form wire:submit="simulate" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Tipo de envío</label>
                        <select wire:model="sim_package_type" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            <option value="paquete">Paquete</option>
                            <option value="sobre">Sobre</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Ciudad origen</label>
                            <input type="text" wire:model="sim_origin_city" placeholder="Caracas"
                                   class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            @error('sim_origin_city') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Ciudad destino</label>
                            <input type="text" wire:model="sim_destination_city" placeholder="Valencia"
                                   class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            @error('sim_destination_city') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($sim_package_type === 'paquete')
                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Peso (kg)</label>
                                <input type="number" step="0.01" wire:model="sim_physical_weight_kg" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Largo (cm)</label>
                                <input type="number" step="0.01" wire:model="sim_length_cm" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Ancho (cm)</label>
                                <input type="number" step="0.01" wire:model="sim_width_cm" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Alto (cm)</label>
                                <input type="number" step="0.01" wire:model="sim_height_cm" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center gap-6">
                        <label class="flex items-center gap-2 text-sm text-[#0F172A]">
                            <input type="checkbox" wire:model="sim_is_fragile" class="rounded border-[#E2E8F0]">
                            Frágil
                        </label>
                        <label class="flex items-center gap-2 text-sm text-[#0F172A]">
                            <input type="checkbox" wire:model.live="sim_has_insurance" class="rounded border-[#E2E8F0]">
                            Con seguro
                        </label>
                    </div>

                    @if ($sim_has_insurance)
                        <div>
                            <label class="block text-xs font-bold text-[#64748B] uppercase tracking-wider mb-1">Valor declarado (USD)</label>
                            <input type="number" step="0.01" wire:model="sim_declared_value_usd" class="w-full rounded-xl border-[#E2E8F0] text-sm">
                            @error('sim_declared_value_usd') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="bg-blue-900 hover:bg-blue-800 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                            Simular
                        </button>
                        <button type="button" wire:click="resetSimulation"
                                class="text-[#64748B] hover:text-[#0F172A] px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                            Limpiar
                        </button>
                    </div>
                </form>

                @if ($simulationError)
                    <div class="mt-5 px-4 py-3 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm">
                        {{ $simulationError }}
                    </div>
                @endif

                @if ($simulationResult)
                    <div class="mt-5 bg-blue-900 rounded-2xl p-5 text-white">
                        <p class="text-blue-200 text-xs uppercase tracking-wider font-bold mb-3">Resultado de la simulación</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>Distancia: <span class="font-semibold">{{ $simulationResult['distance_km'] }} km</span></div>
                            <div>Peso facturable: <span class="font-semibold">{{ $simulationResult['billable_weight_kg'] }} kg</span></div>
                            <div>Subtotal: <span class="font-semibold">${{ number_format($simulationResult['subtotal_price_usd'], 2) }}</span></div>
                            <div>Recargo frágil: <span class="font-semibold">${{ number_format($simulationResult['fragile_surcharge_usd'], 2) }}</span></div>
                            <div>Seguro: <span class="font-semibold">${{ number_format($simulationResult['insurance_price_usd'], 2) }}</span></div>
                            <div>Tasa BCV usada: <span class="font-semibold">{{ number_format($simulationResult['bcv_rate_used'], 2) }}</span></div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-blue-800 flex justify-between items-center">
                            <span class="text-blue-200 text-sm">Total a cobrar</span>
                            <span class="font-display text-2xl font-bold">
                                ${{ number_format($simulationResult['total_price_usd'], 2) }}
                                <span class="text-blue-200 text-sm font-normal">/ Bs. {{ number_format($simulationResult['total_price_ves'], 2) }}</span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
