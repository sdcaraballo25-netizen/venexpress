<!DOCTYPE html>
<html lang="es" x-data="{ sidebarOpen: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Administrativo') — Venexpress</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 text-white transform transition-transform lg:translate-x-0 lg:static"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="h-16 flex items-center px-6 font-bold text-lg border-b border-gray-800">
                Venexpress
            </div>
            <nav class="px-3 py-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800' : '' }}">
                    Dashboard
                </a>
                {{-- Aquí se agregarán: Tasa BCV, Tarifas, Paquetes, Aliados, Choferes --}}
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
        <div class="flex-1 lg:ml-0">
            <header class="h-16 bg-white border-b flex items-center justify-between px-4 lg:px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden">
                    ☰
                </button>
                <div class="flex items-center gap-3 ml-auto">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:underline">Salir</button>
                    </form>
                </div>
            </header>

            <main class="p-4 lg:p-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>