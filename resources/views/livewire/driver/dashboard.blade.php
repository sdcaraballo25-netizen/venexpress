<div class="space-y-6 font-sans">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <p class="text-sm font-medium text-blue-700">
                Panel del repartidor
            </p>

            <h1 class="mt-1 font-display text-3xl font-bold tracking-tight text-[#0F172A]">
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


    {{-- =========================================================
         RESUMEN DEL DÍA
    ========================================================== --}}
    <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- ASIGNADOS --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Asignados
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#0F172A]">
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Pendientes
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#0F172A]">
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        En gestión
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#0F172A]">
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
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm">

            <div class="flex items-start justify-between">

                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Entregados
                    </p>

                    <p class="mt-3 font-display text-3xl font-bold text-[#0F172A]">
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


    {{-- =========================================================
         RUTA ACTIVA
    ========================================================== --}}
    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

        <div class="flex flex-col gap-5">

            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                <div>

                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Ruta actual
                    </p>

                    @if ($activeRoute)

                        <h2 class="mt-1 font-display text-2xl font-bold text-[#0F172A]">
                            {{ $activeRoute->name }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ $activeRoute->city }}

                            @if ($activeRoute->state)
                                · {{ $activeRoute->state }}
                            @endif
                        </p>

                    @else

                        <h2 class="mt-1 font-display text-2xl font-bold text-[#0F172A]">
                            Sin ruta asignada
                        </h2>

                        <p class="mt-1 max-w-xl text-sm text-slate-500">
                            Cuando el administrador te asigne una ruta,
                            podrás verla y comenzar tu operación desde aquí.
                        </p>

                    @endif

                </div>


                @if ($activeRoute)

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

                    @elseif ($activeRoute->status === \App\Models\Route::STATUS_IN_PROGRESS)

                        <span class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            Ruta en curso
                        </span>

                    @endif

                @endif

            </div>


            @if ($activeRoute)

                {{-- PROGRESO --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Progreso de la ruta
                        </span>

                        <span class="text-sm font-bold text-[#0F172A]">
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

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">

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

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                            {{ $routeStopsCount }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Paradas visitadas
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">
                            {{ $visitedStopsCount }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-4">

                        <p class="text-xs text-slate-400">
                            Tipo
                        </p>

                        <p class="mt-1 text-sm font-semibold text-[#0F172A]">

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

            <h2 class="font-display text-lg font-bold text-[#0F172A]">
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
                class="group rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-xl">
                        📷
                    </div>

                    <div>

                        <h3 class="font-semibold text-[#0F172A]">
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
                class="group rounded-2xl border border-[#E2E8F0] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md"
            >

                <div class="flex items-center gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-xl">
                        📦
                    </div>

                    <div>

                        <h3 class="font-semibold text-[#0F172A]">
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


    {{-- =========================================================
         PAQUETES PENDIENTES + ENTREGAS
    ========================================================== --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">


        {{-- PENDIENTES --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <h2 class="font-display text-lg font-bold text-[#0F172A]">
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

                                    <p class="font-mono text-sm font-semibold text-[#0F172A]">
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

                    <p class="mt-3 font-semibold text-[#0F172A]">
                        No tienes paquetes pendientes
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        Todo está al día.
                    </p>

                </div>

            @endif

        </div>


        {{-- ENTREGAS RECIENTES --}}
        <div class="rounded-2xl border border-[#E2E8F0] bg-white p-6 shadow-sm">

            <div>

                <h2 class="font-display text-lg font-bold text-[#0F172A]">
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

                                <p class="font-mono text-sm font-semibold text-[#0F172A]">
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

                    <p class="mt-3 font-semibold text-[#0F172A]">
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

