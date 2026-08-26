<div class="space-y-6" x-data>

    <div>
        <h2 class="font-display text-2xl font-semibold text-[#0F172A]">
            Registrar pedido
        </h2>
        <p class="text-sm text-slate-500">
            Origen: {{ $origin_city }}
        </p>
    </div>

    @if ($createdTrackingNumber)
        {{-- PANEL DE ÉXITO --}}
        <div
            class="rounded-2xl border border-[#E2E8F0] bg-white p-6"
            wire:key="success-{{ $createdTrackingNumber }}"
            x-init="
                $nextTick(() => {
                    document.getElementById('qr-canvas').innerHTML = '';
                    new QRCode(document.getElementById('qr-canvas'), {
                        text: '{{ $createdTrackingNumber }}',
                        width: 160,
                        height: 160,
                    });
                });
            "
        >
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div id="qr-canvas" wire:ignore></div>

                <div class="flex-1 text-center sm:text-left">
                    <p class="text-sm text-emerald-600 font-medium">
                        Guía registrada con éxito
                    </p>
                    <p class="mt-1 font-display text-2xl font-semibold text-[#0F172A]">
                        {{ $createdTrackingNumber }}
                    </p>
                    <div class="mt-3 flex gap-6 justify-center sm:justify-start text-sm">
                        <div>
                            <p class="text-slate-500">Distancia</p>
                            <p class="font-medium text-[#0F172A]">
                                {{ data_get($pricePreview, 'distance_km', 0) }} km
                            </p>
                        </div>
                        @if (($pricePreview['delivery_fee_usd'] ?? 0) > 0)
                            <div>
                                <p class="text-slate-500">Delivery</p>
                                <p class="font-medium text-[#0F172A]">
                                    ${{ number_format($pricePreview['delivery_fee_usd'], 2) }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-slate-500">Total USD</p>
                            <p class="font-semibold text-[#0F172A]">
                                ${{ number_format($createdTotalUsd, 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total VES</p>
                            <p class="font-semibold text-[#0F172A]">
                                Bs. {{ number_format($createdTotalVes, 2) }}
                            </p>
                        </div>
                    </div>

                    <button
                        wire:click="registerAnother"
                        class="mt-4 rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Registrar otro pedido
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- FORMULARIO --}}
        <form wire:submit="save" class="space-y-6">

            {{-- REMITENTE --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Remitente
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-slate-600">Nombre completo</label>
                        <input type="text" wire:model="sender_name"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('sender_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-600">Cédula / RIF</label>
                        <input type="text" wire:model="sender_id_doc" placeholder="V-12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('sender_id_doc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-600">Teléfono</label>
                        <input type="text" wire:model="sender_phone"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('sender_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- DESTINATARIO --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Destinatario
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-slate-600">Nombre completo</label>
                        <input type="text" wire:model="recipient_name"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('recipient_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-600">Cédula / RIF</label>
                        <input type="text" wire:model="recipient_id_doc" placeholder="V-12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('recipient_id_doc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-slate-600">Teléfono</label>
                        <input type="text" wire:model="recipient_phone"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('recipient_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- PAQUETE --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Paquete y destino
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="text-sm text-slate-600">Estado destino</label>
                        <select wire:model.live="destination_state"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            <option value="">Selecciona un estado...</option>
                            @foreach (array_keys(config('venezuela.states', [])) as $state)
                                <option value="{{ $state }}">{{ $state }}</option>
                            @endforeach
                        </select>
                        @error('destination_state') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-slate-600">Ciudad destino</label>
                        <select wire:model.live="destination_city"
                            @disabled(empty($destination_state))
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900 disabled:bg-slate-50">
                            <option value="">Selecciona una ciudad...</option>
                            @foreach (config('venezuela.states')[$destination_state] ?? [] as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                        @error('destination_city') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-slate-600">Tipo</label>
                        <select wire:model.live="package_type" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            <option value="sobre">Sobre</option>
                            <option value="paquete">Paquete</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-sm text-slate-600">Peso físico (kg)</label>
                        <input type="number" step="0.001" wire:model.live.debounce.500ms="physical_weight_kg"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('physical_weight_kg') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span>
                            <strong>Origen:</strong>
                            {{ $origin_city }}{{ $origin_state ? ', ' . $origin_state : '' }}
                        </span>
                        @if ($pricePreview && isset($pricePreview['distance_km']))
                            <span class="font-semibold">
                                Distancia por carretera: {{ data_get($pricePreview, 'distance_km', 0) }} km
                            </span>
                        @endif
                    </div>
                    @if ($pricePreviewError)
                        <p class="mt-1 text-xs text-red-600">{{ $pricePreviewError }}</p>
                    @endif
                </div>

                @if ($package_type === 'paquete')
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="text-sm text-slate-600">Largo (cm)</label>
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="length_cm"
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        </div>
                        <div>
                            <label class="text-sm text-slate-600">Ancho (cm)</label>
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="width_cm"
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        </div>
                        <div>
                            <label class="text-sm text-slate-600">Alto (cm)</label>
                            <input type="number" step="0.01" wire:model.live.debounce.500ms="height_cm"
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        </div>
                    </div>
                @endif

                <div class="flex flex-wrap gap-6 mt-4">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="is_fragile" class="h-4 w-4 rounded border border-slate-300 text-blue-900 focus:ring-blue-900">
                        Frágil
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" wire:model.live="has_insurance" class="h-4 w-4 rounded border border-slate-300 text-blue-900 focus:ring-blue-900">
                        Asegurar envío
                    </label>
                </div>

                @if ($has_insurance)
                    <div class="mt-4 max-w-xs">
                        <label class="text-sm text-slate-600">Valor declarado (USD)</label>
                        <input type="number" step="0.01" wire:model.live.debounce.500ms="declared_value_usd"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('declared_value_usd') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            {{-- DELIVERY --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Entrega a domicilio
                </h3>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" wire:model.live="requires_delivery"
                        class="h-4 w-4 rounded border border-slate-300 text-blue-900 focus:ring-blue-900">
                    Requiere delivery
                </label>

                @if ($requires_delivery)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="md:col-span-2">
                            <label class="text-sm text-slate-600">Dirección exacta de entrega</label>
                            <textarea wire:model.live="delivery_address" rows="2"
                                placeholder="Calle/avenida, edificio o casa, número, piso, apartamento..."
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900"></textarea>
                            @error('delivery_address') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm text-slate-600">Sector / urbanización</label>
                            <input type="text" wire:model.live="delivery_sector"
                                placeholder="Ej. La Floresta"
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            @error('delivery_sector') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-sm text-slate-600">Punto de referencia</label>
                            <input type="text" wire:model.live="delivery_reference"
                                placeholder="Ej. Frente al centro comercial..."
                                class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            @error('delivery_reference') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($pricePreview)
                        <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                            <span class="text-slate-500">Cargo delivery:</span>
                            <strong>${{ number_format($pricePreview['delivery_fee_usd'] ?? 0, 2) }}</strong>
                        </div>
                    @endif
                @endif
            </div>

            {{-- COBRO --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Cobro
                </h3>

                <label class="flex items-center gap-2 text-sm text-slate-600 mb-4">
                    <input type="checkbox" wire:model.live="is_cod"
                        class="h-4 w-4 rounded border border-slate-300 text-blue-900 focus:ring-blue-900">
                    Cobro contra entrega (COD) — se cobra en destino, no en esta agencia
                </label>

                @if (! $is_cod)
                    <div class="max-w-sm">
                        <label class="text-sm text-slate-600">Método de pago del envío</label>
                        <select wire:model="payment_method"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            <option value="">Selecciona...</option>
                            <option value="efectivo_usd">Efectivo USD</option>
                            <option value="efectivo_ves">Efectivo VES</option>
                            <option value="pago_movil">Pago móvil</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle</option>
                        </select>
                        @error('payment_method') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div class="max-w-sm">
                        <label class="text-sm text-slate-600">Monto a cobrar en destino (USD)</label>
                        <div class="mt-1 w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-semibold text-[#0F172A]">
                            {{ $cod_amount_usd ? '$' . number_format($cod_amount_usd, 2) : 'Se calcula al completar destino y peso' }}
                        </div>
                        <p class="text-xs text-slate-400 mt-1">
                            Calculado automáticamente según la tarifa. No se edita manualmente.
                        </p>
                        @error('cod_amount_usd') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            {{-- TARIFA ESTIMADA (en vivo) --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Tarifa estimada
                </h3>

                @if ($pricePreview)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Peso facturable</p>
                            <p class="font-medium text-[#0F172A]">
                                {{ number_format($pricePreview['billable_weight_kg'], 3) }} kg
                            </p>
                        </div>
                        @if (($pricePreview['fragile_surcharge_usd'] ?? 0) > 0)
                            <div>
                                <p class="text-slate-500">Recargo frágil</p>
                                <p class="font-medium text-[#0F172A]">
                                    ${{ number_format($pricePreview['fragile_surcharge_usd'], 2) }}
                                </p>
                            </div>
                        @endif
                        @if (($pricePreview['insurance_price_usd'] ?? 0) > 0)
                            <div>
                                <p class="text-slate-500">Seguro</p>
                                <p class="font-medium text-[#0F172A]">
                                    ${{ number_format($pricePreview['insurance_price_usd'], 2) }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-slate-500">Distancia</p>
                            <p class="font-medium text-[#0F172A]">
                                {{ data_get($pricePreview, 'distance_km', 0) }} km
                            </p>
                        </div>
                        @if (($pricePreview['delivery_fee_usd'] ?? 0) > 0)
                            <div>
                                <p class="text-slate-500">Delivery</p>
                                <p class="font-medium text-[#0F172A]">
                                    ${{ number_format($pricePreview['delivery_fee_usd'], 2) }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="text-slate-500">Total USD</p>
                            <p class="font-display text-lg font-semibold text-blue-900">
                                ${{ number_format($pricePreview['total_price_usd'], 2) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total VES</p>
                            <p class="font-display text-lg font-semibold text-[#0F172A]">
                                Bs. {{ number_format($pricePreview['total_price_ves'], 2) }}
                            </p>
                        </div>
                    </div>
                @elseif ($pricePreviewError)
                    <p class="text-sm text-red-600">
                        No se pudo calcular la tarifa: {{ $pricePreviewError }}
                    </p>
                @else
                    <p class="text-sm text-slate-400">
                        Completa la ciudad destino y el peso físico para ver el precio.
                    </p>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-between gap-3">
                <button
                    type="button"
                    wire:click="calculatePrice"
                    wire:loading.attr="disabled"
                    wire:target="calculatePrice"
                    class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-800 disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="calculatePrice">
                        Calcular precio
                    </span>
                    <span wire:loading wire:target="calculatePrice">
                        Calculando...
                    </span>
                </button>

                <button type="submit"
                    class="rounded-xl bg-blue-900 px-6 py-3 text-sm font-medium text-white"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Registrar guía</span>
                    <span wire:loading>Calculando tarifa...</span>
                </button>
            </div>
        </form>
    @endif
</div>

@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    @endpush
@endonce
