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
        href="{{ asset('images/venexpress-logo.png') }}"
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
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

    {{-- NAVBAR (compartido) --}}
    <x-public-navbar />


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


                {{-- VOLVER A RASTREAR --}}
                <div class="mt-6">

                    <a
                        href="{{ route('tracking.index') }}"
                        class="inline-flex items-center justify-center gap-2
                               bg-blue-950 hover:bg-blue-900
                               text-white font-semibold
                               text-sm px-5 py-3 rounded-lg
                               transition"
                    >

                        <i class="fa-solid fa-arrow-left text-xs"></i>

                        Rastrear otra guía

                    </a>

                </div>

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
                                class="text-xs font-semibold px-3 py-1 rounded-full {{ match ($package->current_status) {
                                    \App\Models\Package::STATUS_EN_DEVOLUCION, \App\Models\Package::STATUS_ENTREGA_FALLIDA => 'bg-red-100 text-red-700',
                                    \App\Models\Package::STATUS_DEVUELTO => 'bg-slate-100 text-slate-700',
                                    \App\Models\Package::STATUS_EN_RUTA => 'bg-amber-100 text-amber-800',
                                    default => 'bg-green-100 text-green-700',
                                } }}"
                            >

                                {{ $currentStatusLabel ?? $package->status_label }}

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


                @if (! ($statusIsKnown ?? true))

                    <div
                        class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                    >

                        Este paquete tiene un estado especial que no forma
                        parte de la línea de tiempo estándar. La línea de
                        tiempo muestra el último paso conocido; consulta con
                        Venexpress para más detalles.

                    </div>

                @endif


                @if ($hasOpenIncident ?? false)

                    <div
                        class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
                    >

                        Tu envío tiene una incidencia en revisión, contáctanos.

                    </div>

                @endif

                {{-- Entrega a domicilio en curso / fallida y devolución al remitente --}}
                @if ($package->current_status === \App\Models\Package::STATUS_EN_RUTA)

                    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Tu envío va en camino a la dirección de entrega. Al recibirlo, dale al repartidor el PIN de entrega que te enviamos por correo (o muestra tu cédula).
                    </div>

                @elseif ($package->current_status === \App\Models\Package::STATUS_ENTREGA_FALLIDA)

                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        No pudimos entregar tu envío en este intento. Te contactaremos para coordinar un nuevo intento.
                    </div>

                @elseif ($package->current_status === \App\Models\Package::STATUS_EN_DEVOLUCION)

                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Este envío no se pudo entregar y está siendo devuelto al remitente. El remitente podrá retirarlo en la agencia donde lo envió.
                    </div>

                @elseif ($package->current_status === \App\Models\Package::STATUS_DEVUELTO)

                    <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        Este envío fue devuelto al remitente{{ $package->returned_at ? ' el '.$package->returned_at->format('d/m/Y') : '' }}.
                    </div>

                @endif


                {{-- TIMELINE --}}
                <div
                    class="flex items-start justify-between relative overflow-x-auto"
                >

                    <div
                        class="absolute top-6 left-0 right-0 h-1 bg-gray-200 z-0"
                    ></div>


                    <div
    class="h-2 rounded-full bg-blue-600"
    @style(['width' => $progressPercent . '%'])
></div>


                    @foreach($statusSteps as $step)

                        <div
                            class="relative z-10 flex flex-1 flex-col items-center text-center px-1 min-w-[90px]"
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


                {{-- ACCIONES --}}
                <div
                    class="mt-8 pt-6 border-t border-gray-100 flex justify-center"
                >

                    <a
                        href="{{ route('tracking.index') }}"
                        class="inline-flex items-center justify-center gap-2
                               bg-blue-950 hover:bg-blue-900
                               text-white font-semibold
                               text-sm px-5 py-3 rounded-lg
                               transition"
                    >

                        <i class="fa-solid fa-arrow-left text-xs"></i>

                        Rastrear otra guía

                    </a>

                </div>

            </div>

        @endif

    </div>

</body>
</html>