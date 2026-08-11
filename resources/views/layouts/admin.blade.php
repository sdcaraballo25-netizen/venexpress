<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel Administrativo' }} — Venexpress</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        .font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }
        .font-tracking { font-family: 'JetBrains Mono', ui-monospace, monospace; }
    </style>
</head>
<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-[#0B1220] text-white transform transition-transform lg:translate-x-0 lg:static"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="h-16 flex items-center gap-2.5 px-6 border-b border-white/10">
                <span class="flex h-7 w-7 items-center justify-center rounded-md bg-[#FF6A1A] text-white text-sm font-bold font-display">V</span>
                <span class="font-display font-semibold text-[15px] tracking-tight">Venexpress</span>
            </div>

            <nav class="px-3 py-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('admin.dashboard') ? 'bg-[#FF6A1A]' : 'bg-white/20' }}"></span>
                    Dashboard
                </a>

                <a href="{{ route('admin.bcv-rates') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.bcv-rates') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('admin.bcv-rates') ? 'bg-[#FF6A1A]' : 'bg-white/20' }}"></span>
                    Tasa BCV
                </a>

                <a href="{{ route('admin.rate-matrices') }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('admin.rate-matrices') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ request()->routeIs('admin.rate-matrices') ? 'bg-[#FF6A1A]' : 'bg-white/20' }}"></span>
                    Matrices de tarifas
                </a>

                <div class="pt-4 mt-4 border-t border-white/10">
                    <p class="px-3 text-[11px] uppercase tracking-wider text-white/30 font-medium mb-1">Próximamente</p>
                    @foreach (['Paquetes', 'Aliados', 'Choferes'] as $item)
                        <span class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-white/25 cursor-default">
                            <span class="h-1.5 w-1.5 rounded-full bg-white/10"></span>
                            {{ $item }}
                        </span>
                    @endforeach
                </div>
            </nav>
        </aside>

        {{-- Overlay móvil --}}
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 z-20 lg:hidden"
            x-cloak
        ></div>

        {{-- Contenido --}}
        <div class="flex-1 lg:ml-0 min-w-0">
            <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 lg:px-8">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-[#0B1220]">
                    ☰
                </button>
                <div class="flex items-center gap-4 ml-auto">
                    <span class="text-sm text-[#64748B]">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-[#64748B] hover:text-[#FF6A1A] transition-colors">Salir</button>
                    </form>
                </div>
            </header>

            <main class="p-4 lg:p-8 max-w-6xl">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>