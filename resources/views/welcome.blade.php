<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Venexpress | Envíos y rastreo en Venezuela</title>

    <link rel="icon" href="{{ asset('images/venexpress-logo-solo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

        /* =====================================================
           NAVEGACIÓN
        ====================================================== */

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
            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }

        .main-nav-link:hover,
        .main-nav-link.is-active {
            color: #172554;
        }

        .main-nav-link:hover {
            transform: translateY(-1px);
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

        /* =====================================================
           HERO
        ====================================================== */

        .hero-section {
            min-height: 500px;
        }

        .hero-content {
            min-height: 500px;
        }

        .hero-vehicle {
            transform: translateY(28px) scale(1.06);
            transform-origin: center bottom;
        }

        .hero-title {
    font-size: 3.7rem;
    line-height: 1;
}

        .hero-description {
            max-width: 560px;
        }

        .coverage-map {
            max-width: 31rem;
        }

        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (min-width: 1280px) {
    .hero-title {
        font-size: 3.95rem;
        line-height: 1;
    }

    .hero-vehicle {
        transform: translateY(32px) scale(1.10);
    }
}

        @media (max-width: 1023px) {
            .hero-title {
                font-size: 3.75rem;
            }

            .hero-vehicle {
                transform: translateY(20px) scale(1.02);
            }

            .main-nav-links {
                gap: 1.4rem;
            }
        }

        @media (max-width: 767px) {
            .hero-section,
            .hero-content {
                min-height: auto;
            }

            .hero-content {
                padding-top: 3.5rem;
                padding-bottom: 3.5rem;
            }

            .hero-title {
                font-size: 3rem;
            }

            .hero-vehicle {
                transform: none;
                margin-top: 1.5rem;
            }

            .main-nav-links {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2.65rem;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>

<body class="antialiased bg-white">


    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    <nav id="main-navbar" class="bg-white border-b border-gray-100 sticky top-0 z-50">

        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

            <a href="{{ route('home') }}"
               class="shrink-0"
               aria-label="Venexpress - Inicio">

                <img
                    src="{{ asset('images/venexpress-logo.png') }}"
                    alt="Venexpress"
                    class="h-9 w-auto"
                >

            </a>


            <div class="main-nav-links">

                <a href="{{ route('home') }}"
                   class="main-nav-link is-active">
                    Inicio
                </a>

                <a href="#servicios"
                   class="main-nav-link">
                    Servicios
                </a>

                <a href="{{ route('public.calculator') }}"
                   class="main-nav-link">
                    Calcular precio
                </a>

                <a href="{{ route('public.offices') }}"
                   class="main-nav-link">
                    Agencias aliadas
                </a>

                <a href="{{ route('tracking.index') }}"
                   class="main-nav-link">
                    Rastreo
                </a>

                <a href="#ayuda"
                   class="main-nav-link">
                    Ayuda
                </a>

            </div>


            <div class="flex items-center gap-3">

                <a
                    href="{{ route('login') }}"
                    class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-6 py-2.5 rounded-lg transition inline-flex items-center justify-center shadow-sm hover:shadow-md"
                >
                    Iniciar sesión
                </a>


                <button
                    id="mobile-menu-button"
                    type="button"
                    class="md:hidden w-10 h-10 rounded-lg border border-gray-200 text-blue-950 flex items-center justify-center"
                    aria-label="Abrir menú"
                    aria-expanded="false"
                    aria-controls="mobile-menu"
                >
                    <i id="mobile-menu-icon" class="fa-solid fa-bars"></i>
                </button>

            </div>

        </div>


        {{-- MENÚ MÓVIL --}}
        <div id="mobile-menu"
             class="hidden border-t border-gray-100 bg-white md:hidden">

            <div class="max-w-7xl mx-auto px-6 py-3">

                <a href="{{ route('home') }}"
                   class="mobile-menu-link block py-3 text-sm font-semibold text-blue-950">
                    Inicio
                </a>

                <a href="#servicios"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Servicios
                </a>

                <a href="{{ route('public.calculator') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Calcular precio
                </a>

                <a href="{{ route('public.offices') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Agencias aliadas
                </a>

                <a href="{{ route('tracking.index') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Rastreo
                </a>

                <a href="#ayuda"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Ayuda
                </a>

            </div>

        </div>

    </nav>



    {{-- =========================================================
         HERO
    ========================================================== --}}
    <section class="hero-section relative overflow-hidden bg-white">

        {{-- Fondo --}}
        <div class="absolute inset-0 z-0 pointer-events-none">

            <img
                src="{{ asset('images/skyline-hero.png') }}"
                alt=""
                class="absolute inset-0 w-full h-full object-cover object-right opacity-75"
            >

        </div>


        <div class="hero-content relative z-10 max-w-7xl mx-auto px-6 py-10 md:py-12 grid md:grid-cols-2 gap-6 lg:gap-10 items-center">


            {{-- =================================================
                 TEXTO
            ================================================== --}}
            <div class="relative z-20 max-w-2xl">

                <h1 class="hero-title font-extrabold text-blue-950 leading-[0.82] tracking-tight">

    <span class="block whitespace-nowrap">
        Envía fácil.
    </span>

    <span class="block text-red-600 whitespace-nowrap">
        Rastrea siempre.
    </span>

</h1>


                <p class="hero-description mt-6 text-gray-600 text-lg leading-7.5 font-medium">
                    Calcula el precio de tu envío, encuentra una agencia y consulta el estado de tu paquete de forma sencilla.
                </p>


                {{-- =================================================
                     BOTONES
                ================================================== --}}
                <div class="mt-7 flex flex-nowrap items-center gap-2">


                    {{-- Calcular --}}
                    <a
                        href="{{ route('public.calculator') }}"
                        class="group inline-flex items-center gap-1.5 bg-white/50 hover:bg-white/80 backdrop-blur-sm border border-blue-900/20 hover:border-blue-900/40 text-blue-950 text-sm font-bold px-3 py-2 rounded-lg transition duration-200 shadow-sm hover:shadow-md"
                    >

                        <span class="w-5.5 h-5.5 rounded-md bg-blue-50/80 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-calculator text-[10px] text-blue-900"></i>
                        </span>

                        Calcular precio

                        <i class="fa-solid fa-arrow-right text-[10px] text-blue-900/60 transition-transform group-hover:translate-x-0.5"></i>

                    </a>


                    {{-- Agencias --}}
                    <a
                        href="{{ route('public.offices') }}"
                        class="group inline-flex items-center gap-1.5 bg-white/50 hover:bg-white/80 backdrop-blur-sm border border-blue-900/20 hover:border-blue-900/40 text-blue-950 text-sm font-bold px-3 py-2 rounded-lg transition duration-200 shadow-sm hover:shadow-md"
                    >

                        <span class="w-5.5 h-5.5 rounded-md bg-blue-50/80 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-location-dot text-amber-500 text-[10px]"></i>
                        </span>

                        Agencias cercanas

                        <i class="fa-solid fa-arrow-right text-[10px] text-blue-900/60 transition-transform group-hover:translate-x-0.5"></i>

                    </a>


                    {{-- Rastreo --}}
                    <a
                        href="{{ route('tracking.index') }}"
                        class="group inline-flex items-center gap-1.5 bg-white/50 hover:bg-white/80 backdrop-blur-sm border border-blue-900/20 hover:border-blue-900/40 text-blue-950 text-sm font-bold px-3 py-2 rounded-lg transition duration-200 shadow-sm hover:shadow-md"
                    >

                        <span class="w-5.5 h-5.5 rounded-md bg-blue-50/80 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-location-crosshairs text-blue-900 text-[10px]"></i>
                        </span>

                        Rastrear envío

                        <i class="fa-solid fa-arrow-right text-[10px] text-blue-900/60 transition-transform group-hover:translate-x-0.5"></i>

                    </a>

                </div>

            </div>



            {{-- =================================================
                 VEHÍCULO
            ================================================== --}}
            <div class="relative flex justify-center md:justify-end items-end">

                <div class="hero-vehicle relative w-full max-w-md lg:max-w-2xl">

                    <img
                        src="{{ asset('images/van-hero.png') }}"
                        alt="Furgoneta Venexpress"
                        class="w-full relative z-10 drop-shadow-[0_30px_25px_rgba(15,23,55,0.25)]"
                    >


                    {{-- Sombra debajo de la camioneta --}}
                    <div
                        class="absolute left-1/2 bottom-1 -translate-x-1/2 w-[68%] h-5 bg-blue-950/25 rounded-full blur-md"
                    ></div>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
         FRANJA DE BENEFICIOS
    ========================================================== --}}
    <section class="bg-blue-950">

        <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-6">


            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved text-amber-400"></i>
                </div>

                <span class="text-white text-sm">
                    Envíos seguros a nivel nacional
                </span>

            </div>


            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-location-dot text-amber-400"></i>
                </div>

                <span class="text-white text-sm">
                    Cobertura en las principales ciudades
                </span>

            </div>


            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-handshake text-amber-400"></i>
                </div>

                <span class="text-white text-sm">
                    Aliados comerciales de confianza
                </span>

            </div>


            <div class="flex items-center gap-3">

                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-headset text-amber-400"></i>
                </div>

                <span class="text-white text-sm">
                    Atención al cliente 24/7
                </span>

            </div>

        </div>

    </section>



    {{-- =========================================================
         ACCESOS RÁPIDOS
    ========================================================== --}}
    <section id="servicios" class="bg-white">

        <div class="max-w-7xl mx-auto px-6 py-16">

            <div class="text-center mb-10">

                <h2 class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3">
                    ¿Qué necesitas hacer?

                    <span class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"></span>
                </h2>

                <p class="mt-4 text-sm text-gray-500">
                    Todo lo que necesitas para gestionar tu envío.
                </p>

            </div>


            <div class="grid md:grid-cols-3 gap-5 lg:gap-6 items-stretch">


                {{-- CALCULAR --}}
                <a
                    href="{{ route('public.calculator') }}"
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 p-6 md:p-7 flex flex-col min-h-[245px] transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-900/20"
                >

                    <div class="absolute top-0 left-0 right-0 h-1 bg-blue-900"></div>

                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-6 transition duration-300 group-hover:bg-blue-900">
                        <i class="fa-solid fa-calculator text-blue-900 text-lg transition duration-300 group-hover:text-white"></i>
                    </div>

                    <div>

                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-900 mb-2">
                            Tu envío
                        </p>

                        <h3 class="text-lg font-bold text-blue-950">
                            Calcula tu envío
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 leading-6 max-w-sm">
                            Obtén una estimación del precio de tu envío en pocos pasos.
                        </p>

                    </div>

                    <span class="inline-flex items-center mt-auto pt-6 text-sm font-semibold text-blue-900 group-hover:text-red-600 transition">
                        Calcular precio
                        <i class="fa-solid fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                    </span>

                </a>



                {{-- AGENCIAS --}}
                <a
                    href="{{ route('public.offices') }}"
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 p-6 md:p-7 flex flex-col min-h-[245px] transition duration-300 hover:-translate-y-1 hover:border-amber-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-amber-400/20"
                >

                    <div class="absolute top-0 left-0 right-0 h-1 bg-amber-400"></div>

                    <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center mb-6 transition duration-300 group-hover:bg-amber-400">
                        <i class="fa-solid fa-location-dot text-amber-600 text-lg transition duration-300 group-hover:text-blue-950"></i>
                    </div>

                    <div>

                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-amber-600 mb-2">
                            Punto cercano
                        </p>

                        <h3 class="text-lg font-bold text-blue-950">
                            Encuentra una agencia
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 leading-6 max-w-sm">
                            Localiza el punto Venexpress más conveniente para entregar tu paquete.
                        </p>

                    </div>

                    <span class="inline-flex items-center mt-auto pt-6 text-sm font-semibold text-blue-900 group-hover:text-red-600 transition">
                        Descubrir agencias
                        <i class="fa-solid fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                    </span>

                </a>



                {{-- RASTREO --}}
                <a
                    href="{{ route('tracking.index') }}"
                    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 p-6 md:p-7 flex flex-col min-h-[245px] transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-900/20"
                >

                    <div class="absolute top-0 left-0 right-0 h-1 bg-blue-900"></div>

                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center mb-6 transition duration-300 group-hover:bg-blue-900">
                        <i class="fa-solid fa-location-crosshairs text-blue-900 text-lg transition duration-300 group-hover:text-white"></i>
                    </div>

                    <div>

                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-blue-900 mb-2">
                            Seguimiento
                        </p>

                        <h3 class="text-lg font-bold text-blue-950">
                            Rastrea tu envío
                        </h3>

                        <p class="text-sm text-gray-500 mt-2 leading-6 max-w-sm">
                            Consulta el estado de tu paquete y accede a las herramientas de rastreo.
                        </p>

                    </div>

                    <span class="inline-flex items-center mt-auto pt-6 text-sm font-semibold text-blue-900 group-hover:text-red-600 transition">
                        Ir al rastreo
                        <i class="fa-solid fa-arrow-right ml-2 text-xs transition-transform group-hover:translate-x-1"></i>
                    </span>

                </a>

            </div>

        </div>

    </section>



    {{-- =========================================================
         CÓMO FUNCIONA
    ========================================================== --}}
    <section class="max-w-7xl mx-auto px-6 py-20">

        <div class="text-center mb-16">

            <h2 class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3">
                ¿Cómo funciona?

                <span class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"></span>
            </h2>

            <p class="mt-4 text-sm text-gray-500">
                Cuatro pasos para enviar tu paquete con Venexpress.
            </p>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 relative">

            <div class="hidden md:block absolute top-9 left-[12%] right-[12%] border-t-2 border-dashed border-gray-300 z-0"></div>


            <div class="relative z-10 text-center">

                <div class="relative w-20 h-20 mx-auto">

                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-calculator text-blue-950 text-2xl"></i>
                    </div>

                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">
                        1
                    </span>

                </div>

                <h3 class="font-semibold text-blue-950 mt-4">
                    Cotiza
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Consulta el precio de tu envío.
                </p>

            </div>


            <div class="relative z-10 text-center">

                <div class="relative w-20 h-20 mx-auto">

                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-list text-blue-950 text-2xl"></i>
                    </div>

                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">
                        2
                    </span>

                </div>

                <h3 class="font-semibold text-blue-950 mt-4">
                    Registra
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Completa los datos de tu envío.
                </p>

            </div>


            <div class="relative z-10 text-center">

                <div class="relative w-20 h-20 mx-auto">

                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-box text-blue-950 text-2xl"></i>
                    </div>

                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">
                        3
                    </span>

                </div>

                <h3 class="font-semibold text-blue-950 mt-4">
                    Entrega
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Lleva el paquete a una agencia.
                </p>

            </div>


            <div class="relative z-10 text-center">

                <div class="relative w-20 h-20 mx-auto">

                    <div class="w-20 h-20 rounded-full bg-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-location-dot text-blue-950 text-2xl"></i>
                    </div>

                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">
                        4
                    </span>

                </div>

                <h3 class="font-semibold text-blue-950 mt-4">
                    Rastrea
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Sigue el estado de tu paquete.
                </p>

            </div>

        </div>


        <div class="mt-10 text-center">

            <a
                href="{{ route('public.calculator') }}"
                class="bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition inline-flex items-center justify-center"
            >
                Comenzar ahora
                <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>

        </div>

    </section>



    {{-- =========================================================
         POR QUÉ ELEGIR VENEXPRESS
    ========================================================== --}}
    <section id="aliados" class="bg-gray-50">

        <div class="max-w-7xl mx-auto px-6 py-20">

            <div class="text-center mb-14">

                <h2 class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3">
                    ¿Por qué elegir
                    <span class="text-red-600">Venexpress</span>?

                    <span class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"></span>
                </h2>

            </div>


            <div class="grid md:grid-cols-2 gap-10 items-center">


                {{-- BENEFICIOS --}}
                <div class="grid sm:grid-cols-2 gap-5">

                    <div class="bg-white rounded-xl border border-gray-100 p-5">

                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-earth-americas text-blue-900"></i>
                        </div>

                        <h3 class="font-semibold text-blue-950 text-sm">
                            Cobertura nacional
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Llegamos a las principales ciudades del país.
                        </p>

                    </div>


                    <div class="bg-white rounded-xl border border-gray-100 p-5">

                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-box text-amber-500"></i>
                        </div>

                        <h3 class="font-semibold text-blue-950 text-sm">
                            Envíos seguros
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Tus paquetes están protegidos en cada etapa del envío.
                        </p>

                    </div>


                    <div class="bg-white rounded-xl border border-gray-100 p-5">

                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-people-group text-blue-900"></i>
                        </div>

                        <h3 class="font-semibold text-blue-950 text-sm">
                            Alianzas confiables
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Trabajamos con los mejores aliados del sector.
                        </p>

                    </div>


                    <div class="bg-white rounded-xl border border-gray-100 p-5">

                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-regular fa-clock text-blue-900"></i>
                        </div>

                        <h3 class="font-semibold text-blue-950 text-sm">
                            Entrega puntual
                        </h3>

                        <p class="text-xs text-gray-500 mt-1">
                            Comprometidos con la puntualidad de tu envío.
                        </p>

                    </div>

                </div>



                {{-- MAPA --}}
                <div class="flex flex-col items-center">

                    <img
                        src="{{ asset('images/venezuela-map.png') }}"
                        alt="Cobertura Venexpress en Venezuela"
                        class="coverage-map w-full"
                    >

                    <a
                        href="{{ route('public.offices') }}"
                        class="mt-5 bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold px-5 py-3 rounded-lg transition inline-flex items-center justify-center shadow-sm"
                    >

                        <i class="fa-solid fa-location-dot mr-2 text-amber-400"></i>

                        Descubre agencias cercanas

                        <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>

                    </a>

                </div>

            </div>

        </div>

    </section>



    {{-- =========================================================
         FOOTER
    ========================================================== --}}
    <footer id="ayuda" class="bg-blue-950">

        <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-5 gap-10">


            <div>

                <img
                    src="{{ asset('images/venexpress-logo-white.png') }}"
                    alt="Venexpress"
                    class="h-8 mb-4"
                >

                <p class="text-sm text-blue-200">
                    Conectamos a Venezuela con soluciones de envío rápidas, seguras y confiables.
                </p>


                <div class="flex items-center gap-3 mt-5">

                    <a
                        href="#"
                        class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition"
                    >
                        <i class="fa-brands fa-facebook-f text-white text-sm"></i>
                    </a>

                    <a
                        href="#"
                        class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition"
                    >
                        <i class="fa-brands fa-instagram text-white text-sm"></i>
                    </a>

                    <a
                        href="#"
                        class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition"
                    >
                        <i class="fa-brands fa-x-twitter text-white text-sm"></i>
                    </a>

                    <a
                        href="#"
                        class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition"
                    >
                        <i class="fa-brands fa-whatsapp text-white text-sm"></i>
                    </a>

                </div>

            </div>


            <div>

                <h4 class="text-white font-semibold text-sm mb-4">
                    Enlaces rápidos
                </h4>

                <ul class="space-y-2 text-sm text-blue-200">

                    <li>
                        <a href="{{ route('home') }}" class="hover:text-white transition">
                            Inicio
                        </a>
                    </li>

                    <li>
                        <a href="#servicios" class="hover:text-white transition">
                            Servicios
                        </a>
                    </li>

                    <li>
                        <a href="#aliados" class="hover:text-white transition">
                            Aliados
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('tracking.index') }}" class="hover:text-white transition">
                            Rastreo
                        </a>
                    </li>

                    <li>
                        <a href="#ayuda" class="hover:text-white transition">
                            Ayuda
                        </a>
                    </li>

                </ul>

            </div>


            <div>

                <h4 class="text-white font-semibold text-sm mb-4">
                    Servicios
                </h4>

                <ul class="space-y-2 text-sm text-blue-200">

                    <li>
                        <a href="{{ route('public.calculator') }}" class="hover:text-white transition">
                            Envíos Nacionales
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('public.calculator') }}" class="hover:text-white transition">
                            Envíos Express
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('login') }}" class="hover:text-white transition">
                            Carga Empresarial
                        </a>
                    </li>

                </ul>

            </div>


            <div>

                <h4 class="text-white font-semibold text-sm mb-4">
                    Ayuda
                </h4>

                <ul class="space-y-2 text-sm text-blue-200">

                    <li>
                        <a href="#ayuda" class="hover:text-white transition">
                            Preguntas frecuentes
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:text-white transition">
                            Políticas
                        </a>
                    </li>

                    <li>
                        <a href="#" class="hover:text-white transition">
                            Términos y condiciones
                        </a>
                    </li>

                    <li>
                        <a href="mailto:info@venexpress.com" class="hover:text-white transition">
                            Contáctanos
                        </a>
                    </li>

                </ul>

            </div>


            <div>

                <h4 class="text-white font-semibold text-sm mb-4">
                    Contáctanos
                </h4>

                <ul class="space-y-3 text-sm text-blue-200">

                    <li class="flex items-start gap-2">

                        <i class="fa-solid fa-phone mt-0.5 text-white"></i>

                        <span>
                            0800-VENEXPRESS<br>
                            0800-83639773
                        </span>

                    </li>


                    <li class="flex items-center gap-2">

                        <i class="fa-solid fa-envelope text-white"></i>

                        <span>
                            info@venexpress.com
                        </span>

                    </li>


                    <li class="flex items-center gap-2">

                        <i class="fa-solid fa-location-dot text-white"></i>

                        <span>
                            Caracas, Venezuela
                        </span>

                    </li>

                </ul>

            </div>

        </div>


        <div class="border-t border-white/10">

            <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-blue-300">
                &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.
            </div>

        </div>

    </footer>



    {{-- =========================================================
         JAVASCRIPT
    ========================================================== --}}
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

                    const open = !menu.classList.contains('hidden');

                    if (open) {

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