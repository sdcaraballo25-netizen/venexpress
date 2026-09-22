<div class="space-y-6 font-sans">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <p class="text-sm font-medium text-blue-700">
                Panel del repartidor
            </p>

            <h1 class="mt-1 font-display text-3xl font-bold tracking-tight text-[#111111]">
                Hola, {{ $driver->user?->name ?? auth()->user()->name }} 👋
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">

            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Panel operativo
            </span>

        </div>

    </div>


    @if ($isFirstTimeDriver)

        {{-- =========================================================
             BIENVENIDA (primera vez, sin historial todavía)
        ========================================================== --}}
        <div class="rounded-3xl bg-gradient-to-br from-blue-900 to-blue-950 p-8 text-white shadow-sm">

            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">

                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-blue-100">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        Cuenta aprobada
                    </span>

                    <h2 class="mt-4 font-display text-2xl font-bold">
                        ¡Bienvenido a Venexpress, {{ $driver->user?->name ?? auth()->user()->name }}!
                    </h2>

                    <p class="mt-2 text-sm text-blue-100">
                        Todavía no tienes paquetes ni rutas asignadas — es normal, es tu primera vez aquí.
                        En cuanto un administrador te asigne una ruta, aparecerá abajo y podrás empezar a escanear.
                    </p>
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                    <a href="{{ route('repartidor.app-download') }}" wire:navigate
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-950 hover:bg-blue-50 transition">
                        <i class="fa-solid fa-download"></i>
                        Descargar app
                    </a>
                    <a href="{{ route('repartidor.help') }}" wire:navigate
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/30 px-5 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-circle-question"></i>
                        Ver ayuda
                    </a>
                </div>

            </div>

        </div>

    @endif


    @if ($isHub)

        {{-- =========================================================
             MI RUTA ACTUAL (HUB)
        ========================================================== --}}
        <div
            class="rounded-3xl border border-[#E5E5E0] bg-white p-6 shadow-sm"
            @if (! $activeRoute) wire:poll.15s @endif
        >

            <div class="flex flex-col gap-5">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                    <div>

                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                            @if ($activeRoute)
                                Mi ruta actual
                            @else
                                Rutas disponibles
                            @endif
                        </p>

                        @if ($activeRoute)

                            <h2 class="mt-1 font-display text-2xl font-bold text-[#111111]">
                                {{ $activeRoute->name }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">

                                @if ($activeRoute->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                    Traslado a hub
                                @elseif ($activeRoute->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                                    Distribución a almacén
                                @elseif ($activeRoute->route_type === \App\Models\Route::TYPE_DELIVERY)
                                    Entregas
                                @else
                                    {{ $activeRoute->route_type }}
                                @endif

                                ·

                                @if ($activeRoute->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                    Agencias aliadas → Hub Venexpress
                                @elseif ($activeRoute->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                                    Hub Venexpress → Almacén destino
                                @else
                                    Hub / Almacén → Cliente final
                                @endif

                            </p>

                        @elseif ($availableRoutes->isNotEmpty())

                            @php $availableRoutesCount = $availableRoutes->count(); @endphp

                            <div class="mt-2 flex items-center gap-3">

                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 font-display text-lg font-bold text-blue-900">
                                    {{ $availableRoutesCount }}
                                </span>

                                <div>

                                    <h2 class="font-display text-lg font-bold leading-tight text-[#111111]">
                                        {{ $availableRoutesCount === 1 ? 'Ruta compatible esperando' : 'Rutas compatibles esperando' }}
                                    </h2>

                                    <p class="mt-0.5 max-w-xl text-sm leading-snug text-slate-500">
                                        Todavía no tienes una ruta activa. Toma una de las
                                        rutas compatibles de abajo para comenzar tu operación.
                                    </p>

                                </div>

                            </div>

                        @else

                            <h2 class="mt-1 font-display text-2xl font-bold text-[#111111]">
                                Sin rutas disponibles
                            </h2>

                            <p class="mt-1 max-w-xl text-sm text-slate-500">
                                No tienes una ruta activa y no hay rutas compatibles
                                por el momento. Vuelve a revisar más tarde.
                            </p>

                        @endif

                    </div>


                    @if ($activeRoute)

                        <div class="flex flex-wrap items-center gap-2">

                            @if ($activeRoute->status === \App\Models\Route::STATUS_ASSIGNED)

                                <button
                                    type="button"
                                    wire:click="startRoute"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="startRoute">
                                        Iniciar ruta
                                    </span>

                                    <span wire:loading wire:target="startRoute">
                                        Iniciando...
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    wire:loading.attr="disabled"
                                    @click.prevent="$store.confirm.open({
                                        message: '¿Confirmas que quieres liberar esta ruta? Volverá a estar disponible para otros repartidores.',
                                        confirmText: 'Liberar ruta',
                                        variant: 'warning',
                                        onConfirm: () => $wire.releaseRoute(),
                                    })"
                                    class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-semibold text-[#111111] transition hover:border-amber-300 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="releaseRoute">
                                        Liberar ruta
                                    </span>

                                    <span wire:loading wire:target="releaseRoute">
                                        Liberando...
                                    </span>
                                </button>

                            @elseif ($activeRoute->status === \App\Models\Route::STATUS_IN_PROGRESS)

                                <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                    Ruta en curso
                                </span>

                                <button
                                    type="button"
                                    wire:loading.attr="disabled"
                                    @click.prevent="$store.confirm.open({
                                        message: '¿Confirmas que quieres finalizar esta ruta? Las paradas pendientes quedarán marcadas como omitidas.',
                                        confirmText: 'Finalizar ruta',
                                        variant: 'danger',
                                        onConfirm: () => $wire.completeRoute(),
                                    })"
                                    class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-semibold text-[#111111] transition hover:border-red-300 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="completeRoute">
                                        Finalizar ruta
                                    </span>

                                    <span wire:loading wire:target="completeRoute">
                                        Finalizando...
                                    </span>
                                </button>

                            @endif

                            <a
                                href="{{ route('repartidor.route-detail', $activeRoute->id) }}"
                                class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-semibold text-[#111111] transition hover:border-blue-300 hover:bg-blue-50"
                            >
                                Ver detalles
                            </a>

                        </div>

                    @endif

                </div>


                @if ($activeRoute)

                    {{-- PROGRESO --}}
                    <div>

                        <div class="mb-2 flex items-center justify-between">

                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Paradas completadas
                            </span>

                            <span class="text-sm font-bold text-[#111111]">
                                {{ $visitedStopsCount }} de {{ $routeStopsCount }} · {{ $routeProgress }}%
                            </span>

                        </div>

                        <div class="h-2.5 w-full rounded-full bg-slate-100">
                            <div
                                class="h-2.5 rounded-full bg-blue-700 transition-all duration-500"
                                @style(['width' => $routeProgress . '%'])
                            ></div>
                        </div>

                        <div class="mt-2 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">

                            <span>
                                {{ $pendingStopsCount }} paradas pendientes
                                · {{ $routePackagesProcessed }} paquetes procesados
                            </span>

                            <span class="font-semibold text-[#111111]">

                                Estado:

                                @if ($activeRoute->status === \App\Models\Route::STATUS_ASSIGNED)
                                    Asignada
                                @elseif ($activeRoute->status === \App\Models\Route::STATUS_IN_PROGRESS)
                                    En curso
                                @else
                                    {{ $activeRoute->status }}
                                @endif

                            </span>

                        </div>

                    </div>


                    {{-- PRÓXIMA PARADA --}}
                    @if ($nextPendingStop)

                        <div class="rounded-2xl bg-slate-50 p-4">

                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Próxima parada
                            </p>

                            <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                <div>
                                    <p class="font-semibold text-sm text-[#111111]">
                                        {{ $nextPendingStop->ally?->business_name ?? $nextPendingStop->warehouse?->name ?? 'Parada' }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $nextPendingStop->ally?->city ?? $nextPendingStop->warehouse?->city ?? $activeRoute->city }}

                                        @if ($nextPendingStop->packages_collected_count)
                                            · {{ $nextPendingStop->packages_collected_count }} paquetes
                                        @endif
                                    </p>
                                </div>

                                <span class="inline-flex w-fit items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-600"></span>
                                    Pendiente
                                </span>

                            </div>

                        </div>

                    @endif


                @elseif ($availableRoutes->isNotEmpty())

                    {{-- RUTAS DISPONIBLES --}}
                    <div class="space-y-3">

                        @foreach ($availableRoutes as $route)

                            <div class="flex flex-col gap-3 rounded-xl border border-[#E5E5E0] p-4 sm:flex-row sm:items-center sm:justify-between">

                                <div>

                                    <p class="font-semibold text-sm text-[#111111]">
                                        {{ $route->name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $route->city }}

                                        @if ($route->state)
                                            · {{ $route->state }}
                                        @endif

                                        ·

                                        @if ($route->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                            Traslado a hub
                                        @elseif ($route->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                                            Distribución a almacén
                                        @elseif ($route->route_type === \App\Models\Route::TYPE_DELIVERY)
                                            Entregas
                                        @else
                                            {{ $route->route_type }}
                                        @endif

                                        · {{ $route->stops->count() }} paradas
                                    </p>

                                </div>

                                <button
                                    type="button"
                                    wire:click="claimRoute({{ $route->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="claimRoute({{ $route->id }})"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="claimRoute({{ $route->id }})">
                                        Tomar ruta
                                    </span>

                                    <span wire:loading wire:target="claimRoute({{ $route->id }})">
                                        Tomando...
                                    </span>
                                </button>

                            </div>

                        @endforeach

                    </div>

                @endif


                {{-- MENSAJES --}}
                @if (session('routeSuccess'))

                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('routeSuccess') }}
                    </div>

                @endif


                @if (session('routeError'))

                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('routeError') }}
                    </div>

                @endif

            </div>

        </div>


        {{-- =========================================================
             ESCANEAR (acción principal HUB — específica a la fase)
        ========================================================== --}}
        @if ($hubScanOperation)

            @php
                $hubScanTitle = match ($hubScanOperation) {
                    'collection' => 'RECOLECCIÓN EN ALIADO',
                    \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE => 'SALIDA DESDE HUB',
                    \App\Livewire\Driver\Support\HubDistributionPhase::ARRIVAL => 'RECEPCIÓN EN ALMACÉN',
                };

                $hubScanSubtitle = match ($hubScanOperation) {
                    'collection' => 'Aliado → HUB',
                    \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE => 'HUB → Almacén destino',
                    \App\Livewire\Driver\Support\HubDistributionPhase::ARRIVAL => 'Llegada al almacén destino',
                };

                $hubScanCta = match ($hubScanOperation) {
                    'collection' => 'Escanear recolección',
                    \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE => 'Escanear salida',
                    \App\Livewire\Driver\Support\HubDistributionPhase::ARRIVAL => 'Escanear recepción',
                };
            @endphp

            <a
                href="{{ route('repartidor.scanner') }}"
                class="group flex flex-col gap-4 rounded-3xl border border-blue-900 bg-blue-900 p-6 shadow-sm transition hover:bg-blue-800 sm:flex-row sm:items-center sm:justify-between"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-2xl">
                        📷
                    </div>

                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-white">
                            {{ $hubScanTitle }}
                        </span>

                        <h2 class="mt-1.5 font-display text-lg font-bold text-white">
                            {{ $hubScanSubtitle }}
                        </h2>

                        @if ($hubScanOperation === 'collection')
                            <p class="mt-1 text-sm text-blue-100">
                                Siguiente parada:
                                <span class="font-semibold text-white">
                                    {{ $nextPendingStop?->ally?->business_name ?? '—' }}
                                </span>
                            </p>
                        @elseif ($hubScanOperation === \App\Livewire\Driver\Support\HubDistributionPhase::DEPARTURE)
                            <p class="mt-1 text-sm text-blue-100">
                                Paquetes pendientes:
                                <span class="font-semibold text-white">
                                    {{ $hubScanPendingCount }}
                                </span>
                            </p>
                        @else
                            <p class="mt-1 text-sm text-blue-100">
                                Almacén:
                                <span class="font-semibold text-white">
                                    {{ $hubScanWarehouseName ?? '—' }}
                                </span>
                                · Paquetes por recibir:
                                <span class="font-semibold text-white">
                                    {{ $hubScanPendingCount }}
                                </span>
                            </p>
                        @endif
                    </div>

                </div>

                <span class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-900 transition group-hover:bg-blue-50">
                    {{ $hubScanCta }}
                </span>

            </a>

        @else

            {{-- Sin ruta HUB en curso: acceso genérico al escáner. --}}
            <a
                href="{{ route('repartidor.scanner') }}"
                class="group flex flex-col gap-4 rounded-3xl border border-blue-900 bg-blue-900 p-6 shadow-sm transition hover:bg-blue-800 sm:flex-row sm:items-center sm:justify-between"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-2xl">
                        📷
                    </div>

                    <div>
                        <h2 class="font-display text-lg font-bold text-white">
                            Escanear paquetes
                        </h2>

                        <p class="mt-1 text-sm text-blue-100">
                            Procesa los paquetes de esta ruta mediante QR/código.
                        </p>
                    </div>

                </div>

                <span class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-blue-900 transition group-hover:bg-blue-50">
                    Abrir escáner
                </span>

            </a>

        @endif


        {{-- MIS PAQUETES (secundario para HUB) --}}
        <a
            href="{{ route('repartidor.packages') }}"
            class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-blue-900"
        >
            📦 Ver mis paquetes asignados
        </a>

    @endif


    {{-- =========================================================
         RESUMEN DEL DÍA
    ========================================================== --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- ASIGNADOS --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Asignados
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#111111]">
                        {{ number_format($assignedCount) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-lg">
                    📦
                </div>

            </div>

            <p class="mt-2 text-xs text-slate-500">
                Paquetes asociados a ti
            </p>

        </div>


        {{-- PENDIENTES --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Pendientes
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#111111]">
                        {{ number_format($pendingCount) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-lg">
                    ⏳
                </div>

            </div>

            <p class="mt-2 text-xs text-slate-500">
                Envíos todavía no entregados
            </p>

        </div>


        {{-- EN GESTIÓN --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        En gestión
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#111111]">
                        {{ number_format($collectedCount) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-lg">
                    🚚
                </div>

            </div>

            <p class="mt-2 text-xs text-slate-500">
                Paquetes ya recolectados
            </p>

        </div>


        {{-- ENTREGADOS --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Entregados
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#111111]">
                        {{ number_format($deliveredCount) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-lg">
                    ✅
                </div>

            </div>

            <p class="mt-2 text-xs text-slate-500">
                {{ number_format($deliveredTodayCount) }} entregados hoy
            </p>

        </div>

    </div>


    @unless ($isHub)

    {{-- =========================================================
         RUTA ACTIVA
    ========================================================== --}}
    <div
        class="rounded-3xl border border-[#E5E5E0] bg-white p-6 shadow-sm"
        @if (! $activeRoute) wire:poll.15s @endif
    >

        <div class="flex flex-col gap-5">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        @if ($activeRoute)
                            Ruta actual
                        @else
                            Rutas disponibles
                        @endif
                    </p>

                    @if ($activeRoute)

                        <h2 class="mt-1 font-display text-2xl font-bold text-[#111111]">
                            {{ $activeRoute->name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $activeRoute->city }}

                            @if ($activeRoute->state)
                                · {{ $activeRoute->state }}
                            @endif
                        </p>

                    @elseif ($availableRoutes->isNotEmpty())

                        @php $availableRoutesCount = $availableRoutes->count(); @endphp

                        <div class="mt-2 flex items-center gap-3">

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 font-display text-lg font-bold text-blue-900">
                                {{ $availableRoutesCount }}
                            </span>

                            <div>

                                <h2 class="font-display text-lg font-bold leading-tight text-[#111111]">
                                    {{ $availableRoutesCount === 1 ? 'Ruta compatible esperando' : 'Rutas compatibles esperando' }}
                                </h2>

                                <p class="mt-0.5 max-w-xl text-sm leading-snug text-slate-500">
                                    Todavía no tienes una ruta activa. Toma una de las
                                    rutas compatibles de abajo para comenzar tu operación.
                                </p>

                            </div>

                        </div>

                    @else

                        <h2 class="mt-1 font-display text-2xl font-bold text-[#111111]">
                            Sin rutas disponibles
                        </h2>

                        <p class="mt-1 max-w-xl text-sm text-slate-500">
                            No tienes una ruta activa y no hay rutas compatibles
                            por el momento. Vuelve a revisar más tarde.
                        </p>

                    @endif

                </div>


                @if ($activeRoute)

                    <div class="flex flex-wrap items-center gap-2">

                        @if ($activeRoute->status === \App\Models\Route::STATUS_ASSIGNED)

                            <button
                                type="button"
                                wire:click="startRoute"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="startRoute">
                                    Iniciar ruta
                                </span>

                                <span wire:loading wire:target="startRoute">
                                    Iniciando...
                                </span>
                            </button>

                            <button
                                type="button"
                                wire:loading.attr="disabled"
                                @click.prevent="$store.confirm.open({
                                    message: '¿Confirmas que quieres liberar esta ruta? Volverá a estar disponible para otros repartidores.',
                                    confirmText: 'Liberar ruta',
                                    variant: 'warning',
                                    onConfirm: () => $wire.releaseRoute(),
                                })"
                                class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-semibold text-[#111111] transition hover:border-amber-300 hover:bg-amber-50 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="releaseRoute">
                                    Liberar ruta
                                </span>

                                <span wire:loading wire:target="releaseRoute">
                                    Liberando...
                                </span>
                            </button>

                        @elseif ($activeRoute->status === \App\Models\Route::STATUS_IN_PROGRESS)

                            <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                Ruta en curso
                            </span>

                        @endif

                        <a
                            href="{{ route('repartidor.route-detail', $activeRoute->id) }}"
                            class="inline-flex items-center justify-center rounded-xl border border-[#E5E5E0] px-5 py-3 text-sm font-semibold text-[#111111] transition hover:border-blue-300 hover:bg-blue-50"
                        >
                            Ver detalles
                        </a>

                    </div>

                @endif

            </div>


            @if ($activeRoute)

                {{-- PROGRESO --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Progreso de la ruta
                        </span>

                        <span class="text-sm font-bold text-[#111111]">
                            {{ $routeProgress }}%
                        </span>

                    </div>

                    <div class="h-full rounded-full bg-blue-700 transition-all duration-500">
    <div
        class="h-full rounded-full bg-blue-700 transition-all duration-500"
        @style(['width' => $routeProgress . '%'])
    ></div>
</div>

                    <div class="mt-2 flex justify-between text-xs text-slate-500">

                        <span>
                            {{ $visitedStopsCount }} paradas visitadas
                        </span>

                        <span>
                            {{ $pendingStopsCount }} pendientes
                        </span>

                    </div>

                </div>


                {{-- DATOS DE LA RUTA --}}
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Estado
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#111111]">

                            @if ($activeRoute->status === \App\Models\Route::STATUS_ASSIGNED)
                                Asignada
                            @elseif ($activeRoute->status === \App\Models\Route::STATUS_IN_PROGRESS)
                                En curso
                            @else
                                {{ $activeRoute->status }}
                            @endif

                        </p>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Paradas
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#111111]">
                            {{ $routeStopsCount }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Paradas visitadas
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#111111]">
                            {{ $visitedStopsCount }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Tipo
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#111111]">

                            @if ($activeRoute->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                Traslado a hub
                            @elseif ($activeRoute->route_type === \App\Models\Route::TYPE_DELIVERY)
                                Entregas
                            @else
                                {{ $activeRoute->route_type }}
                            @endif

                        </p>

                    </div>

                </div>

            @elseif ($availableRoutes->isNotEmpty())

                {{-- RUTAS DISPONIBLES --}}
                <div class="space-y-3">

                    @foreach ($availableRoutes as $route)

                        <div class="flex flex-col gap-3 rounded-xl border border-[#E5E5E0] p-4 sm:flex-row sm:items-center sm:justify-between">

                            <div>

                                <p class="font-semibold text-sm text-[#111111]">
                                    {{ $route->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $route->city }}

                                    @if ($route->state)
                                        · {{ $route->state }}
                                    @endif

                                    ·

                                    @if ($route->route_type === \App\Models\Route::TYPE_HUB_TRANSFER)
                                        Traslado a hub
                                    @elseif ($route->route_type === \App\Models\Route::TYPE_HUB_DISTRIBUTION)
                                        Distribución a almacén
                                    @elseif ($route->route_type === \App\Models\Route::TYPE_DELIVERY)
                                        Entregas
                                    @else
                                        {{ $route->route_type }}
                                    @endif

                                    · {{ $route->stops->count() }} paradas
                                </p>

                            </div>

                            <button
                                type="button"
                                wire:click="claimRoute({{ $route->id }})"
                                wire:loading.attr="disabled"
                                wire:target="claimRoute({{ $route->id }})"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="claimRoute({{ $route->id }})">
                                    Tomar ruta
                                </span>

                                <span wire:loading wire:target="claimRoute({{ $route->id }})">
                                    Tomando...
                                </span>
                            </button>

                        </div>

                    @endforeach

                </div>

            @endif


            {{-- MENSAJES --}}
            @if (session('routeSuccess'))

                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('routeSuccess') }}
                </div>

            @endif


            @if (session('routeError'))

                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('routeError') }}
                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
         ACCIONES RÁPIDAS
    ========================================================== --}}
    <div>

        <div class="mb-4">

            <h2 class="font-display text-lg font-bold text-[#111111]">
                Acciones rápidas
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Accede directamente a las tareas principales.
            </p>

        </div>


        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- ESCANEAR --}}
            <a
                href="{{ route('repartidor.scanner') }}"
                class="group rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-xl">
                        📷
                    </div>

                    <div>

                        <h3 class="font-semibold text-[#111111]">
                            Escanear paquete
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Registrar una recolección mediante pistoleo.
                        </p>

                    </div>

                </div>

            </a>


            {{-- MIS PAQUETES --}}
            <a
                href="{{ route('repartidor.packages') }}"
                class="group rounded-2xl border border-[#E5E5E0] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-xl">
                        📦
                    </div>

                    <div>

                        <h3 class="font-semibold text-[#111111]">
                            Mis paquetes
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Consulta y gestiona tus envíos asignados.
                        </p>

                    </div>

                </div>

            </a>

        </div>

    </div>

    @endunless


    {{-- =========================================================
         PAQUETES PENDIENTES + ENTREGAS
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">


        {{-- PENDIENTES --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <h2 class="font-display text-lg font-bold text-[#111111]">
                        Paquetes pendientes
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Envíos que todavía requieren atención.
                    </p>

                </div>

                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    {{ number_format($pendingCount) }}
                </span>

            </div>


            @if ($pendingPackages->count())

                <div class="mt-5 space-y-3">

                    @foreach ($pendingPackages as $package)

                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">

                            <div class="flex items-start justify-between gap-4">

                                <div class="min-w-0">

                                    <p class="font-mono text-sm font-semibold text-[#111111]">
                                        {{ $package->tracking_number }}
                                    </p>

                                    <p class="mt-1 truncate text-sm font-medium text-slate-700">
                                        {{ $package->recipient_name }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $package->destination_city }}
                                        ·
                                        {{ $package->destination_state }}
                                    </p>

                                </div>


                                <span class="shrink-0 rounded-lg bg-white px-2 py-1 text-[10px] font-semibold uppercase text-slate-500">
                                    {{ str_replace('_', ' ', $package->current_status) }}
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="py-10 text-center">

                    <div class="text-4xl">
                        ✅
                    </div>

                    <p class="mt-3 font-semibold text-[#111111]">
                        No tienes paquetes pendientes
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Todo está al día.
                    </p>

                </div>

            @endif

        </div>


        {{-- ENTREGAS RECIENTES --}}
        <div class="rounded-2xl border border-[#E5E5E0] bg-white p-6 shadow-sm">

            <div>

                <h2 class="font-display text-lg font-bold text-[#111111]">
                    Entregas recientes
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Tus últimos paquetes entregados.
                </p>

            </div>


            @if ($recentDeliveries->count())

                <div class="mt-5 space-y-3">

                    @foreach ($recentDeliveries as $package)

                        <div class="flex items-center justify-between gap-4 rounded-xl bg-emerald-50 p-4">

                            <div class="min-w-0">

                                <p class="font-mono text-sm font-semibold text-[#111111]">
                                    {{ $package->tracking_number }}
                                </p>

                                <p class="mt-1 truncate text-sm text-slate-600">
                                    {{ $package->recipient_name }}
                                </p>

                            </div>


                            <div class="shrink-0 text-right">

                                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Entregado
                                </span>

                                @if ($package->delivery_completed_at)

                                    <p class="mt-1 text-[11px] text-slate-400">
                                        {{ $package->delivery_completed_at->format('d/m H:i') }}
                                    </p>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="py-10 text-center">

                    <div class="text-4xl">
                        📭
                    </div>

                    <p class="mt-3 font-semibold text-[#111111]">
                        Aún no hay entregas
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Tus entregas completadas aparecerán aquí.
                    </p>

                </div>

            @endif

        </div>

    </div>

</div>

