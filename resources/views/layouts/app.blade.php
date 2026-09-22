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

<body class="bg-[#F7F7F4] text-[#111111] antialiased">

    <div class="min-h-screen flex">

        {{-- NAVEGACIÓN --}}
        {{--
            El módulo Ally tiene su propio layout dedicado
            (layouts/ally.blade.php) con su propio menú lateral.
            Los componentes Livewire de Ally usan
            #[Layout('layouts.ally')] y nunca pasan por aquí.
            Este layout genérico (usado por /dashboard y /profile)
            solo necesita el menú estándar para el resto de roles.
        --}}
        @if(auth()->check())
            <livewire:layout.navigation />
        @endif


        {{-- CONTENIDO PRINCIPAL --}}
        <div class="flex-1 min-w-0">

            {{-- HEADER --}}
            <header class="h-16 bg-white border-b border-[#E5E5E0] flex items-center justify-between px-6 lg:px-8">

                <div>
                    <span class="text-sm text-[#6B6B66]">
                        {{ \App\Models\User::roleLabels()[auth()->user()->role] ?? '' }}
                    </span>
                </div>

                <div class="flex items-center gap-4">

                    <span class="text-sm font-medium text-[#111111]">
                        {{ auth()->user()->name }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="text-sm font-medium text-[#6B6B66] hover:text-red-600 transition"
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

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    @stack('scripts')

</body>
</html>
