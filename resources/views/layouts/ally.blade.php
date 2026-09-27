<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $title ?? 'Panel Aliado' }} — Venexpress
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
        }

        .font-tracking {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
        }
    </style>
</head>

<body class="bg-[#F7F7F4] text-[#111111] antialiased">

<div class="min-h-screen flex">

    {{-- ==========================================================
         SIDEBAR
    =========================================================== --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 border-r border-[#E5E5E0] bg-white px-5 py-8 flex flex-col justify-between transform transition-transform duration-200 md:relative md:translate-x-0"
        :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >

        <div class="min-h-0 flex flex-col">

            {{-- LOGO --}}
            <div class="flex items-center gap-3 px-2 mb-10">

                <div class="bg-amber-400 text-[#111111] p-2 rounded-xl shrink-0">
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
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                        />
                    </svg>
                </div>

                <div>
                    <span class="font-display font-bold text-xl text-[#111111] block leading-none">
                        Venexpress
                    </span>

                    <span class="text-xs text-[#B8B8B2]">
                        Agencia Aliada
                    </span>
                </div>

            </div>


            {{-- ==================================================
                 NAVEGACIÓN
            =================================================== --}}
            <nav class="space-y-1 overflow-y-auto">

                {{-- PRINCIPAL --}}
                <p class="px-2 text-xs font-semibold text-[#B8B8B2] uppercase tracking-wider mb-3">
                    Principal
                </p>


                {{-- DASHBOARD (solo Aliado Administrador: agrega TODAS las taquillas) --}}
                @if (auth()->user()->isAliado())
                    <a
                        href="{{ route('ally.dashboard') }}"
                        wire:navigate
                        @click="$store.sidebar.open = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('ally.dashboard')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2-2v-2z"
                            />
                        </svg>

                        <span>
                            Resumen
                        </span>

                    </a>
                @endif


                {{-- AYUDA --}}
                <a
                    href="{{ route('ally.help') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.help')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
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
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('recommendations.create')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
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


                {{-- ==================================================
                     OPERACIONES
                =================================================== --}}
                <p class="px-2 text-xs font-semibold text-[#B8B8B2] uppercase tracking-wider mb-3 mt-6">
                    Operaciones
                </p>


                {{-- REGISTRAR PEDIDO --}}
                <a
                    href="{{ route('ally.packages.create') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.create')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>

                    <span>
                        Registrar pedido
                    </span>

                </a>

                {{-- EMPRENDEDORES --}}
                <a
                    href="{{ route('ally.emprendedor-pedidos') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.emprendedor-pedidos')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 11H4L5 9z"
                        />
                    </svg>

                    <span>
                        Emprendedores
                    </span>

                </a>

                {{-- MIS PEDIDOS --}}
<a
    href="{{ route('ally.packages.index') }}"
    wire:navigate
    @click="$store.sidebar.open = false"
    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
    {{ request()->routeIs('ally.packages.index')
        ? 'bg-amber-400 text-[#111111]'
        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
>
    <svg
        class="w-5 h-5"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5h6m-7 4h8m-9 4h10m-9 4h8M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"
        />
    </svg>

    <span>
        Mis pedidos
    </span>
</a>


                {{-- RECEPCIÓN --}}
                <a
                    href="{{ route('ally.packages.reception') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.reception')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
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
                        Recepción de paquetes
                    </span>

                </a>


                {{-- RETIRO EN AGENCIA --}}
                <a
                    href="{{ route('ally.packages.pickup') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.pickup')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 10h18M5 10v10h14V10M7 10V7a5 5 0 0110 0v3"
                        />
                    </svg>

                    <span>
                        Retiro en agencia
                    </span>

                </a>


                {{-- CIERRE DEL DÍA --}}
                {{--
                    Visible para ambos roles: el Aliado Administrador ve
                    todo el negocio (con filtro por taquilla), Taquilla
                    solo ve lo que ella misma registró.
                --}}
                <a
                    href="{{ route('ally.sales-closeout') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.sales-closeout')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    <span>
                        Cierre del día
                    </span>

                </a>


                {{-- COBRO EN DESTINO --}}
                {{--
                    Fuera del bloque "Administración": la ruta ally.cod
                    permite role:aliado,aliado_taquilla, así que el
                    personal de Taquilla también debe ver este enlace.
                --}}
                <a
                    href="{{ route('ally.cod') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.cod')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    <span>
                        Cobro en destino
                    </span>

                </a>


                {{-- INCIDENCIAS --}}
                {{--
                    Igual que Cobro en destino: ally.incidents también
                    permite role:aliado,aliado_taquilla.
                --}}
                <a
                    href="{{ route('ally.incidents') }}"
                    wire:navigate
                    @click="$store.sidebar.open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.incidents')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                >

                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>

                    <span>
                        Incidencias
                    </span>

                </a>


                @if (auth()->user()->isAliado())

                    {{-- ==================================================
                         ADMINISTRACIÓN
                    =================================================== --}}
                    <p class="px-2 text-xs font-semibold text-[#B8B8B2] uppercase tracking-wider mb-3 mt-6">
                        Administración
                    </p>


                    {{-- TAQUILLAS --}}
                    <a
                        href="{{ route('ally.staff') }}"
                        wire:navigate
                        @click="$store.sidebar.open = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('ally.staff')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
                    >

                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            />
                        </svg>

                        <span>
                            Gestión de Taquillas
                        </span>

                    </a>


                    {{-- Comisiones --}}
                    <a
                        href="{{ route('ally.commissions') }}"
                        wire:navigate
                        class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('ally.commissions')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-slate-600 hover:bg-slate-50' }}"
                    >
                        <svg class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 16v-4m8-4a8 8 0 11-16 0 8 8 0 0116 0z"/>
                        </svg>

                        <span>Comisiones y saldo</span>
                    </a>


                    {{-- Reportes --}}
                    <a
                        href="{{ route('ally.reports') }}"
                        wire:navigate
                        class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('ally.reports')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-slate-600 hover:bg-slate-50' }}"
                    >
                        <svg class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"/>
                        </svg>

                        <span>Reportes</span>
                    </a>


                    {{-- Corte de caja --}}
                    <a
                        href="{{ route('ally.cash-cut') }}"
                        wire:navigate
                        class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition
                        {{ request()->routeIs('ally.cash-cut')
                            ? 'bg-amber-400 text-[#111111]'
                            : 'text-slate-600 hover:bg-slate-50' }}"
                    >
                        <svg class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 10h18M5 10v8m4-8v8m6-8v8m4-8v8M3 18h18M5 6h14l2 4H3l2-4z"/>
                        </svg>

                        <span>Corte de caja</span>
                    </a>

                @endif

            </nav>

        </div>


        {{-- ==========================================================
             PERFIL
        =========================================================== --}}
        <div class="mt-8 pt-6 border-t border-[#E5E5E0] shrink-0">

            <div class="flex items-center gap-3 px-2">

                <div
                    class="w-10 h-10 rounded-full bg-amber-400 flex items-center justify-center text-[#111111] font-bold uppercase shrink-0"
                >
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>

                <div class="overflow-hidden min-w-0">

                    <p class="text-sm font-semibold text-[#111111] truncate">
                        {{ Auth::user()->name ?? 'Aliado' }}
                    </p>

                    <p class="text-xs text-[#6B6B66] truncate">
                        {{ Auth::user()->email ?? '' }}
                    </p>

                </div>

            </div>


            <a
                href="{{ route('profile') }}"
                class="block w-full mt-4 px-2 text-sm text-[#6B6B66] font-medium hover:text-[#111111] transition-colors"
            >
                Mi Perfil
            </a>

            <form
                method="POST"
                action="{{ route('logout') }}"
                class="w-full mt-2"
            >
                @csrf

                <button
                    type="submit"
                    class="w-full text-left px-2 text-sm text-red-500 font-medium hover:text-red-700 transition-colors"
                >
                    Cerrar sesión
                </button>

            </form>

        </div>

    </aside>


    {{-- ==========================================================
         OVERLAY MÓVIL
    =========================================================== --}}
    <div
        x-show="$store.sidebar.open"
        @click="$store.sidebar.open = false"
        class="fixed inset-0 bg-black/40 z-30 md:hidden"
        x-cloak
    ></div>


    {{-- ==========================================================
         CONTENIDO PRINCIPAL
    =========================================================== --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <header
            class="h-16 bg-white border-b border-[#E5E5E0] flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20"
        >

            <button
                type="button"
                @click="$store.sidebar.open = !$store.sidebar.open"
                class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-[#111111] hover:bg-slate-100"
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


            <div class="flex items-center gap-4 ml-auto">

                <div class="hidden sm:block text-right">

                    <p class="text-sm font-semibold text-[#111111]">
                        {{ auth()->user()->name ?? 'Aliado' }}
                    </p>

                    <p class="text-xs text-[#6B6B66]">
                        Agencia Aliada
                    </p>

                </div>

                <a
                    href="{{ route('profile') }}"
                    class="text-sm font-medium text-[#6B6B66] hover:text-[#111111] transition-colors"
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
                        class="text-sm font-medium text-[#6B6B66] hover:text-red-600 transition-colors"
                    >
                        Salir
                    </button>

                </form>

            </div>

        </header>


        {{-- ======================================================
             CONTENIDO DEL COMPONENTE LIVEWIRE

             Dashboard.php utiliza:
             #[Layout('layouts.ally')]

             Por eso Livewire inserta aquí:
             livewire.ally.dashboard
        ======================================================= --}}
        <main class="p-4 lg:p-8 w-full max-w-7xl mx-auto">

            {{ $slot }}

        </main>

    </div>

</div>

<x-confirm-dialog />

@livewireScripts

@stack('scripts')

</body>
</html>
