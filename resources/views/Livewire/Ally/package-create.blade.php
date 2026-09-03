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
                    document.getElementById('qr-canvas-invoice').innerHTML = '';
                    new QRCode(document.getElementById('qr-canvas-invoice'), {
                        text: '{{ $createdTrackingNumber }}',
                        width: 170,
                        height: 170,
                    });
                    document.getElementById('qr-canvas-label').innerHTML = '';
                    new QRCode(document.getElementById('qr-canvas-label'), {
                        text: '{{ $createdTrackingNumber }}',
                        width: 260,
                        height: 260,
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
                            onclick="
                                document.body.setAttribute('data-print-target', 'invoice');
                                document.getElementById('dynamic-page-size').textContent = '@page { size: 80mm auto; margin: 3mm; }';
                                window.print();
                            "
                            class="rounded-xl border border-blue-900 px-4 py-2 text-sm font-medium text-blue-900"
                        >
                            Imprimir factura cliente (tickera 80mm)
                        </button>
                        <button
                            type="button"
                            onclick="
                                document.body.setAttribute('data-print-target', 'label');
                                document.getElementById('dynamic-page-size').textContent = '@page { size: auto; margin: 10mm; }';
                                window.print();
                            "
                            class="rounded-xl border border-blue-900 px-4 py-2 text-sm font-medium text-blue-900"
                        >
                            Imprimir etiqueta paquete (impresora normal)
                        </button>
                        @if ($createdPackageId)
                            <a
                                href="{{ route('packages.label', $createdPackageId) }}"
                                target="_blank"
                                class="rounded-xl border border-blue-900 px-4 py-2 text-sm font-medium text-blue-900"
                            >
                                Descargar guía (PDF)
                            </a>
                        @endif
                        <button
                            wire:click="registerAnother"
                            class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white"
                        >
                            Registrar otro pedido
                        </button>
                    </div>

                    <style id="dynamic-page-size"></style>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- DOCUMENTO 1: FACTURA / COMPROBANTE PARA EL CLIENTE       --}}
        {{-- Oculto en pantalla; solo se muestra cuando data-print-target="invoice" --}}
        {{-- ======================================================= --}}
        <div id="printable-invoice" style="display:none; width:100%; max-width:76mm; margin:0 auto; font-family: Arial, sans-serif; color:#000;" wire:key="invoice-{{ $createdTrackingNumber }}">

            <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:8px;margin-bottom:8px;">
                <p style="font-size:18px;font-weight:700;margin:0;">VENEXPRESS</p>
                @if ($ally = auth()->user()->resolveAlly())
                    <p style="font-size:11px;margin:2px 0 0;">{{ $ally->business_name ?? ($ally->name ?? '') }}</p>
                    <p style="font-size:11px;margin:0;">{{ $ally->address ?? '' }}</p>
                    <p style="font-size:11px;margin:0;">{{ $ally->city ?? '' }}</p>
                @endif
            </div>

            <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:8px;margin-bottom:8px;">
                <p style="font-size:13px;font-weight:700;margin:0;">Guía: {{ $createdTrackingNumber }}</p>
                <p style="font-size:11px;margin:2px 0 0;">{{ now()->format('d/m/Y H:i') }}</p>
            </div>

            <div style="text-align:center;border-bottom:1px dashed #000;padding:8px 0;margin-bottom:10px;">
                <p style="font-size:16px;font-weight:700;margin:0;text-transform:uppercase;">
                    Envío {{ ucfirst($printSnapshot['package_type'] ?? '') }}
                </p>
            </div>

            <div style="text-align:center;margin-bottom:10px;">
                <div id="qr-canvas-invoice" wire:ignore style="display:flex;justify-content:center;"></div>
            </div>

            <div style="text-align:center;border-bottom:1px dashed #000;padding-bottom:10px;margin-bottom:10px;">
                <p style="font-size:20px;font-weight:700;margin:0;text-transform:uppercase;line-height:1.2;">
                    {{ $printSnapshot['destination_city'] ?? '' }}
                </p>
                <p style="font-size:13px;margin:2px 0 0;">
                    {{ $printSnapshot['destination_state'] ?? '' }}
                </p>
            </div>

            <div style="text-align:center;border-bottom:1px dashed #000;padding:6px 0;margin-bottom:10px;">
                <p style="font-size:13px;font-weight:700;margin:0;">
                    {{ ! empty($printSnapshot['requires_delivery']) ? 'ENTREGA A DOMICILIO' : 'RETIRO EN AGENCIA' }}
                </p>
            </div>

            <div style="font-size:12px;line-height:1.5;margin-bottom:10px;">
                @if (! empty($printSnapshot['requires_delivery']))
                    <p style="margin:2px 0;"><strong>Dirección:</strong> {{ $printSnapshot['delivery_address'] ?? '' }}</p>
                    <p style="margin:2px 0;"><strong>Sector:</strong> {{ $printSnapshot['delivery_sector'] ?? '' }}</p>
                    @if (! empty($printSnapshot['delivery_reference']))
                        <p style="margin:2px 0;"><strong>Referencia:</strong> {{ $printSnapshot['delivery_reference'] }}</p>
                    @endif
                @endif

                <p style="margin:8px 0 2px;">
                    <strong>Destinatario:</strong> {{ $printSnapshot['recipient_name'] ?? '' }}
                    ({{ $printSnapshot['recipient_id_doc'] ?? '' }})
                </p>
                <p style="margin:2px 0;">
                    <strong>Teléfono:</strong> {{ $printSnapshot['recipient_phone'] ?? '' }}
                </p>
            </div>

            <div style="border-top:1px dashed #000;padding-top:8px;margin-bottom:10px;font-size:13px;text-align:center;">
                @if (! empty($printSnapshot['is_cod']))
                    <p style="font-weight:700;margin:0;">
                        COBRO CONTRA ENTREGA (COD)
                    </p>
                    <p style="font-weight:700;margin:2px 0 0;font-size:16px;">
                        ${{ number_format($printSnapshot['cod_amount_usd'] ?? 0, 2) }}
                    </p>
                @else
                    <p style="font-weight:700;margin:0;">
                        PAGADO
                    </p>
                    <p style="margin:2px 0 0;">
                        ${{ number_format($createdTotalUsd ?? 0, 2) }} / Bs. {{ number_format($createdTotalVes ?? 0, 2) }}
                    </p>
                    <p style="margin:2px 0 0;">
                        {{ \App\Livewire\Ally\PackageCreate::paymentMethodLabels()[$printSnapshot['payment_method'] ?? ''] ?? '—' }}
                    </p>
                @endif
            </div>

            <div style="text-align:center;font-size:11px;border-top:1px dashed #000;padding-top:8px;">
                <p style="margin:0;">¡Gracias por preferir Venexpress!</p>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- DOCUMENTO 2: ETIQUETA PARA PEGAR EN EL PAQUETE FÍSICO    --}}
        {{-- Oculto en pantalla; solo se muestra cuando data-print-target="label" --}}
        {{-- ======================================================= --}}
        <div id="printable-label" style="display:none;" wire:key="label-{{ $createdTrackingNumber }}">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #000;padding-bottom:6px;margin-bottom:10px;">
                <p style="font-size:20px;font-weight:700;margin:0;">VENEXPRESS</p>
                <p style="font-size:20px;font-weight:700;margin:0;">{{ $createdTrackingNumber }}</p>
            </div>

            <p style="margin:3px 0;font-size:13px;"><strong>Remitente:</strong> {{ $printSnapshot['sender_name'] ?? '' }}</p>
            <p style="margin:3px 0;font-size:13px;">
                <strong>Origen:</strong> {{ $printSnapshot['origin_city'] ?? '' }}{{ ! empty($printSnapshot['origin_state']) ? ', ' . $printSnapshot['origin_state'] : '' }}
            </p>
            <p style="margin:3px 0;font-size:13px;">
                <strong>Destinatario:</strong> {{ $printSnapshot['recipient_name'] ?? '' }} (Tel. {{ $printSnapshot['recipient_phone'] ?? '' }})
            </p>
            <p style="margin:3px 0;font-size:13px;">
                <strong>Destino:</strong>
                @if (! empty($printSnapshot['requires_delivery']))
                    {{ $printSnapshot['delivery_address'] ?? '' }} — {{ $printSnapshot['delivery_sector'] ?? '' }},
                @else
                    Retiro en agencia,
                @endif
                {{ $printSnapshot['destination_city'] ?? '' }}{{ ! empty($printSnapshot['destination_state']) ? ', ' . $printSnapshot['destination_state'] : '' }}
            </p>

            <div style="display:flex;justify-content:center;margin:20px 0;">
                <div id="qr-canvas-label" wire:ignore style="display:flex;justify-content:center;"></div>
            </div>

            <div style="display:flex;justify-content:space-between;font-size:13px;border-top:1px solid #000;padding-top:6px;">
                <span>{{ now()->format('d/m/Y') }}</span>
                <span>Peso: {{ $printSnapshot['physical_weight_kg'] ?? '' }} Kg.</span>
                <span style="font-weight:700;text-transform:uppercase;">{{ $printSnapshot['package_type'] ?? '' }}</span>
            </div>

            @if (! empty($printSnapshot['is_fragile']))
                <p style="text-align:center;font-weight:700;margin-top:10px;font-size:14px;">⚠ FRÁGIL</p>
            @endif

            @if (! empty($printSnapshot['is_cod']))
                <p style="text-align:center;font-weight:700;margin-top:10px;font-size:14px;background:#000;color:#fff;padding:6px;">
                    COD — COBRAR ${{ number_format($printSnapshot['cod_amount_usd'] ?? 0, 2) }} EN DESTINO
                </p>
            @endif

            @if ($createdSecurityHash)
                <p style="text-align:center;font-size:11px;margin-top:12px;">
                    Cód. Seg.: {{ $createdSecurityHash }}
                </p>
            @endif
        </div>

        @once
            <style>
                @media print {
                    body * {
                        visibility: hidden !important;
                    }

                    body[data-print-target="invoice"] #printable-invoice,
                    body[data-print-target="invoice"] #printable-invoice * {
                        visibility: visible !important;
                    }
                    body[data-print-target="invoice"] #printable-invoice {
                        display: block !important;
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        color: #000;
                    }

                    body[data-print-target="label"] #printable-label,
                    body[data-print-target="label"] #printable-label * {
                        visibility: visible !important;
                    }
                    body[data-print-target="label"] #printable-label {
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
                        <label class="text-sm text-slate-600">Tipo de documento</label>
                        <select wire:model.live="sender_doc_type"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            @foreach (\App\Livewire\Ally\PackageCreate::DOC_TYPE_LABELS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm text-slate-600">Número de documento</label>
                        <input type="text" wire:model.live.debounce.500ms="sender_doc_number" placeholder="12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('sender_doc_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('sender_id_doc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($senderCustomerFound)
                    <div class="mt-4 flex items-start justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3">
                        <div class="text-sm">
                            <p class="font-medium text-emerald-700">✓ Cliente encontrado</p>
                            <p class="mt-1 text-emerald-700">{{ $sender_name }}</p>
                            <p class="text-emerald-600">{{ $sender_phone }}{{ $sender_email ? ' · '.$sender_email : '' }}</p>
                        </div>
                        <button type="button" wire:click="openSenderCustomerModal"
                            class="shrink-0 text-xs font-medium text-emerald-700 underline hover:text-emerald-900">
                            Editar
                        </button>
                    </div>
                @elseif ($sender_name)
                    <div class="mt-4 flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-sm">
                            <p class="font-medium text-[#0F172A]">{{ $sender_name }}</p>
                            <p class="text-slate-500">{{ $sender_phone }}{{ $sender_email ? ' · '.$sender_email : '' }}</p>
                        </div>
                        <button type="button" wire:click="openSenderCustomerModal"
                            class="shrink-0 text-xs font-medium text-blue-900 underline hover:text-blue-700">
                            Editar
                        </button>
                    </div>
                @endif

                @error('sender_name') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                @error('sender_phone') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- DESTINATARIO --}}
            <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                <h3 class="font-display text-lg font-semibold text-[#0F172A] mb-4">
                    Destinatario
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-slate-600">Tipo de documento</label>
                        <select wire:model.live="recipient_doc_type"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                            @foreach (\App\Livewire\Ally\PackageCreate::DOC_TYPE_LABELS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm text-slate-600">Número de documento</label>
                        <input type="text" wire:model.live.debounce.500ms="recipient_doc_number" placeholder="12345678"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                        @error('recipient_doc_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('recipient_id_doc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($recipientCustomerFound)
                    <div class="mt-4 flex items-start justify-between gap-3 rounded-xl bg-emerald-50 px-4 py-3">
                        <div class="text-sm">
                            <p class="font-medium text-emerald-700">✓ Cliente encontrado</p>
                            <p class="mt-1 text-emerald-700">{{ $recipient_name }}</p>
                            <p class="text-emerald-600">{{ $recipient_phone }}{{ $recipient_email ? ' · '.$recipient_email : '' }}</p>
                        </div>
                        <button type="button" wire:click="openRecipientCustomerModal"
                            class="shrink-0 text-xs font-medium text-emerald-700 underline hover:text-emerald-900">
                            Editar
                        </button>
                    </div>
                @elseif ($recipient_name)
                    <div class="mt-4 flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <div class="text-sm">
                            <p class="font-medium text-[#0F172A]">{{ $recipient_name }}</p>
                            <p class="text-slate-500">{{ $recipient_phone }}{{ $recipient_email ? ' · '.$recipient_email : '' }}</p>
                        </div>
                        <button type="button" wire:click="openRecipientCustomerModal"
                            class="shrink-0 text-xs font-medium text-blue-900 underline hover:text-blue-700">
                            Editar
                        </button>
                    </div>
                @endif

                @error('recipient_name') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                @error('recipient_phone') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- MODAL: DATOS DE CLIENTE NUEVO — REMITENTE --}}
            @if ($showSenderCustomerModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                            Registrar datos del remitente
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            No encontramos este documento en el sistema. Completa sus datos para continuar.
                        </p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="text-sm text-slate-600">Nombre completo</label>
                                <input type="text" wire:model="sender_name" autofocus
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('sender_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Documento</label>
                                <input type="text" value="{{ $sender_id_doc }}" disabled
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Teléfono</label>
                                <input type="text" wire:model="sender_phone"
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('sender_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Correo (opcional)</label>
                                <input type="email" wire:model="sender_email"
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('sender_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showSenderCustomerModal', false)"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="button" wire:click="saveSenderCustomer"
                                class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                                Guardar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MODAL: DATOS DE CLIENTE NUEVO — DESTINATARIO --}}
            @if ($showRecipientCustomerModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                        <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                            Registrar datos del destinatario
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            No encontramos este documento en el sistema. Completa sus datos para continuar.
                        </p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="text-sm text-slate-600">Nombre completo</label>
                                <input type="text" wire:model="recipient_name" autofocus
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('recipient_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Documento</label>
                                <input type="text" value="{{ $recipient_id_doc }}" disabled
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-500">
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Teléfono</label>
                                <input type="text" wire:model="recipient_phone"
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('recipient_phone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm text-slate-600">Correo (opcional)</label>
                                <input type="email" wire:model="recipient_email"
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-900 focus:ring-blue-900">
                                @error('recipient_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" wire:click="$set('showRecipientCustomerModal', false)"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="button" wire:click="saveRecipientCustomer"
                                class="rounded-xl bg-blue-900 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
                                Guardar
                            </button>
                        </div>
                    </div>
                </div>
            @endif

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

            <div class="mt-6 flex items-center justify-end gap-3">
                <span
                    wire:loading
                    wire:target="destination_state,destination_city,physical_weight_kg,length_cm,width_cm,height_cm,package_type,is_fragile,has_insurance,declared_value_usd,requires_delivery"
                    class="text-xs text-slate-400"
                >
                    Calculando tarifa...
                </span>

                <button type="submit"
                    class="rounded-xl bg-blue-900 px-6 py-3 text-sm font-medium text-white disabled:opacity-50"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="save">Registrar guía</span>
                    <span wire:loading wire:target="save">Registrando...</span>
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
