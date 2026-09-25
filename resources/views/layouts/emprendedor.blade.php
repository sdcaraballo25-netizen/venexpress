<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Emprendedor' }} — Venexpress</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }
    </style>

</head>

<body class="bg-[#F7F7F4] text-[#111111] antialiased">

<div class="min-h-screen flex">

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-[#E5E5E0] flex flex-col
               transform transition-transform duration-200 md:relative md:translate-x-0"
        :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >

        {{-- LOGO --}}
        <div class="px-6 pt-7 pb-8">
            <div class="flex items-center gap-3">

                <div class="h-11 w-11 rounded-xl bg-amber-400 flex items-center justify-center text-[#111111] shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h18M3 3v2l7 6v7l4 2v-9l7-6V3" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <span class="font-display font-bold text-xl text-[#111111] block leading-none">Venexpress</span>
                    <span class="text-xs text-[#B8B8B2] block mt-1">Panel Emprendedor</span>
                </div>

            </div>
        </div>

        {{-- NAVEGACIÓN --}}
        <nav class="flex-1 px-4 pb-6 overflow-y-auto">

            <p class="px-3 mb-3 text-xs font-semibold uppercase tracking-wider text-[#B8B8B2]">
                Principal
            </p>

            {{-- DASHBOARD --}}
            <a
                href="{{ route('emprendedor.dashboard') }}"
                wire:navigate
                @click="$store.sidebar.open = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('emprendedor.dashboard')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6" />
                </svg>
                <span>Resumen</span>
            </a>

            {{-- PRODUCTOS --}}
            <a
                href="{{ route('emprendedor.productos') }}"
                wire:navigate
                @click="$store.sidebar.open = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('emprendedor.productos')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span>Mis Productos</span>
            </a>

            {{-- PEDIDOS --}}
            <a
                href="{{ route('emprendedor.pedidos') }}"
                wire:navigate
                @click="$store.sidebar.open = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('emprendedor.pedidos')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Pedidos</span>
            </a>

            {{-- REPORTES --}}
            <a
                href="{{ route('emprendedor.reportes') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('emprendedor.reportes')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2" />
                </svg>
                <span>Reportes</span>
            </a>

            {{-- MI TIENDA (perfil público) --}}
            <a
                href="{{ route('emprendedor.perfil') }}"
                wire:navigate
                @click="$store.sidebar.open = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('emprendedor.perfil')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 21h18M5 21V7l8-4v18M13 21V7l6 4v10M9 9v.01M9 12v.01M9 15v.01" />
                </svg>
                <span>Mi Tienda</span>
            </a>

            {{-- RECOMENDACIONES --}}
            <a
                href="{{ route('recommendations.create') }}"
                wire:navigate
                @click="$store.sidebar.open = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('recommendations.create')
                        ? 'bg-amber-400 text-[#111111]'
                        : 'text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z" />
                </svg>
                <span>Recomendaciones</span>
            </a>

        </nav>

        {{-- USUARIO --}}
        <div class="border-t border-[#E5E5E0] px-5 py-5 bg-white">

            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-amber-400 text-[#111111] flex items-center justify-center font-bold uppercase shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'E', 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-[#111111] truncate">{{ auth()->user()->name ?? 'Emprendedor' }}</p>
                    <p class="text-xs text-[#6B6B66] truncate">{{ auth()->user()?->emprendedor?->business_name ?? 'Mi negocio' }}</p>
                </div>
            </div>

            <a
                href="{{ route('profile') }}"
                class="block w-full mt-4 rounded-xl px-3 py-2 text-sm font-medium text-[#6B6B66] hover:bg-slate-50 hover:text-[#111111] transition-colors"
            >
                Mi Perfil
            </a>

            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors">
                    Cerrar sesión
                </button>
            </form>

        </div>

    </aside>

    {{-- OVERLAY MÓVIL --}}
    <div x-show="$store.sidebar.open" x-cloak @click="$store.sidebar.open = false" class="fixed inset-0 bg-black/40 z-30 md:hidden"></div>

    {{-- ÁREA PRINCIPAL --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <header class="h-16 bg-white border-b border-[#E5E5E0] flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20">

            <button
                type="button"
                @click="$store.sidebar.open = true"
                class="md:hidden h-10 w-10 rounded-xl flex items-center justify-center text-[#111111] hover:bg-slate-100"
                aria-label="Abrir menú"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center gap-4 ml-auto">

                <div class="hidden sm:flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-medium text-amber-800">
                        {{ auth()->user()?->emprendedor?->business_name ?? 'Sin negocio' }}
                    </span>
                </div>

                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-[#111111]">{{ auth()->user()->name ?? 'Emprendedor' }}</p>
                    <p class="text-xs text-[#6B6B66]">Panel de emprendedor</p>
                </div>

                <a
                    href="{{ route('profile') }}"
                    class="h-10 px-4 inline-flex items-center rounded-xl text-sm font-medium text-[#6B6B66] border border-[#E5E5E0] hover:bg-slate-50 hover:text-[#111111] transition-colors"
                >
                    Mi Perfil
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="h-10 px-4 rounded-xl text-sm font-medium text-red-500 border border-red-200 hover:bg-red-50 hover:text-red-700 transition-colors">
                        Salir
                    </button>
                </form>

            </div>

        </header>

        {{-- CONTENIDO LIVEWIRE --}}
        {{-- El marketplace (public.marketplace*) gestiona su propio ancho — ver el mismo comentario en layouts/client.blade.php. --}}
        <main class="p-4 lg:p-8 w-full mx-auto {{ request()->routeIs('public.marketplace*') ? 'max-w-none' : 'max-w-7xl' }}">
            {{ $slot }}
        </main>

    </div>

</div>

<x-confirm-dialog />
<x-image-lightbox />

@livewireScripts

@stack('scripts')

</body>

</html>
