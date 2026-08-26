<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Venexpress') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body class="min-h-screen bg-slate-100">

    {{-- MENÚ LATERAL --}}
    <aside class="fixed inset-y-0 left-0 z-50 w-64 border-r border-slate-200 bg-white">

        {{-- Logo --}}
        <div class="flex h-20 items-center border-b border-slate-200 px-5">
            <a href="{{ route('ally.dashboard') }}"
               wire:navigate
               class="flex items-center gap-3">

                <x-application-logo class="h-10 w-auto fill-current text-slate-800"/>

                <div>
                    <div class="text-base font-semibold text-slate-800">
                        Venexpress
                    </div>

                    <div class="text-xs text-slate-500">
                        Agencia Aliada
                    </div>
                </div>
            </a>
        </div>

        {{-- NAV --}}
        <nav class="flex-1 overflow-y-auto px-3 py-5">

            <div class="mb-2 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Principal
            </div>

            <a href="{{ route('ally.dashboard') }}"
               wire:navigate
               class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
               {{ request()->routeIs('ally.dashboard')
                    ? 'bg-blue-50 text-blue-900'
                    : 'text-slate-600 hover:bg-slate-50' }}">

                <span>Resumen</span>
            </a>


            <div class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                Operaciones
            </div>

            <a href="{{ route('ally.packages.create') }}"
               wire:navigate
               class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium
               {{ request()->routeIs('ally.packages.create')
                    ? 'bg-blue-50 text-blue-900'
                    : 'text-slate-600 hover:bg-slate-50' }}">

                <span>Registrar pedido</span>
            </a>

            <a href="#"
               class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">

                <span>Recepción de paquetes</span>
            </a>

            <a href="#"
               class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">

                <span>Seguimiento de envíos</span>
            </a>


            {{-- SOLO ALIADO OPERATIVO --}}
            @if(auth()->user()->role === 'admin_aliado')

                <div class="mb-2 mt-6 px-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                    Administración
                </div>

                <a href="#"
                   class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Gestión de Taquillas
                </a>

                <a href="#"
                   class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Cobro en destino
                </a>

                <a href="#"
                   class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Incidencias
                </a>

                <a href="#"
                   class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Comisiones y saldo
                </a>

                <a href="#"
                   class="mb-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                    Historial financiero
                </a>

            @endif

        </nav>


        {{-- USUARIO --}}
        <div class="absolute bottom-0 w-full border-t border-slate-200 bg-white p-4">

            <div class="mb-3 rounded-xl bg-slate-50 px-3 py-3">
                <div class="text-sm font-semibold text-slate-800">
                    {{ auth()->user()->name }}
                </div>

                <div class="text-xs text-slate-500">
                    {{ auth()->user()->email }}
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600">
                    Cerrar sesión
                </button>
            </form>

        </div>

    </aside>


    {{-- CONTENIDO --}}
    <main class="ml-64 min-h-screen">
        {{ $slot ?? '' }}

        @yield('content')
    </main>

    @livewireScripts
</body>
</html>
