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

            <h1 class="mt-2 font-display text-2xl font-bold text-[#0F172A]">
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#0F172A]">
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
                        Documento:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->sender_id_doc }}
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#0F172A]">
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
                        Documento:
                    </span>

                    <span class="text-slate-800">
                        {{ $package->recipient_id_doc }}
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
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
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


    {{-- Estado de aceptación --}}
    @if ($package->requires_delivery)

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#0F172A]">
                Estado de entrega
            </h2>

            <div class="mt-4">

                @if ($package->delivery_status === \App\Models\Package::DELIVERY_PENDING)

                    <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-700">
                        El cliente todavía no ha respondido la solicitud de entrega.
                    </div>

                @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_ACCEPTED)

                    <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
                        El cliente aceptó la entrega.
                        Puedes realizar la entrega y confirmarla al finalizar.
                    </div>

                @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_REJECTED)

                    <div class="rounded-xl bg-red-50 p-4 text-sm text-red-700">

                        <p class="font-semibold">
                            El cliente rechazó la entrega.
                        </p>

                        @if ($package->delivery_rejection_reason)
                            <p class="mt-1">
                                Motivo:
                                {{ $package->delivery_rejection_reason }}
                            </p>
                        @endif

                    </div>

                @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_COMPLETED)

                    <div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
                        Entrega completada correctamente.
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
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
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
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
            Acciones
        </h2>

        <div class="mt-4 flex flex-wrap gap-3">

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


            {{-- En tránsito --}}
            @elseif (
                $package->current_status
                === \App\Models\Package::STATUS_EN_TRANSITO_NACIONAL
            )

                @if (
                    $package->requires_delivery
                    && $package->delivery_status
                    === \App\Models\Package::DELIVERY_ACCEPTED
                )

                    <button
                        type="button"
                        wire:click="completeDelivery"
                        wire:loading.attr="disabled"
                        wire:target="completeDelivery"
                        wire:confirm="¿Confirmas que la entrega fue realizada correctamente?"
                        class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="completeDelivery">
                            Confirmar entrega
                        </span>

                        <span wire:loading wire:target="completeDelivery">
                            Confirmando...
                        </span>
                    </button>

                @elseif (
                    $package->requires_delivery
                    && $package->delivery_status
                    === \App\Models\Package::DELIVERY_PENDING
                )

                    <div class="rounded-xl bg-amber-50 px-5 py-3 text-sm font-medium text-amber-700">
                        Esperando aceptación del cliente
                    </div>

                @elseif (
                    $package->requires_delivery
                    && $package->delivery_status
                    === \App\Models\Package::DELIVERY_REJECTED
                )

                    <div class="rounded-xl bg-red-50 px-5 py-3 text-sm font-medium text-red-700">
                        Entrega rechazada por el cliente
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


            {{-- Incidencia --}}
            @if (
                $package->current_status
                !== \App\Models\Package::STATUS_ENTREGADO
            )

                <button
                    type="button"
                    class="rounded-xl border border-red-200 px-5 py-3 text-sm font-medium text-red-700 transition hover:bg-red-50"
                >
                    Reportar incidencia
                </button>

            @endif

        </div>

    </div>


    {{-- Cobro en destino (COD) pendiente de registrar --}}
    @if ($package->is_cod && $package->current_status === \App\Models\Package::STATUS_ENTREGADO && ! $package->cod_collected_at)

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
                wire:click="collectCod"
                wire:loading.attr="disabled"
                wire:target="collectCod"
                wire:confirm="¿Confirmas que recolectaste el cobro en destino?"
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
