<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Tienda Venexpress' }}</title>

    <link rel="icon" href="{{ asset('images/venexpress-logo-solo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    @livewireStyles

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

{{--
    Layout independiente del navbar público general (layouts.public +
    x-public-navbar): solo se usa para invitados en /tienda (ver
    ResolvesLayoutForViewer::resolveLayoutForViewer — un usuario
    logueado sigue viendo /tienda dentro de SU panel de rol, sin
    pasar por aquí). El header/nav/mega-menú del marketplace vive
    dentro del componente Livewire (marketplace.blade.php) porque
    necesita datos en vivo (carrito, búsqueda) que un layout estático
    no puede tener.
--}}
<body class="antialiased bg-white text-[#111111]">

    {{ $slot }}

    <footer class="border-t border-gray-100 bg-white">
        <div class="max-w-[1800px] mx-auto px-4 sm:px-6 lg:px-10 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-400">

            <a href="{{ route('home') }}" class="hover:text-gray-700 transition">
                ← Volver a Venexpress
            </a>

            <span>
                &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.
            </span>

        </div>
    </footer>

    <x-image-lightbox />

    @livewireScripts

</body>
</html>
