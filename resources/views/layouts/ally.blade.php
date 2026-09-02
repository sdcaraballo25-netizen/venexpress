<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">
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

<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">

<div class="min-h-screen flex">

    {{-- ==========================================================
         SIDEBAR
    =========================================================== --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 border-r border-[#E2E8F0] bg-white px-5 py-8 flex flex-col justify-between transform transition-transform duration-200 md:relative md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >

        <div class="min-h-0 flex flex-col">

            {{-- LOGO --}}
            <div class="flex items-center gap-3 px-2 mb-10">

                <div class="bg-blue-900 text-white p-2 rounded-xl shrink-0">
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

                <div>
                    <span class="font-display font-bold text-xl text-[#0F172A] block leading-none">
                        Venexpress
                    </span>

                    <span class="text-xs text-[#94A3B8]">
                        Agencia Aliada
                    </span>
                </div>

            </div>


            {{-- ==================================================
                 NAVEGACIÓN
            =================================================== --}}
            <nav class="space-y-1 overflow-y-auto">

                {{-- PRINCIPAL --}}
                <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3">
                    Principal
                </p>


                {{-- DASHBOARD --}}
                <a
                    href="{{ route('ally.dashboard') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.dashboard')
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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


                {{-- ==================================================
                     OPERACIONES
                =================================================== --}}
                <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3 mt-6">
                    Operaciones
                </p>


                {{-- REGISTRAR PEDIDO --}}
                <a
                    href="{{ route('ally.packages.create') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.create')
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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


                {{-- RECEPCIÓN --}}
                <a
                    href="{{ route('ally.packages.reception') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.reception')
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('ally.packages.pickup')
                        ? 'bg-blue-50 text-blue-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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


                @if (auth()->user()->isAliado())

                    {{-- ==================================================
                         ADMINISTRACIÓN
                    =================================================== --}}
                    <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3 mt-6">
                        Administración
                    </p>


                    {{-- TAQUILLAS --}}
                    <div
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-[#94A3B8]"
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

                    </div>


                    {{-- COBRO EN DESTINO --}}
                    <a
                        href="{{ route('ally.cod') }}"
                        wire:navigate
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('ally.cod')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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
                    <a
                        href="{{ route('ally.incidents') }}"
                        wire:navigate
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('ally.incidents')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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


                    {{-- COMISIONES --}}
                    <a
                        href="{{ route('ally.commissions') }}"
                        wire:navigate
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                        {{ request()->routeIs('ally.commissions')
                            ? 'bg-blue-50 text-blue-900'
                            : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
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
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 3 3 3 3 .895 3 3-1.343 3-3 3m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                        <span>
                            Comisiones y saldo
                        </span>

                    </a>

                @endif

            </nav>

        </div>


        {{-- ==========================================================
             PERFIL
        =========================================================== --}}
        <div class="mt-8 pt-6 border-t border-[#E2E8F0] shrink-0">

            <div class="flex items-center gap-3 px-2">

                <div
                    class="w-10 h-10 rounded-full bg-blue-900 flex items-center justify-center text-white font-bold uppercase shrink-0"
                >
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>

                <div class="overflow-hidden min-w-0">

                    <p class="text-sm font-semibold text-[#0F172A] truncate">
                        {{ Auth::user()->name ?? 'Aliado' }}
                    </p>

                    <p class="text-xs text-[#64748B] truncate">
                        {{ Auth::user()->email ?? '' }}
                    </p>

                </div>

            </div>


            <form
                method="POST"
                action="{{ route('logout') }}"
                class="w-full mt-4"
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
        x-show="sidebarOpen"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 z-30 md:hidden"
        x-cloak
    ></div>


    {{-- ==========================================================
         CONTENIDO PRINCIPAL
    =========================================================== --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <header
            class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20"
        >

            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-[#0B1220] hover:bg-slate-100"
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

                    <p class="text-sm font-semibold text-[#0F172A]">
                        {{ auth()->user()->name ?? 'Aliado' }}
                    </p>

                    <p class="text-xs text-[#64748B]">
                        Agencia Aliada
                    </p>

                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="text-sm font-medium text-[#64748B] hover:text-red-600 transition-colors"
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


@livewireScripts

@stack('scripts')

</body>
</html>
