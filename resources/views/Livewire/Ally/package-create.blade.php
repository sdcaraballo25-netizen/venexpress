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
            class="print:hidden rounded-2xl border border-[#E2E8F0] bg-white p-6"
            wire:key="success-{{ $createdTrackingNumber }}"
            x-init="
                $nextTick(() => {
                    document.getElementById('qr-canvas').innerHTML = '';
                    new QRCode(document.getElementById('qr-canvas'), {
                        text: '{{ $createdTrackingNumber }}',
                        width: 160,
                        height: 160,
                    });
                    document.getElementById('qr-canvas-print').innerHTML = '';
                    new QRCode(document.getElementById('qr-canvas-print'), {
                        text: '{{ $createdTrackingNumber }}',
                        width: 120,
                        height: 120,
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

                    <div class="mt-4 flex flex-wrap gap-3 justify-center sm:justify-start">
                        <button
                            type="button"
                            onclick="window.print()"
                            class="rounded-xl border border-blue-900 px-4 py-2 text-sm font-medium text-blue-900"
                        >
                            Imprimir guía
                        </button>
                        <button
                            wire:click="registerAnother"
                            class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white"
                        >
                            Registrar otro pedido
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- COMPROBANTE IMPRIMIBLE — oculto en pantalla, forzado a visible al imprimir --}}
        {{-- Usamos display:none inline + CSS puro (no depende de que Tailwind
             haya compilado las clases print:*), para que funcione aunque los
             assets no se hayan recompilado todavía. --}}
        <div id="printable-guide" style="display:none;" wire:key="print-{{ $createdTrackingNumber }}">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;border-bottom:3px solid #000;padding-bottom:10px;margin-bottom:14px;">
                <div>
                    <p style="font-size:20px;font-weight:700;margin:0;">Venexpress</p>
                    <p style="font-size:11px;margin:2px 0 0;">Comprobante de envío / Guía</p>
                    @if ($ally = auth()->user()->resolveAlly())
                        <p style="font-size:11px;margin:2px 0 0;">Agencia: {{ $ally->name ?? '' }}</p>
                    @endif
                </div>
                <div style="text-align:right;">
                    <p style="font-size:20px;font-weight:700;margin:0;">{{ $createdTrackingNumber }}</p>
                    <p style="font-size:11px;margin:2px 0 0;">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            <div id="qr-canvas-print" wire:ignore style="margin-bottom:14px;"></div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Remitente</p>
                    <p style="margin:0;">{{ $printSnapshot['sender_name'] ?? '' }}</p>
                    <p style="margin:0;">C.I./RIF: {{ $printSnapshot['sender_id_doc'] ?? '' }}</p>
                    <p style="margin:0;">Tel: {{ $printSnapshot['sender_phone'] ?? '' }}</p>
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Destinatario</p>
                    <p style="margin:0;">{{ $printSnapshot['recipient_name'] ?? '' }}</p>
                    <p style="margin:0;">C.I./RIF: {{ $printSnapshot['recipient_id_doc'] ?? '' }}</p>
                    <p style="margin:0;">Tel: {{ $printSnapshot['recipient_phone'] ?? '' }}</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Origen</p>
                    <p style="margin:0;">{{ $printSnapshot['origin_city'] ?? '' }}{{ ! empty($printSnapshot['origin_state']) ? ', ' . $printSnapshot['origin_state'] : '' }}</p>
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Destino</p>
                    <p style="margin:0;">{{ $printSnapshot['destination_city'] ?? '' }}{{ ! empty($printSnapshot['destination_state']) ? ', ' . $printSnapshot['destination_state'] : '' }}</p>
                </div>
            </div>

            @if (! empty($printSnapshot['requires_delivery']))
                <div style="margin-bottom:14px;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Entrega a domicilio</p>
                    <p style="margin:0;">{{ $printSnapshot['delivery_address'] ?? '' }}</p>
                    <p style="margin:0;">{{ $printSnapshot['delivery_sector'] ?? '' }}</p>
                    @if (! empty($printSnapshot['delivery_reference']))
                        <p style="margin:0;">Referencia: {{ $printSnapshot['delivery_reference'] }}</p>
                    @endif
                </div>
            @else
                <div style="margin-bottom:14px;">
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Entrega</p>
                    <p style="margin:0;">Retiro en agencia destino</p>
                </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Paquete</p>
                    <p style="margin:0;">Tipo: {{ ucfirst($printSnapshot['package_type'] ?? '') }}</p>
                    <p style="margin:0;">Peso: {{ $printSnapshot['physical_weight_kg'] ?? '' }} kg</p>
                    @if (($printSnapshot['package_type'] ?? '') !== 'sobre' && ($printSnapshot['length_cm'] || $printSnapshot['width_cm'] || $printSnapshot['height_cm']))
                        <p style="margin:0;">
                            Dimensiones: {{ $printSnapshot['length_cm'] ?? '—' }} x {{ $printSnapshot['width_cm'] ?? '—' }} x {{ $printSnapshot['height_cm'] ?? '—' }} cm
                        </p>
                    @endif
                    @if (! empty($printSnapshot['is_fragile']))
                        <p style="margin:0;font-weight:700;">⚠ FRÁGIL</p>
                    @endif
                    @if (! empty($printSnapshot['has_insurance']))
                        <p style="margin:0;">Asegurado — valor declarado: ${{ number_format($printSnapshot['declared_value_usd'] ?? 0, 2) }}</p>
                    @endif
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;text-transform:uppercase;border-bottom:1px solid #000;margin:0 0 4px;">Cobro</p>
                    @if (! empty($printSnapshot['is_cod']))
                        <p style="margin:0;font-weight:700;">COBRO CONTRA ENTREGA (COD)</p>
                        <p style="margin:0;">Monto a cobrar en destino: ${{ number_format($printSnapshot['cod_amount_usd'] ?? 0, 2) }}</p>
                    @else
                        <p style="margin:0;font-weight:700;">PAGADO EN AGENCIA DE ORIGEN</p>
                        <p style="margin:0;">Método: {{ \App\Livewire\Ally\PackageCreate::paymentMethodLabels()[$printSnapshot['payment_method'] ?? ''] ?? '—' }}</p>
                    @endif
                </div>
            </div>

            <div style="border-top:3px solid #000;padding-top:8px;margin-top:10px;display:flex;justify-content:space-between;font-weight:700;font-size:15px;">
                <span>Total USD: ${{ number_format($createdTotalUsd ?? 0, 2) }}</span>
                <span>Total VES: Bs. {{ number_format($createdTotalVes ?? 0, 2) }}</span>
            </div>

            <div style="margin-top:36px;display:grid;grid-template-columns:1fr 1fr;gap:40px;font-size:11px;">
                <div style="border-top:1px solid #000;padding-top:4px;text-align:center;">Firma de quien entrega</div>
                <div style="border-top:1px solid #000;padding-top:4px;text-align:center;">Firma de quien recibe</div>
            </div>
        </div>

        @once
            <style>
                @media print {
                    body * {
                        visibility: hidden !important;
                    }
                    #printable-guide,
                    #printable-guide * {
                        visibility: visible !important;
                    }
                    #printable-guide {
                        display: block !important;
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        color: #000;
                    }
                }
            </style>
        @endonce
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
                        <input type="text" wire:model.live.debounce.500ms="sender_id_doc" placeholder="V-12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @if ($senderCustomerFound)
                            <p class="text-xs text-emerald-600 mt-1">✓ Cliente encontrado — datos autocompletados</p>
                        @endif
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
                        <input type="text" wire:model.live.debounce.500ms="recipient_id_doc" placeholder="V-12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @if ($recipientCustomerFound)
                            <p class="text-xs text-emerald-600 mt-1">✓ Cliente encontrado — datos autocompletados</p>
                        @endif
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
