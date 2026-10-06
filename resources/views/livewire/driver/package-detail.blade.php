<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <a
                href="{{ route('repartidor.packages') }}"
                class="text-sm font-medium text-blue-700 hover:text-blue-900"
            >
                ← Volver a mis paquetes
            </a>

            <h1 class="mt-2 font-display text-2xl font-bold text-[#111111]">
                Detalle de guía
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $package->tracking_number }}
            </p>
        </div>

        <div>
            <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                {{ $package->statusLabel() }}
            </span>

            <a
                href="{{ route('packages.label', $package->id) }}"
                target="_blank"
                class="ml-2 inline-flex rounded-full border border-blue-700 px-3 py-1 text-sm font-medium text-blue-700"
            >
                Ver guía (PDF)
            </a>
        </div>

    </div>


    {{-- Mensajes --}}
    @if (session('success'))

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
            {{ session('success') }}
        </div>

    @endif


    @if (session('error'))

        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>

    @endif


    {{-- Información de la guía --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Remitente --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#111111]">
                Remitente
            </h2>

            <div class="mt-4 space-y-2 text-sm">

                <p>
                    <span class="font-medium text-slate-500">
                        Nombre:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->sender_name }}
                    </span>
                </p>

                <p>
                    <span class="font-medium text-slate-500">
                        Teléfono:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->sender_phone }}
                    </span>
                </p>

            </div>

        </div>


        {{-- Destinatario --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#111111]">
                Destinatario
            </h2>

            <div class="mt-4 space-y-2 text-sm">

                <p>
                    <span class="font-medium text-slate-500">
                        Nombre:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->recipient_name }}
                    </span>
                </p>

                <p>
                    <span class="font-medium text-slate-500">
                        Teléfono:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->recipient_phone }}
                    </span>
                </p>

            </div>

        </div>

    </div>


    {{-- Ruta --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#111111]">
            Ruta
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">

            <div>
                <p class="text-xs text-slate-500">
                    Origen
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $package->origin_city }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">
                    Destino
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $package->destination_city }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">
                    Tipo
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ ucfirst($package->package_type) }}
                </p>
            </div>

        </div>

    </div>


    {{-- Dirección de entrega --}}
    @if ($package->requires_delivery)

        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">

            <h2 class="font-display text-lg font-semibold text-blue-900">
                Entrega a domicilio
            </h2>

            <div class="mt-4 space-y-3 text-sm text-blue-900">

                @if ($package->delivery_address)
                    <div>
                        <p class="text-xs font-medium text-blue-700">
                            Dirección
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_address }}
                        </p>
                    </div>
                @endif

                @if ($package->delivery_sector)
                    <div>
                        <p class="text-xs font-medium text-blue-700">
                            Sector
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_sector }}
                        </p>
                    </div>
                @endif

                @if ($package->delivery_reference)
                    <div>
                        <p class="text-xs font-medium text-blue-700">
                            Referencia
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_reference }}
                        </p>
                    </div>
                @endif

            </div>

        </div>

    @endif


    {{-- Estado de entrega --}}
    @if ($package->requires_delivery && in_array($package->current_status, [\App\Models\Package::STATUS_EN_RUTA, \App\Models\Package::STATUS_ENTREGADO], true))

        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#111111]">
                Estado de entrega
            </h2>

            <div class="mt-4">

                @if ($package->current_status === \App\Models\Package::STATUS_EN_RUTA)

                    @if ($package->acceptsDeliveryPin())
                        <div class="rounded-xl bg-blue-50 p-4 text-sm text-blue-800">
                            El destinatario recibió por correo un <strong>PIN de entrega</strong>. Pídeselo cuando le entregues el paquete.
                            Si no lo tiene, confirma con su cédula y una foto de la entrega.
                        </div>
                    @else
                        <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
                            Esta entrega no tiene PIN disponible: confirma con la cédula del destinatario y una foto de la entrega.
                        </div>
                    @endif

                @else

                    <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
                        Entrega completada
                        {{ $package->delivery_confirmation_method === \App\Models\Package::DELIVERY_CONFIRMATION_PIN ? '(verificada con PIN).' : '(verificada con cédula y foto).' }}
                    </div>

                @endif

            </div>

        </div>

    @endif


    {{-- COD --}}
    @if ($package->is_cod)

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h2 class="font-display text-lg font-semibold text-amber-900">
                Cobro contra entrega
            </h2>

            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <p class="text-xs text-amber-700">
                        Monto
                    </p>

                    <p class="mt-1 text-lg font-bold text-amber-900">
                        ${{ number_format((float) $package->cod_amount_usd, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-amber-700">
                        Estado
                    </p>

                    <p class="mt-1 text-sm font-semibold text-amber-900">
                        {{ $package->cod_status === \App\Models\Package::COD_LIQUIDADO
                            ? 'Liquidado'
                            : 'Pendiente' }}
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- Historial --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#111111]">
            Historial de la guía
        </h2>

        @if ($package->histories->isEmpty())

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">
                    No hay movimientos registrados.
                </p>
            </div>

        @else

            <div class="mt-5 space-y-5">

                @foreach ($package->histories->sortByDesc('created_at') as $history)

                    <div class="flex gap-3">

                        <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-blue-900"></div>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                <p class="text-sm font-semibold text-slate-800">
                                    {{ $history->eventTypeLabel() }}
                                </p>

                                <p class="text-xs text-slate-400">
                                    {{ $history->created_at?->format('d/m/Y H:i') }}
                                </p>

                            </div>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ \App\Models\Package::STATUS_LABELS[$history->status] ?? $history->status }}
                            </p>

                            @if ($history->origin_location || $history->destination_location)

                                <div class="mt-2 text-xs text-slate-500">

                                    @if ($history->origin_location)
                                        <p>
                                            <span class="font-medium">
                                                Origen:
                                            </span>

                                            {{ $history->origin_location }}
                                        </p>
                                    @endif

                                    @if ($history->destination_location)
                                        <p>
                                            <span class="font-medium">
                                                Destino:
                                            </span>

                                            {{ $history->destination_location }}
                                        </p>
                                    @endif

                                </div>

                            @endif

                            @if ($history->location_description)

                                <p class="mt-2 text-xs text-slate-500">
                                    {{ $history->location_description }}
                                </p>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>


    {{-- Acciones --}}
    <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#111111]">
            Acciones
        </h2>

        <div class="mt-4 flex flex-wrap gap-3">

            @if ($isHub)

                {{-- Para HUB, el paquete se procesa por escaneo dentro
                     de la ruta: no aplican las acciones de Delivery. --}}
                <div class="rounded-xl bg-slate-50 px-5 py-3 text-sm font-medium text-slate-600">
                    Este paquete se gestiona escaneándolo dentro de tu ruta.
                </div>

                <a
                    href="{{ $activeRouteId ? route('repartidor.route-detail', $activeRouteId) : route('repartidor.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-medium text-[#111111] transition hover:border-blue-300 hover:bg-blue-50"
                >
                    Volver a mi ruta
                </a>

                <a
                    href="{{ route('repartidor.scanner') }}"
                    class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-800"
                >
                    Escanear paquetes
                </a>

            @else

            {{-- Pendiente de recolección --}}
            @if (
                $package->current_status
                === \App\Models\Package::STATUS_RECIBIDO_AGENCIA
            )

                <div class="rounded-xl bg-amber-50 px-5 py-3 text-sm font-medium text-amber-700">
                    Pendiente de recolección
                </div>


            {{-- Recolectado --}}
            @elseif (
                $package->current_status
                === \App\Models\Package::STATUS_RECOLECTADO_VENEXPRESS
            )

                <button
                    type="button"
                    wire:click="startDelivery"
                    wire:loading.attr="disabled"
                    wire:target="startDelivery"
                    class="rounded-xl bg-blue-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="startDelivery">
                        Iniciar entrega
                    </span>

                    <span wire:loading wire:target="startDelivery">
                        Iniciando...
                    </span>
                </button>


            {{-- En ruta de entrega --}}
            @elseif (
                $package->current_status
                === \App\Models\Package::STATUS_EN_RUTA
            )

                {{-- Misma condición que el backend (PackageService::completeDelivery):
                     asignado a este repartidor, a domicilio y en ruta. --}}
                @if ($package->requires_delivery)

                    @php
                        $withPin = $package->acceptsDeliveryPin() && ! $deliverWithoutPin;
                    @endphp

                    <div class="w-full space-y-4">

                    @if ($package->is_cod && $package->cod_collected_at)
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                            Este pedido contra entrega ya figura como pagado: no cobres al entregar.
                        </div>
                    @endif

                    @if ($package->is_cod && ! $package->cod_collected_at)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 space-y-3">
                            <p class="text-sm font-semibold text-amber-800">
                                Cobro contra entrega: US$ {{ number_format((float) $package->cod_amount_usd, 2) }}
                            </p>
                            <p class="text-xs text-amber-700">No entregues el paquete sin registrar el pago.</p>

                            <div>
                                <label for="codPaymentMethod" class="text-sm font-medium text-slate-700">Forma de pago</label>
                                <select
                                    id="codPaymentMethod"
                                    wire:model.live="codPaymentMethod"
                                    class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                                >
                                    <option value="">Selecciona cómo te cancelaron...</option>
                                    @foreach (\App\Models\Package::PAYMENT_METHOD_LABELS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('codPaymentMethod') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if ($codPaymentMethod !== '' && ! str_starts_with($codPaymentMethod, 'efectivo'))
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="codPaymentReference" class="text-sm font-medium text-slate-700">
                                            Número de referencia{{ in_array($codPaymentMethod, \App\Models\Package::PAYMENT_METHODS_REQUIRING_REFERENCE, true) ? '' : ' (opcional)' }}
                                        </label>
                                        <input id="codPaymentReference" type="text" wire:model="codPaymentReference" autocomplete="off"
                                               class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        @error('codPaymentReference') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="codPaymentProof" class="text-sm font-medium text-slate-700">Comprobante (opcional)</label>
                                        <input id="codPaymentProof" type="file" accept="image/*" wire:model="codPaymentProof"
                                               class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:text-sm file:font-medium">
                                        <p wire:loading wire:target="codPaymentProof" class="mt-1 text-xs text-slate-500">Subiendo comprobante...</p>
                                        @error('codPaymentProof') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="receiverName" class="text-sm font-medium text-slate-700">Nombre de quien recibe</label>
                            <input id="receiverName" type="text" wire:model="receiverName" autocomplete="off"
                                   class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            @error('receiverName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="receiverPhone" class="text-sm font-medium text-slate-700">Teléfono (opcional)</label>
                            <input id="receiverPhone" type="tel" wire:model="receiverPhone" autocomplete="off"
                                   class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            @error('receiverPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @if ($package->acceptsDeliveryPin())
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" wire:model.live="deliverWithoutPin" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                            El destinatario no tiene el PIN
                        </label>
                    @endif

                    @if ($withPin)
                        <div>
                            <label for="deliveryPin" class="text-sm font-medium text-slate-700">PIN de entrega</label>
                            <input id="deliveryPin" type="text" inputmode="numeric" maxlength="6" wire:model="deliveryPin" autocomplete="off" placeholder="6 dígitos"
                                   class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-lg tracking-[0.4em] shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:w-48">
                            @error('deliveryPin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="receiverIdDoc" class="text-sm font-medium text-slate-700">Cédula del destinatario</label>
                                <input id="receiverIdDoc" type="text" wire:model="receiverIdDoc" autocomplete="off" placeholder="V-12345678"
                                       class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                @error('receiverIdDoc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="deliveryPhoto" class="text-sm font-medium text-slate-700">Foto de la entrega</label>
                                <input id="deliveryPhoto" type="file" accept="image/*" capture="environment" wire:model="deliveryPhoto"
                                       class="mt-1 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium">
                                <p wire:loading wire:target="deliveryPhoto" class="mt-1 text-xs text-slate-500">Subiendo foto...</p>
                                @error('deliveryPhoto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    <button
                        type="button"
                        wire:loading.attr="disabled"
                        wire:target="completeDelivery"
                        @click.prevent="$store.confirm.open({
                            message: '¿Confirmas que la entrega fue realizada correctamente?',
                            confirmText: 'Confirmar entrega',
                            variant: 'primary',
                            onConfirm: () => $wire.completeDelivery(),
                        })"
                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="completeDelivery">
                            Confirmar entrega
                        </span>

                        <span wire:loading wire:target="completeDelivery">
                            Confirmando...
                        </span>
                    </button>

                    </div>

                @else

                    <div class="rounded-xl bg-blue-50 px-5 py-3 text-sm font-medium text-blue-700">
                        Entrega en curso
                    </div>

                @endif


            {{-- Entregado --}}
            @elseif (
                $package->current_status
                === \App\Models\Package::STATUS_ENTREGADO
            )

                <div class="rounded-xl bg-emerald-50 px-5 py-3 text-sm font-medium text-emerald-700">
                    Entrega completada
                </div>

            @endif

            @endif


            {{-- Incidencia (p. ej. no se pudo entregar). Antes este botón
                 no hacía nada en el panel web. --}}
            @if (
                $package->current_status
                !== \App\Models\Package::STATUS_ENTREGADO
                && ! $showIncidentForm
            )

                <button
                    type="button"
                    wire:click="$set('showIncidentForm', true)"
                    class="rounded-xl border border-red-200 px-5 py-3 text-sm font-medium text-red-700 transition hover:bg-red-50"
                >
                    Reportar incidencia
                </button>

            @endif

        </div>

        @if ($showIncidentForm && $package->current_status !== \App\Models\Package::STATUS_ENTREGADO)

            <form wire:submit="reportIncident" class="mt-5 space-y-3 rounded-xl border border-red-200 bg-red-50/40 p-4">

                <p class="text-sm font-semibold text-red-800">
                    Reportar incidencia
                </p>
                <p class="text-xs text-red-700">
                    El estado del paquete no cambia: el equipo administrativo revisará el reporte y te indicará cómo seguir.
                </p>

                <div>
                    <label for="incidentType" class="text-sm font-medium text-slate-700">Motivo</label>
                    <select id="incidentType" wire:model="incidentType"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-red-600 focus:ring-red-600">
                        <option value="">Selecciona...</option>
                        @foreach (\App\Services\IncidentService::DRIVER_TYPE_LABELS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('incidentType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="incidentDescription" class="text-sm font-medium text-slate-700">Descripción</label>
                    <textarea id="incidentDescription" wire:model="incidentDescription" rows="3" maxlength="1000"
                              class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-red-600 focus:ring-red-600"></textarea>
                    @error('incidentDescription') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="reportIncident"
                            class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                        <span wire:loading.remove wire:target="reportIncident">Enviar reporte</span>
                        <span wire:loading wire:target="reportIncident">Enviando...</span>
                    </button>
                    <button type="button" wire:click="$set('showIncidentForm', false)"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-white">
                        Cancelar
                    </button>
                </div>

            </form>

        @endif

    </div>


    {{-- Cobro en destino (COD) pendiente de registrar --}}
    @if (! $isHub && $package->is_cod && $package->current_status === \App\Models\Package::STATUS_ENTREGADO && ! $package->cod_collected_at)

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h3 class="font-display text-lg font-semibold text-amber-900">
                Cobro en destino (COD)
            </h3>

            <p class="mt-1 text-sm text-amber-800">
                Monto:
                <strong>
                    US$ {{ number_format((float) $package->cod_amount_usd, 2) }}
                </strong>
            </p>

            <button
                type="button"
                wire:loading.attr="disabled"
                wire:target="collectCod"
                @click.prevent="$store.confirm.open({
                    message: '¿Confirmas que recolectaste el cobro en destino?',
                    confirmText: 'Registrar cobro',
                    variant: 'primary',
                    onConfirm: () => $wire.collectCod(),
                })"
                class="mt-4 rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="collectCod">
                    Registrar cobro COD
                </span>

                <span wire:loading wire:target="collectCod">
                    Registrando...
                </span>
            </button>

        </div>

    @endif

</div>
