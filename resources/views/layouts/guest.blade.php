<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'VenExpress') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            body, .font-sans, .font-display { font-family: 'Poppins', sans-serif; }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-blue-950 antialiased">
        <div class="min-h-screen grid lg:grid-cols-[minmax(0,0.85fr)_minmax(0,1fr)] bg-white">

            <aside class="relative overflow-hidden bg-blue-950 px-6 py-10 sm:px-10 lg:px-14 lg:py-14 flex flex-col justify-between">
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-[130%] rotate-[-8deg]">
                    <div class="h-6 w-full bg-blue-800/60"></div>
                    <div class="h-6 w-full bg-amber-400/80 mt-3"></div>
                    <div class="h-6 w-full bg-red-600/70 mt-3"></div>
                </div>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(251,191,36,0.12),transparent_55%)]"></div>

                <div class="relative">
                    <a href="{{ url('/') }}" wire:navigate>
                        <x-venexpress-logo variant="light" size="lg" />
                    </a>

                    <p class="mt-10 max-w-xs font-display text-2xl sm:text-3xl font-semibold leading-snug text-white hidden lg:block">
                        Gestiona tus guías, tarifas y comisiones desde un solo lugar.
                    </p>

                    <ul class="mt-8 space-y-4 hidden lg:block">
                        @foreach ([
                            'Rastreo de guías en tiempo real',
                            'Corte de caja y comisiones automáticos',
                            'Cobertura en las principales ciudades',
                        ] as $feature)
                            <li class="flex items-start gap-3 text-sm text-blue-100">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-400/15 text-amber-400">
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.4 7.4a1 1 0 0 1-1.4 0L3.3 9.5a1 1 0 1 1 1.4-1.4l3.9 3.9 6.7-6.7a1 1 0 0 1 1.4 0Z" clip-rule="evenodd"/></svg>
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="relative hidden lg:inline-flex items-center gap-2 rounded-full bg-white/5 px-3.5 py-1.5 ring-1 ring-white/10">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    <p class="text-xs font-medium text-blue-200">
                        @if (request()->routeIs('admin.login'))
                            Panel privado — solo personal administrativo VenExpress
                        @else
                            Clientes, repartidores y puntos aliados, todo en un solo lugar.
                        @endif
                    </p>
                </div>
            </aside>

            <main class="relative flex items-center justify-center px-6 py-12 sm:px-10">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
