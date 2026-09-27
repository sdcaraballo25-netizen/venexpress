<!DOCTYPE html>
<html lang="es" x-data="{}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Venexpress' }}</title>

    <link rel="icon" href="{{ asset('images/venexpress-logo-solo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        #servicios,
        #ayuda,
        #aliados {
            scroll-margin-top: 90px;
        }

    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    @livewireStyles
</head>

<body class="antialiased bg-white">

    {{-- =========================================================
         NAVBAR PÚBLICA (compartido)
    ========================================================== --}}
    <x-public-navbar />


    {{-- =========================================================
         CONTENIDO
    ========================================================== --}}

    {{ $slot }}


    {{-- =========================================================
         FOOTER
    ========================================================== --}}
    <footer class="bg-blue-950 text-white/70 pt-14 pb-8">

        <div class="max-w-7xl mx-auto px-6">

            <div class="grid md:grid-cols-4 gap-10">

                {{-- Marca --}}
                <div>

                    <img
                        src="{{ asset('images/venexpress-logo-white.png') }}"
                        alt="Venexpress"
                        class="h-8 mb-4"
                    >

                    <p class="text-sm text-white/50">
                        Servicio nacional de encomiendas a través de agencias aliadas en toda Venezuela.
                    </p>

                </div>


                {{-- Navegación --}}
                <div>

                    <h4 class="text-white font-semibold text-sm mb-3">
                        Navegación
                    </h4>

                    <ul class="space-y-2 text-sm">

                        <li>
                            <a
                                href="{{ route('home') }}"
                                class="hover:text-white transition"
                            >
                                Inicio
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('home') }}#servicios"
                                class="hover:text-white transition"
                            >
                                Servicios
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('public.calculator') }}"
                                class="hover:text-white transition"
                            >
                                Calcular precio
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('public.offices') }}"
                                class="hover:text-white transition"
                            >
                                Agencias aliadas
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('tracking.index') }}"
                                class="hover:text-white transition"
                            >
                                Rastreo
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('public.help') }}"
                                class="hover:text-white transition"
                            >
                                Ayuda
                            </a>
                        </li>

                    </ul>

                </div>


                {{-- Servicios --}}
                <div>

                    <h4 class="text-white font-semibold text-sm mb-3">
                        Servicios
                    </h4>

                    <ul class="space-y-2 text-sm">

                        <li>
                            <a
                                href="{{ route('home') }}#servicios"
                                class="hover:text-white transition"
                            >
                                Envíos nacionales
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('home') }}#servicios"
                                class="hover:text-white transition"
                            >
                                Sobres
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('home') }}#servicios"
                                class="hover:text-white transition"
                            >
                                Entrega a domicilio
                            </a>
                        </li>

                    </ul>

                </div>


                {{-- Ayuda --}}
                <div>

                    <h4 class="text-white font-semibold text-sm mb-3">
                        Ayuda
                    </h4>

                    <ul class="space-y-2 text-sm">

                        <li>
                            <a
                                href="{{ route('public.help') }}"
                                class="hover:text-white transition"
                            >
                                Preguntas frecuentes
                            </a>
                        </li>

                        <li>
                            <a
                                href="{{ route('public.recommendations') }}"
                                class="hover:text-white transition"
                            >
                                Recomendaciones
                            </a>
                        </li>

                        <li>
                            <a
                                href="mailto:info@venexpress.com"
                                class="hover:text-white transition"
                            >
                                Contáctanos
                            </a>
                        </li>

                    </ul>

                </div>

            </div>


            {{-- Copyright --}}
            <div class="border-t border-white/10 mt-10 pt-6 text-xs text-white/40 text-center">

                &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.

            </div>

        </div>

    </footer>


    <x-image-lightbox />

    {{-- =========================================================
         JAVASCRIPT
    ========================================================== --}}
    @livewireScripts


</body>
</html>