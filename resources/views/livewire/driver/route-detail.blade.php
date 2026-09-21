<div class="space-y-6">

    {{-- Encabezado --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <a
                href="{{ route('repartidor.dashboard') }}"
                class="text-sm font-medium text-black hover:text-gray-700"
            >
                ← Resumen
            </a>

            <h1 class="mt-2 font-display text-2xl font-bold text-[#0F172A]">
                {{ $route->name }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ $route->city }}

                @if ($route->state)
                    · {{ $route->state }}
                @endif
            </p>
        </div>

        <div>
            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-black">

                @if ($route->status === \App\Models\Route::STATUS_DRAFT)
                    Borrador
                @elseif ($route->status === \App\Models\Route::STATUS_ASSIGNED)
                    Asignada
                @elseif ($route->status === \App\Models\Route::STATUS_IN_PROGRESS)
                    En curso
                @elseif ($route->status === \App\Models\Route::STATUS_COMPLETED)
                    Completada
                @elseif ($route->status === \App\Models\Route::STATUS_CANCELLED)
                    Cancelada
                @else
                    {{ $route->status }}
                @endif

            </span>
        </div>

    </div>


    {{-- Escanear (acceso principal mientras la ruta está en curso) --}}
    @if ($route->isInProgress())

        @if ($hubScanOperation)

            @php
                $hubScanTitle = match ($hubScanOperation) {
                    'collection' => 'RECOLECCIÓN EN ALIADO',
                    \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE => 'SALIDA DESDE HUB',
                    \App\Livewire\Driver\Support\HubDistributionPhase::ARRIVAL => 'RECEPCIÓN EN ALMACÉN',
                };

                $hubScanCta = match ($hubScanOperation) {
                    'collection' => 'Escanear recolección',
                    \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE => 'Escanear salida',
                    \App\Livewire\Driver\Support\HubDistributionPhase::ARRIVAL => 'Escanear recepción',
                };
            @endphp

            <a
                href="{{ route('repartidor.scanner') }}"
                class="group flex flex-col gap-4 rounded-2xl border border-black bg-black p-5 shadow-sm transition hover:bg-gray-800 sm:flex-row sm:items-center sm:justify-between"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-xl">
                        📷
                    </div>

                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">
                            {{ $hubScanTitle }}
                        </span>

                        @if ($hubScanOperation === 'collection')
                            <p class="mt-1.5 text-sm text-gray-300">
                                Siguiente parada:
                                <span class="font-semibold text-white">
                                    {{ $nextPendingStop?->ally?->business_name ?? '—' }}
                                </span>
                            </p>
                        @elseif ($hubScanOperation === \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE)
                            <p class="mt-1.5 text-sm text-gray-300">
                                Paquetes pendientes:
                                <span class="font-semibold text-white">{{ $hubScanPendingCount }}</span>
                            </p>
                        @else
                            <p class="mt-1.5 text-sm text-gray-300">
                                Almacén:
                                <span class="font-semibold text-white">{{ $hubScanWarehouseName ?? '—' }}</span>
                                · Por recibir:
                                <span class="font-semibold text-white">{{ $hubScanPendingCount }}</span>
                            </p>
                        @endif
                    </div>

                </div>

                <span class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-black transition group-hover:bg-amber-50">
                    {{ $hubScanCta }}
                </span>

            </a>

        @else

            {{-- Delivery u otro route_type no soportado en esta pantalla:
                 acceso genérico, sin tocar el vocabulario de Delivery. --}}
            <a
                href="{{ route('repartidor.scanner') }}"
                class="group flex flex-col gap-4 rounded-2xl border border-black bg-black p-5 shadow-sm transition hover:bg-gray-800 sm:flex-row sm:items-center sm:justify-between"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-xl">
                        📷
                    </div>

                    <div>
                        <h2 class="font-display text-base font-bold text-white">
                            Escanear paquetes
                        </h2>

                        <p class="mt-1 text-sm text-gray-300">
                            Continúa procesando las paradas de esta ruta.
                        </p>
                    </div>

                </div>

                <span class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-black transition group-hover:bg-amber-50">
                    Abrir escáner
                </span>

            </a>

        @endif

    @endif


    {{-- Tipo de ruta / Origen - Destino --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Tipo de ruta
                </p>

                <p class="mt-1 text-sm font-semibold text-[#0F172A]">

                    @if ($route->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                        Traslado a hub
                    @elseif ($route->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                        Distribución a almacén
                    @elseif ($route->route_type === \App\Models\Route::TYPE_DELIVERY)
                        Entregas
                    @else
                        {{ $route->route_type }}
                    @endif

                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                    Origen → Destino
                </p>

                <p class="mt-1 text-sm font-semibold text-[#0F172A]">

                    @if ($route->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                        Agencias aliadas → Hub Venexpress
                    @elseif ($route->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                        Hub Venexpress → Almacén destino
                    @elseif ($route->route_type === \App\Models\Route::TYPE_DELIVERY)
                        Hub / Almacén → Cliente final
                    @else
                        —
                    @endif

                </p>
            </div>

        </div>

    </div>


    {{-- Progreso --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <div class="mb-2 flex items-center justify-between">

            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Paradas completadas
            </span>

            <span class="text-sm font-bold text-[#0F172A]">
                {{ $visitedStopsCount }} de {{ $totalStopsCount }} · {{ $routeProgress }}%
            </span>

        </div>

        <div class="h-2.5 w-full rounded-full bg-slate-100">
            <div
                class="h-2.5 rounded-full bg-black transition-all duration-500"
                @style(['width' => $routeProgress . '%'])
            ></div>
        </div>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">

            <span>
                {{ $pendingStopsCount }} paradas pendientes
            </span>

            <span class="font-semibold text-[#0F172A]">

                Estado:

                @if ($route->status === \App\Models\Route::STATUS_ASSIGNED)
                    Asignada
                @elseif ($route->status === \App\Models\Route::STATUS_IN_PROGRESS)
                    En curso
                @elseif ($route->status === \App\Models\Route::STATUS_COMPLETED)
                    Completada
                @elseif ($route->status === \App\Models\Route::STATUS_CANCELLED)
                    Cancelada
                @else
                    {{ $route->status }}
                @endif

            </span>

        </div>

    </div>


    {{-- Paradas --}}
    <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5">

        <h2 class="font-display text-lg font-semibold text-[#0F172A]">
            Paradas
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Recorrido ordenado de la ruta.
        </p>

        @if ($route->stops->isEmpty())

            <p class="mt-4 text-sm text-slate-500">
                Esta ruta todavía no tiene paradas asignadas.
            </p>

        @else

            <div class="mt-4 space-y-3">

                @foreach ($route->stops as $index => $stop)

                    <div class="flex flex-col gap-3 rounded-xl border border-[#E2E8F0] p-4 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">

                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-sm font-bold text-black">
                                {{ $index + 1 }}
                            </div>

                            <div>
                                <p class="font-semibold text-sm text-[#0F172A]">
                                    {{ $stop->ally?->business_name ?? $stop->warehouse?->name ?? 'Parada' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $stop->ally?->city ?? $stop->warehouse?->city ?? $route->city }}
                                </p>

                                @if ($stop->packages_collected_count)
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $stop->packages_collected_count }} paquetes recolectados
                                    </p>
                                @endif
                            </div>

                        </div>

                        <div>

                            @if ($stop->status === \App\Models\RouteStop::STATUS_PENDING)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    Pendiente
                                </span>
                            @elseif ($stop->status === \App\Models\RouteStop::STATUS_VISITED)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Visitada
                                </span>
                            @elseif ($stop->status === \App\Models\RouteStop::STATUS_SKIPPED)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                    Omitida
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                                    {{ $stop->status }}
                                </span>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>
