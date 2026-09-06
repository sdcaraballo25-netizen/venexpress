<!DOCTYPE html>
<html lang="es">
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

        .main-nav-links {
            display: flex;
            align-items: center;
            gap: 2.25rem;
        }

        .main-nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.45rem 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .main-nav-link:hover,
        .main-nav-link.is-active {
            color: #172554;
        }

        .main-nav-link.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.1rem;
            height: 2px;
            border-radius: 999px;
            background: #dc2626;
        }

        @media (max-width: 767px) {
            .main-nav-links {
                display: none;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    @livewireStyles
</head>

<body class="antialiased bg-white">

    {{-- =========================================================
         NAVBAR PÚBLICA
    ========================================================== --}}
    <nav
        id="main-navbar"
        class="bg-white border-b border-gray-100 sticky top-0 z-50"
    >
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            {{-- LOGO --}}
            <a
                href="{{ route('home') }}"
                class="shrink-0"
                aria-label="Venexpress - Inicio"
            >
                <img
                    src="{{ asset('images/venexpress-logo.png') }}"
                    alt="Venexpress"
                    class="h-9 w-auto"
                >
            </a>


            {{-- NAVEGACIÓN DESKTOP --}}
            <div class="main-nav-links">

                {{-- Inicio --}}
                <a
                    href="{{ route('home') }}"
                    class="main-nav-link {{ request()->routeIs('home') ? 'is-active' : '' }}"
                >
                    Inicio
                </a>


                {{-- Servicios --}}
                <a
                    href="{{ route('home') }}#servicios"
                    class="main-nav-link"
                >
                    Servicios
                </a>


                {{-- Calcular precio --}}
                <a
                    href="{{ route('public.calculator') }}"
                    class="main-nav-link {{ request()->routeIs('public.calculator') ? 'is-active' : '' }}"
                >
                    Calcular precio
                </a>


                {{-- Agencias aliadas --}}
                <a
                    href="{{ route('public.offices') }}"
                    class="main-nav-link {{ request()->routeIs('public.offices') ? 'is-active' : '' }}"
                >
                    Agencias aliadas
                </a>


                {{-- Rastreo --}}
                <a
                    href="{{ route('tracking.index') }}"
                    class="main-nav-link {{ request()->routeIs('tracking.*') ? 'is-active' : '' }}"
                >
                    Rastreo
                </a>


                {{-- Ayuda --}}
                <a
                    href="{{ route('home') }}#ayuda"
                    class="main-nav-link"
                >
                    Ayuda
                </a>

            </div>


            {{-- LOGIN --}}
            <div class="flex items-center gap-3">

                <a
                    href="{{ route('login') }}"
                    class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-6 py-2.5 rounded-lg transition inline-flex items-center justify-center"
                >
                    Iniciar sesión
                </a>

                {{-- BOTÓN MENÚ MÓVIL --}}
                <button
                    id="mobile-menu-button"
                    type="button"
                    class="md:hidden w-10 h-10 rounded-lg border border-gray-200 text-blue-950 flex items-center justify-center"
                    aria-label="Abrir menú"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                >
                    <i
                        id="mobile-menu-icon"
                        class="fa-solid fa-bars"
                    ></i>
                </button>

            </div>

        </div>


        {{-- =====================================================
             MENÚ MÓVIL
        ====================================================== --}}
        <div
            id="mobile-menu"
            class="hidden border-t border-gray-100 bg-white md:hidden"
        >
            <div class="max-w-7xl mx-auto px-6 py-3">

                <a
                    href="{{ route('home') }}"
                    class="mobile-menu-link block py-3 text-sm font-semibold {{ request()->routeIs('home') ? 'text-blue-950' : 'text-gray-600' }}"
                >
                    Inicio
                </a>


                <a
                    href="{{ route('home') }}#servicios"
                    class="mobile-menu-link block py-3 text-sm text-gray-600"
                >
                    Servicios
                </a>


                <a
                    href="{{ route('public.calculator') }}"
                    class="mobile-menu-link block py-3 text-sm {{ request()->routeIs('public.calculator') ? 'font-semibold text-blue-950' : 'text-gray-600' }}"
                >
                    Calcular precio
                </a>


                <a
                    href="{{ route('public.offices') }}"
                    class="mobile-menu-link block py-3 text-sm {{ request()->routeIs('public.offices') ? 'font-semibold text-blue-950' : 'text-gray-600' }}"
                >
                    Agencias aliadas
                </a>


                <a
                    href="{{ route('tracking.index') }}"
                    class="mobile-menu-link block py-3 text-sm {{ request()->routeIs('tracking.*') ? 'font-semibold text-blue-950' : 'text-gray-600' }}"
                >
                    Rastreo
                </a>


                <a
                    href="{{ route('home') }}#ayuda"
                    class="mobile-menu-link block py-3 text-sm text-gray-600"
                >
                    Ayuda
                </a>

            </div>
        </div>

    </nav>


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
                                href="{{ route('home') }}#ayuda"
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
                                href="{{ route('home') }}#ayuda"
                                class="hover:text-white transition"
                            >
                                Preguntas frecuentes
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


    {{-- =========================================================
         JAVASCRIPT
    ========================================================== --}}
    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const button = document.getElementById('mobile-menu-button');
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('mobile-menu-icon');

            if (button && menu && icon) {

                const closeMenu = () => {
                    menu.classList.add('hidden');

                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');

                    button.setAttribute('aria-expanded', 'false');
                };


                button.addEventListener('click', function () {

                    const isOpen = !menu.classList.contains('hidden');

                    if (isOpen) {

                        closeMenu();

                    } else {

                        menu.classList.remove('hidden');

                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-xmark');

                        button.setAttribute('aria-expanded', 'true');

                    }

                });


                document
                    .querySelectorAll('.mobile-menu-link')
                    .forEach(link => {

                        link.addEventListener('click', closeMenu);

                    });

            }

        });
    </script>

</body>
</html>