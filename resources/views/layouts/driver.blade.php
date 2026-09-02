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


<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">


<div class="min-h-screen flex">


    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <aside
        class="
            fixed inset-y-0 left-0 z-40
            w-72
            bg-white
            border-r border-[#E2E8F0]
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

            <div class="flex items-center gap-3">

                <div
                    class="
                        h-11 w-11
                        rounded-xl
                        bg-blue-900
                        flex items-center justify-center
                        text-white
                        shrink-0
                    "
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
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                        />

                    </svg>

                </div>


                <div class="min-w-0">

                    <span
                        class="
                            font-display
                            font-bold
                            text-xl
                            text-[#0F172A]
                            block
                            leading-none
                        "
                    >
                        Venexpress
                    </span>

                    <span
                        class="
                            text-xs
                            text-[#94A3B8]
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


            {{-- PRINCIPAL --}}

            <p
                class="
                    px-3
                    mb-3
                    text-xs
                    font-semibold
                    uppercase
                    tracking-wider
                    text-[#94A3B8]
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
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}
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
                    text-[#94A3B8]
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
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}
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


            {{-- ESCANEAR --}}

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
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}
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
                    Escanear paquete
                </span>

            </a>


            {{-- HOJA DE RUTA --}}

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
                    text-[#64748B]
                    hover:bg-slate-50
                    hover:text-[#0F172A]
                    transition-colors
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
                    Hoja de ruta
                </span>

            </a>


            {{-- =================================================
                 ACCESO RÁPIDO
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
                    text-[#94A3B8]
                "
            >
                Acceso rápido
            </p>


            {{-- VERIFICAR GUÍA --}}

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
                    text-[#64748B]
                    hover:bg-slate-50
                    hover:text-[#0F172A]
                    transition-colors
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
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />

                </svg>

                <span>
                    Verificar guía
                </span>

            </a>

        </nav>


        {{-- =====================================================
             USUARIO
        ====================================================== --}}

        <div
            class="
                border-t
                border-[#E2E8F0]
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
                        bg-blue-900
                        text-white
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
                            text-[#0F172A]
                            truncate
                        "
                    >
                        {{ auth()->user()->name ?? 'Repartidor' }}
                    </p>

                    <p
                        class="
                            text-xs
                            text-[#64748B]
                            truncate
                        "
                    >
                        Repartidor
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mt-4"
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
                border-b border-[#E2E8F0]
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
                        text-[#0F172A]
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
                            text-[#0F172A]
                        "
                    >
                        {{ auth()->user()->name ?? 'Repartidor' }}
                    </p>

                    <p
                        class="
                            text-xs
                            text-[#64748B]
                        "
                    >
                        Panel de repartidor
                    </p>

                </div>


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


@livewireScripts

@stack('scripts')

</body>

</html>
