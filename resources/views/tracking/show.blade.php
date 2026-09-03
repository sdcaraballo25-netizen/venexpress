<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Venexpress - Resultado de rastreo</title>

    <link
        rel="icon"
        href="{{ asset('images/venexpress-logo.svg') }}"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js">
    </script>
</head>

<body class="antialiased bg-gray-50">

    {{-- NAVBAR --}}
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">

        <div
            class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between"
        >

            <a href="{{ route('home') }}">

                <img
                    src="{{ asset('images/venexpress-logo.svg') }}"
                    alt="Venexpress"
                    class="h-9"
                >

            </a>


            <div
                class="hidden md:flex items-center gap-9 text-sm font-medium text-gray-500"
            >

                <a
                    href="{{ route('home') }}"
                    class="hover:text-blue-950 transition"
                >
                    Inicio
                </a>

                <a
                    href="{{ route('home') }}#servicios"
                    class="hover:text-blue-950 transition"
                >
                    Servicios
                </a>

                <a
                    href="{{ route('public.calculator') }}"
                    class="hover:text-blue-950 transition"
                >
                    Calcular precio
                </a>

                <a
                    href="{{ route('public.offices') }}"
                    class="hover:text-blue-950 transition"
                >
                    Agencias aliadas
                </a>

                <a
                    href="{{ route('tracking.index') }}"
                    class="text-blue-950 font-semibold"
                >
                    Rastreo
                </a>

                <a
                    href="{{ route('home') }}#ayuda"
                    class="hover:text-blue-950 transition"
                >
                    Ayuda
                </a>

            </div>


            <a
                href="{{ route('login') }}"
                class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-6 py-2.5 rounded-lg transition"
            >
                Iniciar sesión
            </a>

        </div>

    </nav>


    <div class="max-w-4xl mx-auto px-6 py-10">

        {{-- BUSCADOR --}}
        <form
            action="{{ route('tracking.show') }}"
            method="GET"
            class="flex gap-3 mb-8"
        >

            <input
                type="text"
                name="guia"
                value="{{ $guia }}"
                placeholder="Ej. VE-2026-0001258"
                class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-blue-600 focus:border-blue-600"
            >

            <button
                type="submit"
                class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold px-6 rounded-lg transition"
            >
                Rastrear
            </button>

        </form>


        @if(!$package)

            {{-- SIN RESULTADO --}}
            <div
                class="bg-white rounded-2xl border border-gray-100 p-10 text-center"
            >

                <div
                    class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4"
                >

                    <i
                        class="fa-solid fa-magnifying-glass text-red-500 text-xl"
                    ></i>

                </div>


                <h2 class="font-semibold text-blue-950 text-lg">
                    No encontramos esa guía
                </h2>


                <p class="text-sm text-gray-500 mt-2">

                    Verifica que el número de guía
                    "{{ $guia }}"
                    esté escrito correctamente e intenta de nuevo.

                </p>

            </div>

        @else

            {{-- CARD RESULTADO --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8"
            >

                <div
                    class="flex flex-wrap items-start justify-between gap-4 mb-8"
                >

                    <div>

                        <div class="flex items-center gap-3">

                            <span class="font-semibold text-blue-950">

                                Guía:
                                {{ $package->tracking_number }}

                            </span>


                            <span
                                class="text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700"
                            >

                                {{ $package->status_label }}

                            </span>

                        </div>


                        <p class="text-sm text-gray-500 mt-2">

                            Origen:
                            {{ $package->origin_city }}

                            &nbsp;|&nbsp;

                            Destino:
                            {{ $package->destination_city }}

                        </p>

                    </div>


                    <div class="text-right">

                        <p class="text-xs text-gray-400">
                            Última actualización:
                        </p>

                        <p class="text-sm font-semibold text-blue-950">

                            {{ $package->updated_at->format('d/m/Y H:i') }}

                        </p>

                    </div>

                </div>


                {{-- TIMELINE --}}
                <div
                    class="flex items-start justify-between relative overflow-x-auto"
                >

                    <div
                        class="absolute top-6 left-0 right-0 h-1 bg-gray-200 z-0"
                    ></div>


                    <div
                        class="absolute top-6 left-0 h-1 bg-blue-900 z-0"
                        style="width: {{ $progressPercent }}%"
                    ></div>


                    @foreach($statusSteps as $step)

                        <div
                            class="relative z-10 flex flex-col items-center text-center w-1/6 px-1 min-w-[90px]"
                        >

                            <div
                                @class([
                                    'w-12 h-12 rounded-full flex items-center justify-center',

                                    'bg-blue-900 text-white'
                                        => $step['done'],

                                    'bg-amber-400 text-blue-950'
                                        => $step['current'],

                                    'bg-gray-200 text-gray-400'
                                        => !$step['done']
                                        && !$step['current'],
                                ])
                            >

                                <i
                                    class="fa-solid {{ $step['icon'] }}"
                                ></i>

                            </div>


                            <p
                                @class([
                                    'text-xs font-semibold mt-3',

                                    'text-blue-950'
                                        => $step['done']
                                        || $step['current'],

                                    'text-gray-400'
                                        => !$step['done']
                                        && !$step['current'],
                                ])
                            >

                                {{ $step['label'] }}

                            </p>


                            @if($step['timestamp'])

                                <p
                                    class="text-[11px] text-gray-400 mt-1"
                                >

                                    {!! $step['timestamp'] !!}

                                </p>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        @endif

    </div>

</body>
</html>
