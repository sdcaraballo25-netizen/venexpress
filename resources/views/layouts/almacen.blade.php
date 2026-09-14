<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Almacén' }} — Venexpress</title>

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

<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">

<div class="min-h-screen flex">

    {{-- =========================================================
         SIDEBAR
    ========================================================== --}}

    <aside
        class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-[#E2E8F0] flex flex-col
               transform transition-transform duration-200 md:relative md:translate-x-0"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >

        {{-- LOGO --}}
        <div class="px-6 pt-7 pb-8">
            <div class="flex items-center gap-3">

                <div class="h-11 w-11 rounded-xl bg-purple-900 flex items-center justify-center text-white shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7l9-4 9 4M3 7l9 4m-9-4v10l9 4m0-10l9-4m-9 4v10m9-14v10l-9 4" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <span class="font-display font-bold text-xl text-[#0F172A] block leading-none">Venexpress</span>
                    <span class="text-xs text-[#94A3B8] block mt-1">Panel Almacén</span>
                </div>

            </div>
        </div>

        {{-- NAVEGACIÓN --}}
        <nav class="flex-1 px-4 pb-6 overflow-y-auto">

            <p class="px-3 mb-3 text-xs font-semibold uppercase tracking-wider text-[#94A3B8]">
                Principal
            </p>

            {{-- DASHBOARD --}}
            <a
                href="{{ route('almacen.dashboard') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('almacen.dashboard')
                        ? 'bg-purple-50 text-purple-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l9-9 9 9M5 10v10h14V10M9 20v-6h6v6" />
                </svg>
                <span>Resumen</span>
            </a>

            {{-- AYUDA --}}
            <a
                href="{{ route('almacen.help') }}"
                wire:navigate
                @click="sidebarOpen = false"
                class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors
                    {{ request()->routeIs('almacen.help')
                        ? 'bg-purple-50 text-purple-900'
                        : 'text-[#64748B] hover:bg-slate-50 hover:text-[#0F172A]' }}"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Ayuda</span>
            </a>

        </nav>

        {{-- USUARIO --}}
        <div class="border-t border-[#E2E8F0] px-5 py-5 bg-white">

            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-purple-900 text-white flex items-center justify-center font-bold uppercase shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-[#0F172A] truncate">{{ auth()->user()->name ?? 'Almacén' }}</p>
                    <p class="text-xs text-[#64748B] truncate">{{ auth()->user()?->warehouse?->name ?? 'Personal de almacén' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-red-500 hover:bg-red-50 hover:text-red-700 transition-colors">
                    Cerrar sesión
                </button>
            </form>

        </div>

    </aside>

    {{-- OVERLAY MÓVIL --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-30 md:hidden"></div>

    {{-- ÁREA PRINCIPAL --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 lg:px-8 sticky top-0 z-20">

            <button
                type="button"
                @click="sidebarOpen = true"
                class="md:hidden h-10 w-10 rounded-xl flex items-center justify-center text-[#0F172A] hover:bg-slate-100"
                aria-label="Abrir menú"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <div class="flex items-center gap-4 ml-auto">

                <div class="hidden sm:flex items-center gap-2 rounded-full border border-purple-200 bg-purple-50 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-purple-600"></span>
                    <span class="text-xs font-medium text-purple-800">
                        {{ auth()->user()?->warehouse?->name ?? 'Sin almacén asignado' }}
                    </span>
                </div>

                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-[#0F172A]">{{ auth()->user()->name ?? 'Almacén' }}</p>
                    <p class="text-xs text-[#64748B]">Panel de almacén</p>
                </div>

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
        <main class="p-4 lg:p-8 w-full max-w-7xl mx-auto">
            {{ $slot }}
        </main>

    </div>

</div>

@livewireScripts

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

@stack('scripts')

</body>

</html>
