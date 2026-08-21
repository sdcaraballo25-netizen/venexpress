<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venexpress - Rastrea tu envío</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>
<body class="antialiased bg-[#F5F7FA]">

    {{-- NAVBAR --}}
    <nav class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/venexpress-logo.svg') }}" alt="Venexpress" class="h-8">
            </div>
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-700">
                <a href="/" class="text-blue-900 font-semibold">Inicio</a>
                <a href="/servicios" class="hover:text-blue-900">Servicios</a>
                <a href="/aliados" class="hover:text-blue-900">Aliados</a>
                <a href="{{ route('tracking.index') }}" class="hover:text-blue-900">Rastreo</a>
                <a href="/ayuda" class="hover:text-blue-900">Ayuda</a>
            </div>
            <a href="{{ route('login') }}" class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-5 py-2 rounded-lg transition">
                Iniciar sesión
            </a>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-white via-white to-blue-50">
        <div class="absolute inset-0 pointer-events-none">
            <img src="{{ asset('images/city-skyline.svg') }}" class="absolute right-0 bottom-0 w-2/3 opacity-20" alt="">
            <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-b from-blue-800 via-amber-400 to-red-600
                        [clip-path:polygon(60%_0,100%_0,100%_100%,20%_100%)] opacity-90"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 pt-16 pb-10 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl font-extrabold text-blue-950 leading-tight">
                    Rastrea tu <span class="text-red-600">envío</span>
                </h1>
                <p class="mt-4 text-gray-600 max-w-md">
                    Ingresa tu número de guía para conocer el estado de tu paquete en tiempo real.
                </p>

                <form action="{{ route('tracking.show') }}" method="GET" class="mt-6 flex gap-3 max-w-md">
                    <input
                        type="text"
                        name="guia"
                        placeholder="Ej. VE-2026-0001258"
                        class="flex-1 rounded-lg border-gray-300 focus:ring-blue-600 focus:border-blue-600"
                    >
                    <button type="submit"
                        class="bg-blue-900 hover:bg-blue-800 text-white font-semibold px-6 rounded-lg transition">
                        Rastrear
                    </button>
                </form>
            </div>

            <div class="hidden md:block relative">
                <img src="{{ asset('images/van-hero.png') }}" alt="Furgoneta Venexpress" class="w-full max-w-lg mx-auto">
            </div>
        </div>
    </section>

    {{-- FRANJA DE BENEFICIOS --}}
    <section class="bg-blue-950">
        <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-white text-sm">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-shield-halved text-amber-400 text-xl"></i>
                Envíos seguros a nivel nacional
            </div>
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-location-dot text-amber-400 text-xl"></i>
                Cobertura en las principales ciudades
            </div>
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-handshake text-amber-400 text-xl"></i>
                Aliados comerciales de confianza
            </div>
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-headset text-amber-400 text-xl"></i>
                Atención al cliente 24/7
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-white border-t">
        <div class="max-w-7xl mx-auto px-6 py-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.
        </div>
    </footer>

</body>
</html>
