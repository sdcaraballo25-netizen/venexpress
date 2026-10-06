<div class="space-y-6">
    <div>
        <h1 class="font-display text-2xl font-bold text-slate-900">Asignación a reparto</h1>
        <p class="mt-1 text-sm text-slate-500">Asigna únicamente paquetes de domicilio aceptados por el cliente a rutas en curso.</p>
    </div>

    @if($successMessage)<div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ $successMessage }}</div>@endif
    @if($errorMessage)<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $errorMessage }}</div>@endif

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form wire:submit.prevent="search" class="flex flex-col gap-3 sm:flex-row">
            <input wire:model="trackingNumber" class="flex-1 rounded-xl border border-slate-300 px-4 py-3" placeholder="Número de guía">
            <button class="rounded-xl bg-blue-900 px-5 py-3 font-semibold text-white">Buscar</button>
        </form>

        @if($package)
            <div class="mt-6 rounded-xl bg-slate-50 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <div class="font-tracking text-lg font-semibold">{{ $package->tracking_number }}</div>
                        <div class="text-sm text-slate-500">{{ $package->recipient_name }} · {{ $package->destination_city }}</div>
                    </div>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">{{ $package->statusLabel() }}</span>
                </div>

                <div class="mt-4 text-sm">
                    <strong>Entrega:</strong>
                    {{ $package->requires_delivery ? 'A domicilio' : 'Retiro en agencia' }}
                    @if ($package->delivery_attempts > 0)
                        · <strong>Intentos fallidos:</strong> {{ $package->delivery_attempts }}
                    @endif
                </div>

                @if ($package->isDelivered() && $package->receiver_name)
                    <div class="mt-3 text-sm text-slate-600">
                        <strong>Recibió:</strong> {{ $package->receiver_name }}{{ $package->receiver_id_doc ? ' ('.$package->receiver_id_doc.')' : '' }}
                        @if ($package->received_by_third_party)
                            · tercero autorizado
                        @endif
                    </div>
                @endif

                @php
                    $evidence = array_filter([
                        'Foto de la entrega' => $package->delivery_photo_path ? route('packages.delivery-evidence', $package) : null,
                        'Cédula de quien recibió' => $package->third_party_id_photo_path ? route('packages.third-party-id', $package) : null,
                        'Copia de la cédula del destinatario' => $package->recipient_id_copy_path ? route('packages.recipient-id-copy', $package) : null,
                        'Comprobante del cobro' => $package->cod_payment_proof_path ? route('packages.cod-payment-proof', $package) : null,
                    ]);
                @endphp

                @if ($evidence)
                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                        @foreach ($evidence as $label => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-100">{{ $label }}</a>
                        @endforeach
                    </div>
                @endif

                <form wire:submit.prevent="assign" class="mt-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-600">Ruta en curso</label>
                        <select wire:model="routeId" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                            <option value="">Selecciona una ruta</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}">
                                    {{ $route->name }} · {{ $route->city }} · {{ $route->driver?->user?->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('routeId')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button wire:loading.attr="disabled" wire:target="assign" class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white disabled:opacity-50">Asignar a reparto</button>
                </form>

                @if($package->driver_id)
                    <button wire:click="unassign" wire:loading.attr="disabled" wire:target="unassign" class="mt-3 w-full rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white disabled:opacity-50">
                        Retirar asignación
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
