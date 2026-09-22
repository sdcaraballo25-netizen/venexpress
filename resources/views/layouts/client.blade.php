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

<body class="bg-[#F7F7F4] text-[#111111] antialiased">

<div class="min-h-screen flex">

    {{-- ==========================================================
         SIDEBAR
    =========================================================== --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 border-r border-[#E5E5E0] bg-white px-5 py-8 flex flex-col justify-between transform transition-transform duration-200 md:relative md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
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
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        />
                    </svg>
                </div>

                <div>
                    <span class="font-display font-bold text-xl text-[#111111] block leading-none">
                        Venexpress
                    </span>

                    <span class="text-xs text-[#B8B8B2]">
                        Mi cuenta
                    </span>
                </div>

            </div>


            {{-- ==================================================
                 RASTREAR GUÍA

                 Reutiliza la página pública de rastreo (TrackingController
                 / /rastreo/resultado) tal cual, en vez de duplicar aquí
                 la lógica de la línea de tiempo — pero en un modal con
                 un iframe en vez de una pestaña nueva, para que el
                 cliente vea el resultado sin salir de su panel.
            =================================================== --}}
            <div class="px-2 mb-8">
                <form
                    @submit.prevent="
                        $store.tracking.src = '{{ route('tracking.show') }}?guia=' + encodeURIComponent($event.target.guia.value);
                        $dispatch('open-modal', 'tracking-result');
                    "
                >
                    <label class="block text-xs font-semibold text-[#B8B8B2] uppercase tracking-wider mb-2">
                        Rastrear guía
                    </label>

                    <div class="flex gap-2">
                        <input
                            type="text"
                            name="guia"
                            required
                            placeholder="VEN-..."
                            class="w-full min-w-0 rounded-xl border-[#E5E5E0] text-sm focus:border-blue-900 focus:ring-blue-900"
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
            </div>

            {{-- ==================================================
                 NAVEGACIÓN
            =================================================== --}}
            <nav class="space-y-1 overflow-y-auto">

                <p class="px-2 text-xs font-semibold text-[#B8B8B2] uppercase tracking-wider mb-3">
                    Principal
                </p>

                {{-- MIS PEDIDOS --}}
                <a
                    href="{{ route('cliente.dashboard') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.dashboard')
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
                        Mis pedidos
                    </span>

                </a>

                {{-- COTIZAR --}}
                <a
                    href="{{ route('public.calculator') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('public.calculator')
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
                            d="M9 7h6m0 3H9m3 3h.01M9 19l-4-4V7a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-6l-2 2z"
                        />
                    </svg>

                    <span>
                        Cotizar
                    </span>

                </a>

                {{-- INCIDENCIAS --}}
                <a
                    href="{{ route('cliente.incidents') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.incidents')
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

                {{-- RECOMENDACIONES --}}
                <a
                    href="{{ route('recommendations.create') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
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

                {{-- PAGOS --}}
                <a
                    href="{{ route('cliente.pending-payments') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.pending-payments')
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

                    <span class="flex flex-1 items-center justify-between">
                        Pagos

                        @if (auth()->user()->hasPendingCodPayments())
                            <span class="h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                        @endif
                    </span>

                </a>

                {{-- SUCURSALES --}}
                <a
                    href="{{ route('public.offices') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('public.offices')
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
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                        />
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>

                    <span>
                        Sucursales
                    </span>

                </a>

                {{-- TIENDA --}}
                <a
                    href="{{ route('public.marketplace') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('public.marketplace')
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
                        Tienda
                    </span>

                </a>

                {{-- MIS COMPRAS --}}
                <a
                    href="{{ route('cliente.compras') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.compras')
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
                        Mis Compras
                    </span>

                </a>

                {{-- CENTRO DE AYUDA --}}
                <a
                    href="{{ route('cliente.help') }}"
                    wire:navigate
                    @click="sidebarOpen = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-colors
                    {{ request()->routeIs('cliente.help')
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
                        Centro de Ayuda
                    </span>

                </a>

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
                    {{ strtoupper(substr(Auth::user()->name ?? 'C', 0, 1)) }}
                </div>

                <div class="overflow-hidden min-w-0">

                    <p class="text-sm font-semibold text-[#111111] truncate">
                        {{ Auth::user()->name ?? 'Cliente' }}
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
         MODAL DE RASTREO

         Fuera del <aside> a propósito: ese contenedor usa `transform`
         (para el slide-in del sidebar en móvil), y un ancestro con
         `transform` crea un nuevo contenedor de posicionamiento que
         rompe `position: fixed` — el modal quedaba encajonado dentro
         del sidebar en vez de cubrir toda la pantalla.
    =========================================================== --}}
    <x-modal name="tracking-result" maxWidth="2xl">
        <div class="flex items-center justify-between border-b border-[#E5E5E0] px-4 py-3">
            <span class="text-sm font-semibold text-[#111111]">Resultado del rastreo</span>

            <button
                @click="$dispatch('close-modal', 'tracking-result')"
                class="text-[#B8B8B2] hover:text-[#111111]"
                aria-label="Cerrar"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <iframe
            :src="$store.tracking.src"
            class="h-[75vh] w-full"
        ></iframe>
    </x-modal>


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
            class="h-16 bg-white border-b border-[#E5E5E0] flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20"
        >

            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
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
                        {{ auth()->user()->name ?? 'Cliente' }}
                    </p>

                    <p class="text-xs text-[#6B6B66]">
                        Panel de Cliente
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

             Los componentes de App\Livewire\Client usan:
             #[Layout('layouts.client')]
        ======================================================= --}}
        <main class="p-4 lg:p-8 w-full max-w-7xl mx-auto">

            {{ $slot }}

        </main>

    </div>

</div>

<x-image-lightbox />

@livewireScripts

@stack('scripts')

</body>
</html>
