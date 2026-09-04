<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Venexpress' }}</title>
    <link rel="icon" href="{{ asset('images/venexpress-logo-solo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
    @livewireStyles
</head>
<body class="antialiased bg-white">

    {{-- NAVBAR --}}
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/venexpress-logo.png') }}" alt="Venexpress" class="h-9">
            </a>
            <div class="hidden md:flex items-center gap-9 text-sm font-medium text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-blue-950 transition">Inicio</a>
                <a href="{{ route('tracking.index') }}" class="hover:text-blue-950 transition {{ request()->routeIs('tracking.*') ? 'text-blue-950 font-semibold' : '' }}">Rastreo</a>
                <a href="{{ route('public.calculator') }}" class="hover:text-blue-950 transition {{ request()->routeIs('public.calculator') ? 'text-blue-950 font-semibold' : '' }}">Calcular precio</a>
                <a href="{{ route('public.offices') }}" class="hover:text-blue-950 transition {{ request()->routeIs('public.offices') ? 'text-blue-950 font-semibold' : '' }}">Agencias aliadas</a>
            </div>
            <a href="{{ route('login') }}"
               class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-6 py-2.5 rounded-lg transition">
                Iniciar sesión
            </a>
        </div>
    </nav>

    {{ $slot }}

    {{-- FOOTER --}}
    <footer class="bg-blue-950 text-white/70 pt-14 pb-8">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-10">
                <div>
                    <img src="{{ asset('images/venexpress-logo-white.png') }}" alt="Venexpress" class="h-8 mb-4">
                    <p class="text-sm text-white/50">Servicio nacional de encomiendas a través de agencias aliadas en toda Venezuela.</p>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">Navegación</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Inicio</a></li>
                        <li><a href="{{ route('tracking.index') }}" class="hover:text-white transition">Rastreo</a></li>
                        <li><a href="{{ route('public.calculator') }}" class="hover:text-white transition">Calcular precio</a></li>
                        <li><a href="{{ route('public.offices') }}" class="hover:text-white transition">Agencias aliadas</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">Servicios</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#servicios" class="hover:text-white transition">Envíos nacionales</a></li>
                        <li><a href="{{ route('home') }}#servicios" class="hover:text-white transition">Sobres</a></li>
                        <li><a href="{{ route('home') }}#servicios" class="hover:text-white transition">Entrega a domicilio</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold text-sm mb-3">Ayuda</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('home') }}#ayuda" class="hover:text-white transition">Preguntas frecuentes</a></li>
                        <li><a href="{{ route('home') }}#ayuda" class="hover:text-white transition">Contáctanos</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/10 mt-10 pt-6 text-xs text-white/40 text-center">
                &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
