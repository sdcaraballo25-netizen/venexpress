<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'VenExpress') }} — Envíos y paquetería en toda Venezuela</title>
    <meta name="description" content="Rastrea tu envío en tiempo real, encuentra tu agencia aliada más cercana y gestiona tus guías con VenExpress.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-navy-900 antialiased bg-white">

    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-lg focus:bg-navy-900 focus:px-4 focus:py-2 focus:text-white">Saltar al contenido</a>

    {{-- ============ NAV ============ --}}
    <header class="sticky top-0 z-40 border-b border-navy-100/80 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ url('/') }}" wire:navigate>
                <x-venexpress-logo size="md" />
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-navy-700 md:flex">
                <a href="{{ url('/') }}" class="hover:text-navy-900">Inicio</a>
                <a href="#servicios" class="hover:text-navy-900">Servicios</a>
                <a href="#aliados" class="hover:text-navy-900">Aliados</a>
                <a href="{{ route('tracking.index') }}" class="hover:text-navy-900">Rastreo</a>
                <a href="#ayuda" class="hover:text-navy-900">Ayuda</a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="inline-flex items-center rounded-full bg-gold-400 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm hover:bg-gold-300 transition">
                        Ir a mi panel
                    </a>
                @else
                    <a href="{{ route('register') }}" wire:navigate
                       class="hidden sm:inline-flex items-center text-sm font-semibold text-navy-700 hover:text-navy-900">
                        Regístrate
                    </a>
                    <a href="{{ route('login') }}" wire:navigate
                       class="inline-flex items-center rounded-full bg-gold-400 px-5 py-2 text-sm font-semibold text-navy-900 shadow-sm hover:bg-gold-300 transition">
                        Iniciar sesión
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main id="contenido">
        {{-- ============ HERO ============ --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(249,187,36,0.10),transparent_45%)]"></div>

            <div class="relative mx-auto grid max-w-7xl gap-14 px-6 pb-16 pt-14 lg:grid-cols-2 lg:items-center lg:pb-24 lg:pt-20">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-navy-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-navy-600">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
                        Paquetería nacional
                    </span>

                    <h1 class="mt-5 font-display text-4xl font-bold leading-[1.08] text-navy-900 sm:text-5xl">
                        Rastrea tu envío<br class="hidden sm:block"> <span class="text-gold-500">en segundos</span>
                    </h1>

                    <p class="mt-5 max-w-md text-base leading-relaxed text-slate-600">
                        Ingresa tu número de guía y conoce en tiempo real dónde está tu paquete, desde la agencia aliada hasta la entrega final.
                    </p>

                    <form action="{{ route('tracking.index') }}" method="GET" class="mt-8 flex max-w-lg flex-col gap-3 sm:flex-row">
                        <label for="tracking_number" class="sr-only">Número de guía</label>
                        <input id="tracking_number" name="guia" type="text" placeholder="Ej. VE-2026-0001258"
                               class="flex-1 rounded-lg border border-navy-100 px-4 py-3 text-sm text-navy-900 placeholder:text-slate-400 shadow-sm focus:border-navy-400 focus:outline-none focus:ring-2 focus:ring-navy-400">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-gold-400 px-6 py-3 text-sm font-semibold text-navy-900 shadow-sm transition hover:bg-gold-300">
                            Rastrear
                            <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </form>

                    <p class="mt-5 text-xs text-slate-500">
                        ¿Tienes un negocio?
                        <a href="{{ route('register') }}" wire:navigate class="font-semibold text-navy-700 underline decoration-gold-400 decoration-2 underline-offset-4 hover:text-navy-900">
                            Conviértete en Aliado VenExpress →
                        </a>
                    </p>
                </div>

                {{-- Ilustración de firma: franja tricolor + furgón + skyline --}}
                <div class="relative mx-auto w-full max-w-md lg:max-w-none">
                    <svg viewBox="0 0 560 460" class="w-full drop-shadow-brand" role="img" aria-label="Furgón de reparto VenExpress en tránsito">
                        <rect x="0" y="0" width="560" height="460" rx="28" fill="#EEF2FA"/>

                        <g opacity="0.9">
                            <rect x="-40" y="330" width="700" height="26" transform="rotate(-7 280 343)" fill="#0B1830"/>
                            <rect x="-40" y="362" width="700" height="26" transform="rotate(-7 280 375)" fill="#F9BB24"/>
                            <rect x="-40" y="394" width="700" height="26" transform="rotate(-7 280 407)" fill="#CE1126" opacity="0.85"/>
                        </g>

                        <g fill="#D6DFF0">
                            <rect x="30" y="250" width="34" height="90" rx="3"/>
                            <rect x="74" y="220" width="34" height="120" rx="3"/>
                            <rect x="118" y="260" width="34" height="80" rx="3"/>
                            <rect x="420" y="240" width="34" height="100" rx="3"/>
                            <rect x="464" y="210" width="34" height="130" rx="3"/>
                            <rect x="508" y="255" width="34" height="85" rx="3"/>
                        </g>

                        <g transform="translate(0,-6)">
                            <rect x="130" y="185" width="300" height="105" rx="14" fill="#0B1830"/>
                            <rect x="150" y="205" width="90" height="55" rx="6" fill="#EEF2FA"/>
                            <rect x="150" y="205" width="90" height="55" rx="6" fill="none" stroke="#F9BB24" stroke-width="3"/>
                            <text x="195" y="238" text-anchor="middle" font-family="Space Grotesk, sans-serif" font-weight="700" font-size="15" fill="#0B1830">VE</text>
                            <rect x="255" y="205" width="150" height="14" rx="7" fill="#1E3259"/>
                            <rect x="255" y="227" width="110" height="10" rx="5" fill="#2C4677"/>

                            <rect x="340" y="150" width="90" height="70" rx="12" fill="#0B1830"/>
                            <path d="M340 195 L365 155 L420 155 L430 195 Z" fill="#EEF2FA" opacity="0.9"/>
                            <circle cx="196" cy="300" r="26" fill="#0B1830"/>
                            <circle cx="196" cy="300" r="10" fill="#D6DFF0"/>
                            <circle cx="366" cy="300" r="26" fill="#0B1830"/>
                            <circle cx="366" cy="300" r="10" fill="#D6DFF0"/>
                        </g>

                        <g transform="translate(70,60)">
                            <rect x="0" y="0" width="56" height="46" rx="6" fill="#F9BB24"/>
                            <path d="M0 12 L28 24 L56 12 M28 24 V46" stroke="#0B1830" stroke-width="2.4" fill="none" stroke-linejoin="round"/>
                            <path d="M60 40 C 110 20, 150 70, 190 130" stroke="#7690C4" stroke-width="2" stroke-dasharray="4 6" fill="none"/>
                        </g>

                        <g transform="translate(300,60)">
                            <rect x="0" y="0" width="176" height="40" rx="20" fill="white" stroke="#D6DFF0" stroke-width="1.5"/>
                            <circle cx="22" cy="20" r="6" fill="#22C55E"/>
                            <text x="40" y="25" font-family="Inter, sans-serif" font-weight="600" font-size="13" fill="#16244A">VE-2026-0001258 · En tránsito</text>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- Franja de confianza --}}
            <div class="bg-navy-950">
                <div class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-6 py-7 sm:grid-cols-4">
                    @foreach ([
                        ['icon' => 'shield', 'label' => 'Envíos seguros a nivel nacional'],
                        ['icon' => 'pin', 'label' => 'Cobertura en las principales ciudades'],
                        ['icon' => 'handshake', 'label' => 'Aliados comerciales de confianza'],
                        ['icon' => 'headset', 'label' => 'Atención al cliente 24/7'],
                    ] as $item)
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-gold-400">
                                @switch($item['icon'])
                                    @case('shield')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('pin')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="9.5" r="2.4" stroke="currentColor" stroke-width="1.6"/></svg>
                                        @break
                                    @case('handshake')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M3 11l4-4 4 3 3-3 4 4M3 11v4l4 3M21 11v4l-4 3M7 10l3 3 3-3 3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        @break
                                    @case('headset')
                                        <svg viewBox="0 0 24 24" fill="none" class="h-[18px] w-[18px]"><path d="M4 13a8 8 0 1 1 16 0v4a2 2 0 0 1-2 2h-1v-6h3M4 17v-4h3v6H5a1 1 0 0 1-1-1Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        @break
                                @endswitch
                            </span>
                            <span class="text-xs font-medium leading-snug text-navy-100 sm:text-sm">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ EJEMPLO DE RASTREO ============ --}}
        <section class="mx-auto max-w-5xl px-6 py-20">
            <div class="text-center">
                <span class="text-xs font-semibold uppercase tracking-wider text-gold-600">Así funciona el rastreo</span>
                <h2 class="mt-3 font-display text-3xl font-bold text-navy-900 sm:text-4xl">De la agencia aliada a la puerta de tu casa</h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-600">Cada guía pasa por 6 etapas verificadas. Este es un ejemplo de cómo se ve el estado de un envío real.</p>
            </div>

            @php
                $steps = [
                    ['label' => 'Recibido en Agencia Aliada', 'time' => '09/05/2026 · 08:14 a.m.', 'icon' => 'box'],
                    ['label' => 'Recolectado por VenExpress', 'time' => '09/05/2026 · 11:37 a.m.', 'icon' => 'truck'],
                    ['label' => 'En Hub de Clasificación', 'time' => '09/05/2026 · 02:52 p.m.', 'icon' => 'hub'],
                    ['label' => 'En Tránsito Nacional', 'time' => '09/05/2026 · 06:30 p.m.', 'icon' => 'route'],
                    ['label' => 'Listo para Retiro en Agencia Destino', 'time' => 'Pendiente', 'icon' => 'store'],
                    ['label' => 'Entregado al Cliente', 'time' => 'Pendiente', 'icon' => 'check'],
                ];
                $activeIndex = 3;
            @endphp

            <div class="mt-12 overflow-x-auto rounded-2xl border border-navy-100 bg-white p-6 shadow-brand sm:p-8">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-2 border-b border-navy-50 pb-5">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Guía</p>
                        <p class="font-display text-lg font-bold text-navy-900">VE-2026-0001258</p>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-gold-50 px-3 py-1 text-xs font-semibold text-gold-700">En tránsito</span>
                    <p class="text-sm text-slate-500">Caracas <span class="text-navy-300">→</span> Barquisimeto</p>
                </div>

                <div class="grid min-w-[720px] grid-cols-6 gap-2">
                    @foreach ($steps as $i => $step)
                        <div class="flex flex-col items-center text-center">
                            <div class="flex w-full items-center">
                                <div class="h-0.5 flex-1 {{ $i === 0 ? 'bg-transparent' : ($i <= $activeIndex ? 'bg-gold-400' : 'bg-navy-100') }}"></div>
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border-2
                                    {{ $i < $activeIndex ? 'border-gold-400 bg-gold-400 text-navy-900' : ($i === $activeIndex ? 'border-gold-400 bg-white text-gold-600 ring-4 ring-gold-100' : 'border-navy-100 bg-navy-50 text-navy-300') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                        @switch($step['icon'])
                                            @case('box') <path d="M4 8l8-4 8 4-8 4-8-4Zm0 0v8l8 4m0-12v12m0-12l8 4v8l-8 4" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/> @break
                                            @case('truck') <path d="M3 7h11v9H3V7Zm11 3h4l3 3v3h-7v-6ZM6.5 19a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm11 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/> @break
                                            @case('hub') <path d="M12 3v18M3 12h18M5.5 5.5l13 13m0-13-13 13" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/> @break
                                            @case('route') <circle cx="6" cy="6" r="2.2" stroke="currentColor" stroke-width="1.6"/><circle cx="18" cy="18" r="2.2" stroke="currentColor" stroke-width="1.6"/><path d="M7.8 7.5c2 1.5 6.4 4 8.4 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/> @break
                                            @case('store') <path d="M4 9l1-5h14l1 5M4 9h16M4 9v10h16V9M9 19v-6h6v6" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/> @break
                                            @case('check') <path d="M5 13l4 4 10-10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> @break
                                        @endswitch
                                    </svg>
                                </div>
                                <div class="h-0.5 flex-1 {{ $i < $activeIndex ? 'bg-gold-400' : 'bg-navy-100' }}"></div>
                            </div>
                            <p class="mt-3 text-xs font-semibold leading-snug {{ $i <= $activeIndex ? 'text-navy-900' : 'text-navy-300' }}">{{ $step['label'] }}</p>
                            <p class="mt-1 text-[11px] text-slate-400">{{ $step['time'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ CÓMO FUNCIONA ============ --}}
        <section id="servicios" class="bg-navy-50/60 py-20">
            <div class="mx-auto max-w-6xl px-6">
                <div class="text-center">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gold-600">Cómo funciona</span>
                    <h2 class="mt-3 font-display text-3xl font-bold text-navy-900 sm:text-4xl">Enviar con VenExpress toma tres pasos</h2>
                </div>

                <div class="mt-12 grid gap-8 md:grid-cols-3">
                    @foreach ([
                        ['n' => '01', 'title' => 'Llévalo a un Aliado', 'desc' => 'Entrega tu paquete o sobre en cualquiera de nuestras taquillas aliadas y recibe tu número de guía al instante.'],
                        ['n' => '02', 'title' => 'Lo transportamos', 'desc' => 'Tu envío pasa por nuestro hub de clasificación y viaja en tránsito nacional hasta la ciudad de destino.'],
                        ['n' => '03', 'title' => 'Tu destinatario lo recibe', 'desc' => 'Notificamos cuando el paquete está listo para retiro o lo entregamos directamente en la puerta.'],
                    ] as $step)
                        <div class="rounded-2xl bg-white p-7 shadow-sm ring-1 ring-navy-100/70">
                            <span class="font-display text-3xl font-bold text-navy-100">{{ $step['n'] }}</span>
                            <h3 class="mt-3 font-display text-lg font-bold text-navy-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ ALIADOS CTA ============ --}}
        <section id="aliados" class="mx-auto max-w-7xl px-6 py-20">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-gold-600">Para negocios</span>
                    <h2 class="mt-3 font-display text-3xl font-bold text-navy-900 sm:text-4xl">Convierte tu local en una taquilla aliada VenExpress</h2>
                    <p class="mt-4 max-w-lg text-slate-600">
                        Registra guías desde tu propio punto de venta, cobra en dólares o bolívares a la tasa BCV vigente y gana comisión por cada envío que gestiones.
                    </p>
                    <a href="{{ route('register') }}" wire:navigate
                       class="mt-7 inline-flex items-center gap-2 rounded-lg bg-navy-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-navy-700">
                        Quiero ser Aliado
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4"><path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>

                <div class="rounded-2xl bg-navy-950 p-7 text-white shadow-brand sm:p-8">
                    <p class="text-sm text-navy-200">Resumen de hoy · Librería El Estudiante</p>
                    <div class="mt-5 grid grid-cols-2 gap-5">
                        @foreach ([
                            ['v' => '24', 'l' => 'Guías creadas hoy'],
                            ['v' => '18', 'l' => 'Envíos en tránsito'],
                            ['v' => '$320.50', 'l' => 'Comisión acumulada'],
                            ['v' => '$3,205.80', 'l' => 'Ventas del mes'],
                        ] as $stat)
                            <div>
                                <p class="font-display text-2xl font-bold text-gold-400">{{ $stat['v'] }}</p>
                                <p class="mt-1 text-xs text-navy-300">{{ $stat['l'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ AYUDA ============ --}}
        <section id="ayuda" class="bg-navy-950 py-16">
            <div class="mx-auto flex max-w-7xl flex-col items-center gap-5 px-6 text-center">
                <h2 class="font-display text-2xl font-bold text-white sm:text-3xl">¿Tienes dudas sobre tu envío?</h2>
                <p class="max-w-md text-sm text-navy-200">Nuestro equipo de atención al cliente está disponible 24/7 para ayudarte a resolver cualquier inconveniente.</p>
                <a href="{{ route('tracking.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-gold-400 px-6 py-3 text-sm font-semibold text-navy-900 transition hover:bg-gold-300">
                    Rastrear un paquete
                </a>
            </div>
        </section>
    </main>

    {{-- ============ FOOTER ============ --}}
    <footer class="border-t border-navy-800 bg-navy-950">
        <div class="mx-auto max-w-7xl px-6 py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <x-venexpress-logo variant="light" size="sm" />
                    <p class="mt-4 max-w-xs text-sm text-navy-300">Envíos rápidos y seguros por toda Venezuela, de agencia a agencia o hasta la puerta de tu casa.</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy-400">Servicios</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-navy-200">
                        <li><a href="{{ route('tracking.index') }}" class="hover:text-white">Rastreo de guías</a></li>
                        <li><a href="#servicios" class="hover:text-white">Envíos nacionales</a></li>
                        <li><a href="#aliados" class="hover:text-white">Taquillas aliadas</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy-400">Empresa</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-navy-200">
                        <li><a href="#aliados" class="hover:text-white">Conviértete en Aliado</a></li>
                        <li><a href="{{ route('login') }}" wire:navigate class="hover:text-white">Iniciar sesión</a></li>
                        <li><a href="{{ route('register') }}" wire:navigate class="hover:text-white">Crear cuenta</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-navy-400">Ayuda</p>
                    <ul class="mt-4 space-y-2.5 text-sm text-navy-200">
                        <li><a href="#ayuda" class="hover:text-white">Centro de ayuda</a></li>
                        <li><a href="#ayuda" class="hover:text-white">Contacto</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-navy-800 pt-6 text-xs text-navy-400 sm:flex-row">
                <p>&copy; {{ date('Y') }} VenExpress. Todos los derechos reservados.</p>
                <p>Hecho en Venezuela 🇻🇪</p>
            </div>
        </div>
    </footer>

</body>
</html>
