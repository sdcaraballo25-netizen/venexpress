<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Venexpress') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif;
        }
    </style>
</head>

<body class="bg-[#F3F5F7] text-[#0B1220] antialiased">

    <div class="min-h-screen flex">

        {{-- SIDEBAR ALIADO --}}
        @if(auth()->check() && auth()->user()->isAliadoModule())
            <livewire:ally.navigation />
        @endif


        {{-- CONTENIDO PRINCIPAL --}}
        <div class="flex-1 min-w-0 ml-64">

            {{-- HEADER --}}
            <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-6 lg:px-8">

                <div>
                    <span class="text-sm text-[#64748B]">
                        {{ auth()->user()->isAliado() ? 'Aliado Operativo' : 'Taquilla' }}
                    </span>
                </div>

                <div class="flex items-center gap-4">

                    <span class="text-sm font-medium text-[#0F172A]">
                        {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="text-sm font-medium text-[#64748B] hover:text-red-600 transition"
                        >
                            Salir
                        </button>
                    </form>

                </div>

            </header>


            {{-- CONTENIDO --}}
            <main class="p-6 lg:p-8">

                @if (isset($header))
                    <div class="mb-6">
                        {{ $header }}
                    </div>
                @endif

                {{ $slot }}

            </main>

        </div>

    </div>

    @livewireScripts

</body>
</html>
