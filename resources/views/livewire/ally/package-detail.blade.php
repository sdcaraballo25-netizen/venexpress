<div class="space-y-6">

    {{-- ENCABEZADO --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <a
                href="{{ route('ally.packages.index') }}"
                class="text-sm font-medium text-black hover:text-gray-700"
            >
                ← Volver a mis pedidos
            </a>

            <h1 class="mt-2 font-display text-2xl font-bold text-[#0F172A]">
                Detalle del pedido
            </h1>

            <p class="mt-1 text-sm font-tracking text-slate-500">
                {{ $package->tracking_number }}
            </p>

            @if (auth()->user()->isAliado() && $package->registeredBy)
                <p class="mt-1 text-xs text-slate-400">
                    Registrado por <span class="font-medium text-slate-600">{{ $package->registeredBy->name }}</span>
                </p>
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-2">

            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-black">
                {{ $this->statusLabel($package->current_status) }}
            </span>

            @if ($package->requires_delivery)
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-sm font-medium text-amber-700">
                    Delivery
                </span>
            @else
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700">
                    Retiro en oficina
                </span>
            @endif

            <a
                href="{{ route('packages.label', $package->id) }}"
                target="_blank"
                class="inline-flex rounded-full border border-black px-3 py-1 text-sm font-medium text-black"
            >
                Ver guía (PDF)
            </a>

        </div>

    </div>


    {{-- REMITENTE / DESTINATARIO --}}
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


    {{-- RUTA Y PAQUETE --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
            Ruta y paquete
        </h2>

        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">

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

            <div>
                <p class="text-xs text-slate-500">
                    Peso físico
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $package->physical_weight_kg }} kg
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">
                    Dimensiones
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $package->length_cm }}×{{ $package->width_cm }}×{{ $package->height_cm }} cm
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">
                    Peso volumétrico
                </p>

                <p class="mt-1 text-sm font-semibold text-slate-800">
                    {{ $package->volumetric_weight_kg }} kg
                </p>
            </div>

        </div>

    </div>


    {{-- TARIFA --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
            Tarifa
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <p class="text-xs text-slate-500">
                    Total (USD)
                </p>

                <p class="mt-1 text-lg font-bold text-slate-800">
                    US$ {{ number_format((float) $package->total_price_usd, 2) }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-500">
                    Total (VES)
                </p>

                <p class="mt-1 text-lg font-bold text-slate-800">
                    Bs. {{ number_format((float) $package->total_price_ves, 2) }}
                </p>
            </div>

        </div>

    </div>


    {{-- DIRECCIÓN DE ENTREGA --}}
    @if ($package->requires_delivery)

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h2 class="font-display text-lg font-semibold text-black">
                Entrega a domicilio
            </h2>

            <div class="mt-4 space-y-3 text-sm text-black">

                @if ($package->delivery_address)
                    <div>
                        <p class="text-xs font-medium text-amber-700">
                            Dirección
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_address }}
                        </p>
                    </div>
                @endif

                @if ($package->delivery_sector)
                    <div>
                        <p class="text-xs font-medium text-amber-700">
                            Sector
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_sector }}
                        </p>
                    </div>
                @endif

                @if ($package->delivery_reference)
                    <div>
                        <p class="text-xs font-medium text-amber-700">
                            Referencia
                        </p>

                        <p class="mt-1">
                            {{ $package->delivery_reference }}
                        </p>
                    </div>
                @endif

                <div>
                    <p class="text-xs font-medium text-amber-700">
                        Estado de la entrega
                    </p>

                    <p class="mt-1">
                        @if ($package->delivery_status === \App\Models\Package::DELIVERY_PENDING)
                            Esperando aceptación del cliente.
                        @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_ACCEPTED)
                            El cliente aceptó la entrega.
                        @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_REJECTED)
                            El cliente rechazó la entrega.
                            @if ($package->delivery_rejection_reason)
                                Motivo: {{ $package->delivery_rejection_reason }}
                            @endif
                        @elseif ($package->delivery_status === \App\Models\Package::DELIVERY_COMPLETED)
                            Entrega completada correctamente.
                        @else
                            Sin información de entrega todavía.
                        @endif
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- COD --}}
    @if ($package->is_cod)

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h2 class="font-display text-lg font-semibold text-amber-900">
                Cobro contra entrega (COD)
            </h2>

            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <p class="text-xs text-amber-700">
                        Monto
                    </p>

                    <p class="mt-1 text-lg font-bold text-amber-900">
                        US$ {{ number_format((float) $package->cod_amount_usd, 2) }}
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


    {{-- REPARTIDOR --}}
    @if ($package->driver)

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h2 class="font-display text-lg font-semibold text-[#0F172A]">
                Repartidor asignado
            </h2>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <p class="text-xs text-slate-500">
                        Nombre
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ $package->driver->user?->name ?? 'No disponible' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">
                        Teléfono
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-800">
                        {{ $package->driver->phone ?? 'No disponible' }}
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- HISTORIAL --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
            Historial de movimientos
        </h2>

        @if ($package->histories->isEmpty())

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">
                    No hay movimientos registrados.
                </p>
            </div>

        @else

            <div class="mt-5 space-y-5">

                @foreach ($package->histories as $history)

                    <div class="flex gap-3">

                        <div class="mt-1 h-3 w-3 shrink-0 rounded-full bg-black"></div>

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

</div>
