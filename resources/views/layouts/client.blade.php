<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        {{ $title ?? 'Mi cuenta' }} — Venexpress
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>
                </div>

                <div>
                    <span class="font-display font-bold text-xl text-[#0F172A] block leading-none">
                        Venexpress
                    </span>

                    <span class="text-xs text-[#94A3B8]">
                        Mi cuenta
                    </span>
                </div>

            </div>


            {{-- ==================================================
                 RASTREAR GUÍA

                 Reutiliza la página pública de rastreo (TrackingController
                 / /rastreo/resultado) tal cual, en vez de duplicar aquí
                 la lógica de la línea de tiempo. Se abre en pestaña
                 nueva para no sacar al cliente de su cuenta.
            =================================================== --}}
            <form
                method="GET"
                action="{{ route('tracking.show') }}"
                target="_blank"
                class="px-2 mb-8"
            >
                <label class="block text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-2">
                    Rastrear guía
                </label>

                <div class="flex gap-2">
                    <input
                        type="text"
                        name="guia"
                        required
                        placeholder="VEN-..."
                        class="w-full min-w-0 rounded-xl border-[#E2E8F0] text-sm focus:border-blue-900 focus:ring-blue-900"
                    >

                    <button
                        type="submit"
                        class="shrink-0 rounded-xl bg-blue-900 px-3 text-white hover:bg-blue-800"
                        aria-label="Buscar guía"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5A6.5 6.5 0 114 10.5a6.5 6.5 0 0113 0z" />
                        </svg>
                    </button>
                </div>
            </form>

            {{-- ==================================================
                 NAVEGACIÓN
            =================================================== --}}
            <nav class="space-y-1 overflow-y-auto">

                <p class="px-2 text-xs font-semibold text-[#94A3B8] uppercase tracking-wider mb-3">
                    Principal
                </p>

                {{-- MIS PEDIDOS --}}
                <a
                    href="{{ route('cliente.dashboard') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.dashboard')
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
                        Mis pedidos
                    </span>

                </a>

                {{-- INCIDENCIAS --}}
                <a
                    href="{{ route('cliente.incidents') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.incidents')
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

                {{-- PAGOS PENDIENTES --}}
                <a
                    href="{{ route('cliente.pending-payments') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.pending-payments')
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
                        Pagos pendientes
                    </span>

                </a>

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
                    {{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}
                </div>

                <div class="overflow-hidden min-w-0">

                    <p class="text-sm font-semibold text-[#0F172A] truncate">
                        {{ Auth::user()->name ?? 'Cliente' }}
                    </p>

                    <p class="text-xs text-[#64748B] truncate">
                        {{ Auth::user()->email ?? '' }}
                    </p>

                </div>

            </div>

            <a
                href="{{ route('profile') }}"
                class="block w-full mt-4 px-2 text-sm text-[#64748B] font-medium hover:text-[#0F172A] transition-colors"
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
                        {{ auth()->user()->name ?? 'Cliente' }}
                    </p>

                    <p class="text-xs text-[#64748B]">
                        Panel de Cliente
                    </p>

                </div>

                <a
                    href="{{ route('profile') }}"
                    class="text-sm font-medium text-[#64748B] hover:text-[#0F172A] transition-colors"
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
                        class="text-sm font-medium text-[#64748B] hover:text-red-600 transition-colors"
                    >
                        Salir
                    </button>

                </form>

            </div>

        </header>


        {{-- ======================================================
             CONTENIDO DEL COMPONENTE LIVEWIRE

             Los componentes de App\Livewire\Client usan:
             #[Layout('layouts.client')]
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
