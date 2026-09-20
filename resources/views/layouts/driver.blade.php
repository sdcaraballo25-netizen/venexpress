<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title ?? 'Repartidor' }} — Venexpress
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @livewireStyles

    <style>

        [x-cloak] {
            display: none !important;
        }

        body {
            font-family:
                'Inter',
                ui-sans-serif,
                system-ui,
                sans-serif;
        }

        .font-display {
            font-family:
                'Space Grotesk',
                ui-sans-serif,
                system-ui,
                sans-serif;
        }

        .font-tracking {
            font-family:
                'JetBrains Mono',
                ui-monospace,
                monospace;
        }

    </style>

</head>


<body class="bg-[#F7F7F4] text-[#111111] antialiased">


<div class="min-h-screen flex">


    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <aside
        class="
            fixed inset-y-0 left-0 z-40
            w-72
            bg-white
            border-r border-[#E5E5E0]
            flex flex-col
            transform transition-transform duration-200
            md:relative
            md:translate-x-0
        "
        :class="
            sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full md:translate-x-0'
        "
    >

        {{-- =====================================================
             LOGO
        ====================================================== --}}

        <div class="px-6 pt-7 pb-8">

            @php
                $__sidebarDriverType = auth()->user()?->driver?->driver_type;
                $__sidebarIsHubBrand = $__sidebarDriverType === \App\Models\Driver::TYPE_HUB;
            @endphp

            <div class="flex items-center gap-3">

                <div
                    class="
                        h-11 w-11
                        rounded-xl
                        bg-amber-400
                        flex items-center justify-center
                        text-[#111111]
                        shrink-0
                    "
                >

                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        @if ($__sidebarIsHubBrand)

                            {{-- Camión / logística (HUB) --}}
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1"
                            />

                        @else

                            {{-- Vehículo / repartidor (Delivery) --}}
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v9a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8h3l3 4v4a1 1 0 01-1 1h-1m-6 0a2 2 0 11-4 0 2 2 0 014 0zm8 0a2 2 0 11-4 0 2 2 0 014 0z"
                            />

                        @endif

                    </svg>

                </div>


                <div class="min-w-0">

                    <span
                        class="
                            font-display
                            font-bold
                            text-xl
                            text-[#111111]
                            block
                            leading-none
                        "
                    >
                        Venexpress
                    </span>

                    <span
                        class="
                            text-xs
                            text-[#B8B8B2]
                            block
                            mt-1
                        "
                    >
                        Panel Repartidor
                    </span>

                </div>

            </div>

        </div>


        {{-- =====================================================
             NAVEGACIÓN
        ====================================================== --}}

        <nav class="flex-1 px-4 pb-6 overflow-y-auto">

            @php
                $sidebarDriver = auth()->user()?->driver;
                $sidebarIsHub = $sidebarDriver?->driver_type === \App\Models\Driver::TYPE_HUB;
                $sidebarActiveRouteId = $sidebarDriver
                    ? \App\Models\Route::query()
                        ->where('driver_id', $sidebarDriver->id)
                        ->whereIn('status', [
                            \App\Models\Route::STATUS_ASSIGNED,
                            \App\Models\Route::STATUS_IN_PROGRESS,
                        ])
                        ->latest('created_at')
                        ->value('id')
                    : null;
            @endphp


            {{-- PRINCIPAL --}}

            <p
                class="
                    px-3
                    mb-3
                    text-xs
                    font-semibold
                    uppercase
                    tracking-wider
                    text-[#B8B8B2]
                "
            >
                Principal
            </p>


            {{-- DASHBOARD --}}

            <a
                href="{{ route('repartidor.dashboard') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.dashboard')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6"
                    />

                </svg>

                <span>
                    Resumen
                </span>

            </a>


            @if ($sidebarActiveRouteId)

                {{-- MI RUTA --}}

                <a
                    href="{{ route('repartidor.route-detail', $sidebarActiveRouteId) }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="
                        flex items-center gap-3
                        px-4 py-3
                        rounded-xl
                        text-sm
                        font-medium
                        transition-colors
                        {{ request()->routeIs('repartidor.route-detail')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                    "
                >

                    <svg
                        class="w-5 h-5 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 20l-5.447-2.724A2 2 0 012 15.487V8.513a2 2 0 011.106-1.789L9 4m0 16V4m0 16l6-3m-6-13l6 3m0 0l5.447-2.724A2 2 0 0021 6.487v6.026M15 7v10"
                        />

                    </svg>

                    <span>
                        Mi ruta
                    </span>

                </a>

            @endif


            {{-- HISTORIAL DE RUTAS --}}

            <a
                href="{{ route('repartidor.route-history') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.route-history')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                    />

                </svg>

                <span>
                    Historial de rutas
                </span>

            </a>


            {{-- ESCANEAR PAQUETES --}}

            <a
                href="{{ route('repartidor.scanner') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.scanner')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M7 12h10M12 7v10"
                    />

                </svg>

                <span>
                    Escanear paquetes
                </span>

            </a>


            {{-- =================================================
                 OPERACIONES
            ================================================== --}}

            <p
                class="
                    px-3
                    mb-3
                    mt-7
                    text-xs
                    font-semibold
                    uppercase
                    tracking-wider
                    text-[#B8B8B2]
                "
            >
                Operaciones
            </p>


            {{-- MIS PAQUETES --}}

            <a
                href="{{ route('repartidor.packages') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.packages')
                        || request()->routeIs('repartidor.package-detail')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0v10l-8 4-8-4V7m16 0l-8 4-8-4m8 4v10"
                    />

                </svg>

                <span>
                    Mis paquetes
                </span>

            </a>


            {{-- DESCARGAR APP --}}

            <a
                href="{{ route('repartidor.app-download') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.app-download')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"
                    />

                </svg>

                <span>
                    Descargar app
                </span>

            </a>


            {{-- AYUDA --}}

            <a
                href="{{ route('repartidor.help') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('repartidor.help')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />

                </svg>

                <span>
                    Ayuda
                </span>

            </a>


            {{-- RECOMENDACIONES --}}

            <a
                href="{{ route('recommendations.create') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="
                    flex items-center gap-3
                    px-4 py-3
                    rounded-xl
                    text-sm
                    font-medium
                    transition-colors
                    {{ request()->routeIs('recommendations.create')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}
                "
            >

                <svg
                    class="w-5 h-5 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z"
                    />

                </svg>

                <span>
                    Recomendaciones
                </span>

            </a>


        </nav>


        {{-- =====================================================
             USUARIO
        ====================================================== --}}

        <div
            class="
                border-t
                border-[#E5E5E0]
                px-5
                py-5
                bg-white
            "
        >

            <div class="flex items-center gap-3">

                <div
                    class="
                        h-10 w-10
                        rounded-full
                        bg-amber-400
                        text-[#111111]
                        flex items-center justify-center
                        font-bold
                        uppercase
                        shrink-0
                    "
                >
                    {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}
                </div>


                <div class="min-w-0 flex-1">

                    <p
                        class="
                            text-sm
                            font-semibold
                            text-[#111111]
                            truncate
                        "
                    >
                        {{ auth()->user()->name ?? 'Repartidor' }}
                    </p>

                    <p
                        class="
                            text-xs
                            text-[#6B6B66]
                            truncate
                        "
                    >
                        Repartidor
                    </p>

                </div>

            </div>


            <a
                href="{{ route('profile') }}"
                class="
                    block
                    w-full
                    mt-4
                    rounded-xl
                    px-3
                    py-2
                    text-sm
                    font-medium
                    text-[#6B6B66]
                    hover:bg-slate-50
                    hover:text-[#111111]
                    transition-colors
                "
            >
                Mi Perfil
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-2"
            >

                @csrf

                <button
                    type="submit"
                    class="
                        w-full
                        rounded-xl
                        px-3
                        py-2
                        text-left
                        text-sm
                        font-medium
                        text-red-500
                        hover:bg-red-50
                        hover:text-red-700
                        transition-colors
                    "
                >
                    Cerrar sesión
                </button>

            </form>

        </div>

    </aside>


    {{-- =========================================================
         OVERLAY MÓVIL
    ========================================================== --}}

    <div
        x-show="sidebarOpen"
        x-cloak
        @click="sidebarOpen = false"
        class="
            fixed
            inset-0
            bg-black/40
            z-30
            md:hidden
        "
    ></div>


    {{-- =========================================================
         ÁREA PRINCIPAL
    ========================================================== --}}

    <div class="flex-1 min-w-0">


        {{-- HEADER --}}

        <header
            class="
                h-16
                bg-white
                border-b border-[#E5E5E0]
                flex items-center
                justify-between
                px-4 lg:px-8
                sticky top-0
                z-20
            "
        >

            <div class="flex items-center">

                <button
                    type="button"
                    @click="sidebarOpen = true"
                    class="
                        md:hidden
                        h-10 w-10
                        rounded-xl
                        flex items-center justify-center
                        text-[#111111]
                        hover:bg-slate-100
                    "
                    aria-label="Abrir menú"
                >

                    <svg
                        class="w-6 h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />

                    </svg>

                </button>

            </div>


            <div class="flex items-center gap-4">


                {{-- ESTADO DISPONIBLE --}}

                <div
                    class="
                        hidden sm:flex
                        items-center gap-2
                        rounded-full
                        border border-emerald-200
                        bg-emerald-50
                        px-3 py-1.5
                    "
                >

                    <span
                        class="
                            h-2
                            w-2
                            rounded-full
                            bg-emerald-500
                        "
                    ></span>

                    <span
                        class="
                            text-xs
                            font-medium
                            text-emerald-700
                        "
                    >
                        Disponible
                    </span>

                </div>


                <div class="hidden sm:block text-right">

                    <p
                        class="
                            text-sm
                            font-semibold
                            text-[#111111]
                        "
                    >
                        {{ auth()->user()->name ?? 'Repartidor' }}
                    </p>

                    <p
                        class="
                            text-xs
                            text-[#6B6B66]
                        "
                    >
                        Panel de repartidor
                    </p>

                </div>


                <a
                    href="{{ route('profile') }}"
                    class="
                        h-10
                        px-4
                        inline-flex
                        items-center
                        rounded-xl
                        text-sm
                        font-medium
                        text-[#6B6B66]
                        border border-[#E5E5E0]
                        hover:bg-slate-50
                        hover:text-[#111111]
                        transition-colors
                    "
                >
                    Mi Perfil
                </a>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="
                            h-10
                            px-4
                            rounded-xl
                            text-sm
                            font-medium
                            text-red-500
                            border border-red-200
                            hover:bg-red-50
                            hover:text-red-700
                            transition-colors
                        "
                    >
                        Salir
                    </button>

                </form>

            </div>

        </header>


        {{-- =====================================================
             CONTENIDO LIVEWIRE
        ====================================================== --}}

        <main
            class="
                p-4
                lg:p-8
                w-full
                max-w-7xl
                mx-auto
            "
        >

            {{ $slot }}

        </main>

    </div>

</div>

<x-confirm-dialog />

@livewireScripts

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

@stack('scripts')

</body>

</html>
