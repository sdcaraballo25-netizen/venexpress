<div class="space-y-6">

@if (session()->has('success'))
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session()->has('error'))
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        {{ session('error') }}
    </div>
@endif

    {{-- Encabezado --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <a
                href="{{ route('repartidor.packages') }}"
                class="inline-flex items-center text-sm font-medium text-blue-900 hover:underline"
            >
                ← Volver a mis paquetes
            </a>

            <h2 class="mt-3 font-display text-2xl font-semibold text-[#0F172A]">
                Detalle de guía
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $package->tracking_number }}
            </p>
        </div>

        <span class="inline-flex w-fit rounded-full bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700">
            {{ $package->statusLabel() }}
        </span>

    </div>


    {{-- Información principal --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- Destinatario --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Destinatario
            </h3>

            <div class="mt-4 space-y-3">

                <div>
                    <p class="text-xs text-slate-400">
                        Nombre
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-700">
                        {{ $package->recipient_name }}
                    </p>
                </div>

                @if ($package->recipient_id_doc)
                    <div>
                        <p class="text-xs text-slate-400">
                            Documento
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $package->recipient_id_doc }}
                        </p>
                    </div>
                @endif

                @if ($package->recipient_phone)
                    <div>
                        <p class="text-xs text-slate-400">
                            Teléfono
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $package->recipient_phone }}
                        </p>
                    </div>
                @endif

            </div>

        </div>


        {{-- Origen y destino --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Ruta del paquete
            </h3>

            <div class="mt-4 space-y-4">

                <div>
                    <p class="text-xs text-slate-400">
                        Origen
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-700">
                        {{ $package->origin_city }}
                        @if ($package->origin_state)
                            · {{ $package->origin_state }}
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        Destino
                    </p>

                    <p class="mt-1 text-sm font-medium text-slate-700">
                        {{ $package->destination_city }}
                        @if ($package->destination_state)
                            · {{ $package->destination_state }}
                        @endif
                    </p>
                </div>

                @if ($package->distance_km)
                    <div>
                        <p class="text-xs text-slate-400">
                            Distancia
                        </p>

                        <p class="mt-1 text-sm text-slate-700">
                            {{ $package->distance_km }} km
                        </p>
                    </div>
                @endif

            </div>

        </div>

    </div>


    {{-- Dirección de entrega --}}
    @if ($package->requires_delivery)

        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

            <h3 class="font-display text-lg font-semibold text-[#0F172A]">
                Dirección de entrega
            </h3>

            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

                <div>
                    <p class="text-xs text-slate-400">
                        Dirección
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $package->delivery_address ?: 'No especificada' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        Sector
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $package->delivery_sector ?: 'No especificado' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        Referencia
                    </p>

                    <p class="mt-1 text-sm text-slate-700">
                        {{ $package->delivery_reference ?: 'Sin referencia' }}
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- Información del paquete --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h3 class="font-display text-lg font-semibold text-[#0F172A]">
            Información del paquete
        </h3>

        <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">

            <div>
                <p class="text-xs text-slate-400">
                    Tipo
                </p>

                <p class="mt-1 text-sm font-medium text-slate-700">
                    {{ $package->package_type === \App\Models\Package::TYPE_SOBRE ? 'Sobre' : 'Paquete' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">
                    Peso físico
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $package->physical_weight_kg }} kg
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">
                    Peso facturable
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $package->billable_weight_kg }} kg
                </p>
            </div>

            <div>
                <p class="text-xs text-slate-400">
                    Frágil
                </p>

                <p class="mt-1 text-sm text-slate-700">
                    {{ $package->is_fragile ? 'Sí' : 'No' }}
                </p>
            </div>

        </div>

    </div>


    {{-- COD --}}
    @if ($package->is_cod)

        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <h3 class="font-display text-lg font-semibold text-amber-900">
                Cobro contra entrega
            </h3>

            <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div>
                    <p class="text-xs text-amber-700">
                        Monto
                    </p>

                    <p class="mt-1 text-sm font-semibold text-amber-900">
                        ${{ number_format((float) $package->cod_amount_usd, 2) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-amber-700">
                        Estado
                    </p>

                    <p class="mt-1 text-sm font-medium text-amber-900">
                        {{ $package->cod_status === \App\Models\Package::COD_LIQUIDADO ? 'Liquidado' : 'Pendiente' }}
                    </p>
                </div>

            </div>

        </div>

    @endif


    {{-- Historial --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h3 class="font-display text-lg font-semibold text-[#0F172A]">
            Historial de la guía
        </h3>

        @if ($package->histories->isEmpty())

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <p class="text-sm text-slate-500">
                    No hay movimientos registrados.
                </p>
            </div>

        @else

            <div class="mt-5 space-y-4">

                @foreach ($package->histories->sortByDesc('created_at') as $history)

                    <div class="flex gap-3">

                        <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-900"></div>

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">

                                <p class="text-sm font-medium text-slate-700">
                                    {{ \App\Models\Package::STATUS_LABELS[$history->status] ?? $history->status }}
                                </p>

                                <p class="text-xs text-slate-400">
                                    {{ $history->created_at?->format('d/m/Y H:i') }}
                                </p>

                            </div>

                            @if ($history->location_description)
                                <p class="mt-1 text-xs text-slate-500">
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

        <h3 class="font-display text-lg font-semibold text-[#0F172A]">
            Acciones
        </h3>

        <div class="mt-4 flex flex-wrap gap-3">

            @if ($package->current_status === \App\Models\Package::STATUS_RECIBIDO_AGENCIA)

                <button
    type="button"
    wire:click="startDelivery"
    wire:loading.attr="disabled"
    wire:target="startDelivery"
    class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-60"
>
    <span wire:loading.remove wire:target="startDelivery">
        Iniciar entrega
    </span>

    <span wire:loading wire:target="startDelivery">
        Iniciando...
    </span>
</button>

            @elseif ($package->current_status !== \App\Models\Package::STATUS_ENTREGADO)

                <button
                    type="button"
                    class="rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800"
                >
                    Continuar entrega
                </button>

            @endif

            @if ($package->current_status !== \App\Models\Package::STATUS_ENTREGADO)

                <button
                    type="button"
                    class="rounded-xl border border-red-200 px-5 py-2.5 text-sm font-medium text-red-700 transition hover:bg-red-50"
                >
                    Reportar incidencia
                </button>

            @endif

            @if ($package->current_status === \App\Models\Package::STATUS_ENTREGADO)

                <div class="rounded-xl bg-emerald-50 px-5 py-2.5 text-sm font-medium text-emerald-700">
                    Entrega completada
                </div>

            @endif

        </div>

    </div>

</div>