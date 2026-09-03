<div>
    <section class="bg-white">
        <div class="max-w-4xl mx-auto px-6 py-14">

            <div class="text-center mb-10">
                <span class="inline-block bg-amber-100 text-amber-700 text-xs font-semibold tracking-wide uppercase px-3 py-1 rounded-full mb-4">
                    Antes de enviar
                </span>
                <h1 class="text-3xl md:text-4xl font-extrabold text-blue-950">Calcula el precio de tu envío</h1>
                <p class="text-gray-500 mt-3 max-w-xl mx-auto">
                    Simula el costo real de tu paquete o sobre entre dos ciudades de Venezuela,
                    con la tasa BCV del día. Sin registrarte, sin compromiso.
                </p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">

                {{-- FORMULARIO --}}
                <div class="bg-gray-50 border border-gray-100 rounded-2xl p-6 space-y-5">

                    {{-- Tipo de envío --}}
                    <div>
                        <label class="block text-sm font-semibold text-blue-950 mb-2">Tipo de envío</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                wire:click="$set('package_type', '{{ \App\Models\Package::TYPE_PAQUETE }}')"
                                class="py-2.5 rounded-lg text-sm font-medium border transition
                                    {{ $package_type === \App\Models\Package::TYPE_PAQUETE
                                        ? 'bg-blue-950 text-white border-blue-950'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-950' }}">
                                <i class="fa-solid fa-box mr-1.5"></i> Paquete
                            </button>
                            <button type="button"
                                wire:click="$set('package_type', '{{ \App\Models\Package::TYPE_SOBRE }}')"
                                class="py-2.5 rounded-lg text-sm font-medium border transition
                                    {{ $package_type === \App\Models\Package::TYPE_SOBRE
                                        ? 'bg-blue-950 text-white border-blue-950'
                                        : 'bg-white text-gray-600 border-gray-200 hover:border-blue-950' }}">
                                <i class="fa-solid fa-envelope mr-1.5"></i> Sobre
                            </button>
                        </div>
                    </div>

                    {{-- Origen --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estado de origen</label>
                            <select wire:model.live="origin_state"
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                <option value="">Selecciona...</option>
                                @foreach (array_keys(config('venezuela.states', [])) as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Ciudad de origen</label>
                            <select wire:model.live="origin_city" @disabled(! $origin_state)
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950 disabled:bg-gray-100">
                                <option value="">Selecciona...</option>
                                @foreach ($this->originCities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Destino --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Estado de destino</label>
                            <select wire:model.live="destination_state"
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                <option value="">Selecciona...</option>
                                @foreach (array_keys(config('venezuela.states', [])) as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Ciudad de destino</label>
                            <select wire:model.live="destination_city" @disabled(! $destination_state)
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950 disabled:bg-gray-100">
                                <option value="">Selecciona...</option>
                                @foreach ($this->destinationCities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if ($package_type === \App\Models\Package::TYPE_PAQUETE)
                        {{-- Peso y dimensiones (solo paquete) --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Peso físico (kg)</label>
                            <input type="number" step="0.1" min="0" wire:model.live.debounce.500ms="physical_weight_kg"
                                placeholder="Ej. 2.5"
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Dimensiones (cm) — opcional</label>
                            <div class="grid grid-cols-3 gap-3">
                                <input type="number" min="0" wire:model.live.debounce.500ms="length_cm" placeholder="Largo"
                                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                <input type="number" min="0" wire:model.live.debounce.500ms="width_cm" placeholder="Ancho"
                                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                                <input type="number" min="0" wire:model.live.debounce.500ms="height_cm" placeholder="Alto"
                                    class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                            </div>
                        </div>
                    @endif

                    {{-- Opciones --}}
                    <div class="space-y-3 pt-1">
                        <label class="flex items-center gap-2.5 text-sm text-gray-600">
                            <input type="checkbox" wire:model.live="is_fragile" class="rounded border-gray-300 text-blue-950 focus:ring-blue-950">
                            Es frágil
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-600">
                            <input type="checkbox" wire:model.live="requires_delivery" class="rounded border-gray-300 text-blue-950 focus:ring-blue-950">
                            Necesito entrega a domicilio en destino
                        </label>
                        <label class="flex items-center gap-2.5 text-sm text-gray-600">
                            <input type="checkbox" wire:model.live="has_insurance" class="rounded border-gray-300 text-blue-950 focus:ring-blue-950">
                            Quiero asegurar mi envío
                        </label>
                        @if ($has_insurance)
                            <input type="number" step="0.01" min="0" wire:model.live.debounce.500ms="declared_value_usd"
                                placeholder="Valor declarado en USD"
                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-blue-950 focus:border-blue-950">
                        @endif
                    </div>
                </div>

                {{-- RESULTADO --}}
                <div class="bg-blue-950 rounded-2xl p-6 text-white flex flex-col">
                    <h2 class="font-semibold text-lg mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-amber-400"></i> Resultado estimado
                    </h2>

                    <div wire:loading class="text-sm text-white/60 mb-3">Calculando...</div>

                    @if ($errorMessage)
                        <div class="bg-red-500/15 border border-red-400/30 text-red-200 text-sm rounded-lg p-4">
                            {{ $errorMessage }}
                        </div>
                    @elseif ($result)
                        <div class="space-y-2.5 text-sm text-white/80 flex-1">
                            <div class="flex justify-between"><span>Distancia</span><span class="font-mono">{{ $result['distance_km'] }} km</span></div>
                            @if ($package_type === \App\Models\Package::TYPE_PAQUETE)
                                <div class="flex justify-between"><span>Peso facturable</span><span class="font-mono">{{ $result['billable_weight_kg'] }} kg</span></div>
                            @endif
                            @if ($result['fragile_surcharge_usd'] > 0)
                                <div class="flex justify-between"><span>Recargo frágil</span><span class="font-mono">${{ number_format($result['fragile_surcharge_usd'], 2) }}</span></div>
                            @endif
                            @if ($result['insurance_price_usd'] > 0)
                                <div class="flex justify-between"><span>Seguro</span><span class="font-mono">${{ number_format($result['insurance_price_usd'], 2) }}</span></div>
                            @endif
                            @if ($result['delivery_fee_usd'] > 0)
                                <div class="flex justify-between"><span>Entrega a domicilio</span><span class="font-mono">${{ number_format($result['delivery_fee_usd'], 2) }}</span></div>
                            @endif
                        </div>

                        <div class="border-t border-white/15 mt-4 pt-4">
                            <div class="text-xs text-white/50 mb-1">Total estimado</div>
                            <div class="text-3xl font-extrabold text-amber-400">${{ number_format($result['total_price_usd'], 2) }}</div>
                            <div class="text-sm text-white/60 mt-1">
                                Bs. {{ number_format($result['total_price_ves'], 2) }}
                                <span class="text-white/40">(tasa BCV {{ number_format($result['bcv_rate_used'], 2) }})</span>
                            </div>
                        </div>

                        <a href="{{ route('public.offices') }}" class="mt-5 block text-center bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm py-2.5 rounded-lg transition">
                            Buscar agencia aliada más cercana
                        </a>
                    @else
                        <div class="flex-1 flex items-center justify-center text-center text-white/50 text-sm py-10">
                            Completa origen, destino{{ $package_type === \App\Models\Package::TYPE_PAQUETE ? ' y peso' : '' }} para ver el precio.
                        </div>
                    @endif

                    <p class="text-[11px] text-white/40 mt-5">
                        Precio referencial. El monto final se confirma al registrar tu guía en la agencia aliada.
                    </p>
                </div>
            </div>
        </div>
    </section>
</div>
