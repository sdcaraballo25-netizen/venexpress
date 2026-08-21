<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venexpress - Rastrea tu envío</title>
    <link rel="icon" href="{{ asset('images/venexpress-logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>
<body class="antialiased bg-white">

    {{-- NAVBAR --}}
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/venexpress-logo.svg') }}" alt="Venexpress" class="h-9">
            </a>
            <div class="hidden md:flex items-center gap-9 text-sm font-medium text-gray-500">
                <a href="{{ route('home') }}" class="text-blue-950 font-semibold">Inicio</a>
                <a href="#servicios" class="hover:text-blue-950 transition">Servicios</a>
                <a href="#aliados" class="hover:text-blue-950 transition">Aliados</a>
                <a href="{{ route('tracking.index') }}" class="hover:text-blue-950 transition">Rastreo</a>
                <a href="#ayuda" class="hover:text-blue-950 transition">Ayuda</a>
            </div>
            <a href="{{ route('login') }}"
               class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-6 py-2.5 rounded-lg transition">
                Iniciar sesión
            </a>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-white">

    <!-- Skyline -->
    <div class="absolute inset-0 z-0 pointer-events-none">
        <img
            src="{{ asset('images/skyline-hero.png') }}"
            alt=""
            class="absolute right-0 bottom-0 w-full h-full object-cover object-right opacity-80"
        >
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 pt-12 pb-16 grid md:grid-cols-2 gap-8 items-center">

        <!-- Texto -->
        <div>
            <h1 class="text-5xl font-extrabold text-blue-950 leading-tight">
                Rastrea tu <span class="text-red-600">envío</span>
            </h1>

            <p class="mt-4 text-gray-500 max-w-sm">
                Ingresa tu número de guía para conocer el estado de tu paquete en tiempo real.
            </p>

            <form action="{{ route('tracking.show') }}" method="GET"
                  class="mt-7 flex gap-3 max-w-md">
                <input
                    type="text"
                    name="guia"
                    placeholder="Ej. VE-2026-0001258"
                    class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-blue-600 focus:border-blue-600"
                >

                <button
                    type="submit"
                    class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold px-6 rounded-lg transition">
                    Rastrear
                </button>
            </form>
        </div>

        <!-- Van -->
        <div class="relative flex justify-center items-end pb-10">
            <img
                src="{{ asset('images/van-hero.png') }}"
                alt="Furgoneta Venexpress"
                class="w-full max-w-md relative z-10"
            >
        </div>

    </div>
</section>

    {{-- FRANJA DE BENEFICIOS --}}
    <section class="bg-blue-950">
        <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved text-amber-400"></i>
                </div>
                <span class="text-white text-sm">Envíos seguros a nivel nacional</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-location-dot text-amber-400"></i>
                </div>
                <span class="text-white text-sm">Cobertura en las principales ciudades</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-handshake text-amber-400"></i>
                </div>
                <span class="text-white text-sm">Aliados comerciales de confianza</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-headset text-amber-400"></i>
                </div>
                <span class="text-white text-sm">Atención al cliente 24/7</span>
            </div>
        </div>
    </section>

    {{-- COMO FUNCIONA --}}
    <section class="max-w-7xl mx-auto px-6 py-20">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3">
                ¿Cómo funciona?
                <span class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"></span>
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 relative">
            <div class="hidden md:block absolute top-9 left-[12%] right-[12%] border-t-2 border-dashed border-gray-300 z-0"></div>

            <div class="relative z-10 text-center">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-file-signature text-blue-950 text-2xl"></i>
                    </div>
                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">1</span>
                </div>
                <h3 class="font-semibold text-blue-950 mt-4">Ingresa tu guía</h3>
                <p class="text-sm text-gray-500 mt-1">Escribe el número de guía de tu paquete.</p>
            </div>

            <div class="relative z-10 text-center">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-magnifying-glass text-blue-950 text-2xl"></i>
                    </div>
                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">2</span>
                </div>
                <h3 class="font-semibold text-blue-950 mt-4">Rastrear</h3>
                <p class="text-sm text-gray-500 mt-1">Consulta el estado en tiempo real.</p>
            </div>

            <div class="relative z-10 text-center">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-box text-blue-950 text-2xl"></i>
                    </div>
                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">3</span>
                </div>
                <h3 class="font-semibold text-blue-950 mt-4">Sigue tu envío</h3>
                <p class="text-sm text-gray-500 mt-1">Conoce cada paso de tu paquete hasta su destino.</p>
            </div>

            <div class="relative z-10 text-center">
                <div class="relative w-20 h-20 mx-auto">
                    <div class="w-20 h-20 rounded-full bg-amber-400 flex items-center justify-center">
                        <i class="fa-solid fa-circle-check text-white text-2xl"></i>
                    </div>
                    <span class="absolute -top-2 -left-1 w-7 h-7 rounded-full bg-blue-950 text-white text-xs font-bold flex items-center justify-center">4</span>
                </div>
                <h3 class="font-semibold text-blue-950 mt-4">Recibe tu paquete</h3>
                <p class="text-sm text-gray-500 mt-1">Retira en agencia o recibe en la dirección indicada.</p>
            </div>
        </div>
    </section>

    {{-- POR QUE ELEGIR VENEXPRESS --}}
    <section id="aliados" class="bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 py-20">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3">
                    ¿Por qué elegir <span class="text-red-600">Venexpress</span>?
                    <span class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"></span>
                </h2>
            </div>

            <div class="grid md:grid-cols-2 gap-10 items-center">
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-earth-americas text-blue-900"></i>
                        </div>
                        <h3 class="font-semibold text-blue-950 text-sm">Cobertura nacional</h3>
                        <p class="text-xs text-gray-500 mt-1">Llegamos a las principales ciudades del país.</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-box text-amber-500"></i>
                        </div>
                        <h3 class="font-semibold text-blue-950 text-sm">Envíos seguros</h3>
                        <p class="text-xs text-gray-500 mt-1">Tus paquetes están protegidos en cada etapa del envío.</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-solid fa-people-group text-blue-900"></i>
                        </div>
                        <h3 class="font-semibold text-blue-950 text-sm">Alianzas confiables</h3>
                        <p class="text-xs text-gray-500 mt-1">Trabajamos con los mejores aliados del sector.</p>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center mb-3">
                            <i class="fa-regular fa-clock text-blue-900"></i>
                        </div>
                        <h3 class="font-semibold text-blue-950 text-sm">Entrega puntual</h3>
                        <p class="text-xs text-gray-500 mt-1">Comprometidos con la puntualidad de tu envío.</p>
                    </div>
                </div>

                <div class="flex justify-center">
                    <img src="{{ asset('images/venezuela-map.png') }}" alt="Cobertura Venexpress en Venezuela" class="max-w-md w-full">
                </div>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer id="ayuda" class="bg-blue-950">
        <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-5 gap-10">
            <div>
                <img src="{{ asset('images/venexpress-logo.svg') }}" alt="Venexpress" class="h-8 mb-4 brightness-0 invert">
                <p class="text-sm text-blue-200">Conectamos a Venezuela con soluciones de envío rápidas, seguras y confiables.</p>
                <div class="flex items-center gap-3 mt-5">
                    <a href="#" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <i class="fa-brands fa-facebook-f text-white text-sm"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <i class="fa-brands fa-instagram text-white text-sm"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <i class="fa-brands fa-x-twitter text-white text-sm"></i>
                    </a>
                    <a href="#" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                        <i class="fa-brands fa-whatsapp text-white text-sm"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Enlaces rápidos</h4>
                <ul class="space-y-2 text-sm text-blue-200">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Inicio</a></li>
                    <li><a href="#servicios" class="hover:text-white transition">Servicios</a></li>
                    <li><a href="#aliados" class="hover:text-white transition">Aliados</a></li>
                    <li><a href="{{ route('tracking.index') }}" class="hover:text-white transition">Rastreo</a></li>
                    <li><a href="#ayuda" class="hover:text-white transition">Ayuda</a></li>
                </ul>
            </div>

            <div id="servicios">
                <h4 class="text-white font-semibold text-sm mb-4">Servicios</h4>
                <ul class="space-y-2 text-sm text-blue-200">
                    <li><a href="#" class="hover:text-white transition">Envíos Nacionales</a></li>
                    <li><a href="#" class="hover:text-white transition">Envíos Express</a></li>
                    <li><a href="#" class="hover:text-white transition">Carga Empresarial</a></li>
                    <li><a href="#" class="hover:text-white transition">Casillero Internacional</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Ayuda</h4>
                <ul class="space-y-2 text-sm text-blue-200">
                    <li><a href="#" class="hover:text-white transition">Preguntas frecuentes</a></li>
                    <li><a href="#" class="hover:text-white transition">Políticas</a></li>
                    <li><a href="#" class="hover:text-white transition">Términos y condiciones</a></li>
                    <li><a href="#" class="hover:text-white transition">Contáctanos</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Contáctanos</h4>
                <ul class="space-y-3 text-sm text-blue-200">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-phone mt-0.5"></i>
                        <span>0800-VENEXPRESS<br>0800-83639773</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-envelope"></i>
                        <span>info@venexpress.com</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Caracas, Venezuela</span>
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

</body>
</html>
