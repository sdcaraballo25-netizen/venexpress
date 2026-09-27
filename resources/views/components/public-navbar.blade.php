{{--
    Navbar público compartido de las páginas internas (Calcular precio,
    Agencias, Rastreo, Resultado de rastreo, Ayuda...). Replica el diseño
    del navbar de la landing (welcome.blade.php): buscador de guía,
    "Regístrate" e "Iniciar sesión".

    Es autocontenido: CSS con prefijo .pnav- y JS propio, para no chocar
    con los estilos ni los scripts de la página que lo incluye. Requiere
    Font Awesome, que ya cargan todas esas páginas.
--}}

@once
    <style>
        .pnav {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid #f3f4f6;
            box-shadow: 0 1px 0 rgba(17,17,17,.06);
            font-family: 'Poppins', sans-serif;
            /* Como la landing: fondo blanco sólido siempre (también al
               reaparecer) y se desliza fuera de pantalla al bajar. */
            background: #ffffff !important;
            opacity: 1;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            transition: transform 0.28s ease;
            will-change: transform;
        }

        .pnav.nav-hidden {
            transform: translateY(-100%);
        }

        @media (prefers-reduced-motion: reduce) {
            .pnav {
                transition: none;
            }
        }

        .pnav-inner {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1rem;
        }

        .pnav-logo {
            flex: 0 0 auto;
        }

        .pnav-logo img {
            display: block;
            height: 2rem;
            width: auto;
        }

        .pnav-links {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            /* 1.1rem como la landing en pantallas anchas; se aprieta
               progresivamente en anchos medios para que quepan los 7 enlaces */
            gap: clamp(0.65rem, calc((100vw - 900px) / 20), 1.1rem);
        }

        .pnav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.3rem 0;
            color: #70706b;
            font-size: 0.78rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .pnav-link:hover,
        .pnav-link.is-active {
            color: #111111;
        }

        .pnav-link.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.25rem;
            height: 2px;
            border-radius: 999px;
            background: #F7D900;
        }

        .pnav-right {
            display: flex;
            flex: 0 1 auto;
            min-width: 0;
            align-items: center;
            gap: 0.5rem;
            margin-left: auto;
        }

        .pnav-search {
            display: flex;
            flex: 0 1 235px;
            min-width: 176px;
            align-items: center;
            height: 42px;
            margin: 0;
            background: #ffffff;
            border: 1px solid #d8d8d3;
            border-radius: 10px;
            overflow: hidden;
        }

        .pnav-search input {
            flex: 1;
            min-width: 0;
            height: 100%;
            padding: 0 13px;
            border: 0;
            outline: 0;
            box-shadow: none;
            background: transparent;
            color: #111111;
            font-family: inherit;
            font-size: 0.78rem;
        }

        .pnav-search input::placeholder {
            color: #9a9a95;
        }

        .pnav-search button {
            flex: 0 0 44px;
            width: 44px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: #111111;
            color: #ffffff;
            cursor: pointer;
        }

        .pnav-register,
        .pnav-login {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            padding: 0.625rem 1rem;
            border-radius: 0.5rem;
            color: #111111;
            font-size: 0.86rem;
            font-weight: 600;
            line-height: 1.25rem;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .pnav-register {
            border: 1px solid #111111;
        }

        .pnav-register:hover {
            background: #111111;
            color: #ffffff;
        }

        /* Mismo color que el botón de la landing: bg-amber-400 / hover:bg-amber-500,
           que tailwind.config.js redefine como #F7FF00 / #DEE600. */
        .pnav-login {
            background: #F7FF00;
        }

        .pnav-login:hover {
            background: #DEE600;
        }

        .pnav-burger {
            display: none;
            width: 2.5rem;
            height: 2.5rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            background: #ffffff;
            color: #111111;
            cursor: pointer;
        }

        .pnav-burger .pnav-icon-close,
        .pnav-burger.is-open .pnav-icon-open {
            display: none;
        }

        .pnav-burger.is-open .pnav-icon-close {
            display: inline-block;
        }

        .pnav-menu {
            display: none;
            border-top: 1px solid #f3f4f6;
            background: #ffffff;
            padding: 0.75rem 1.25rem 1rem;
        }

        .pnav-menu.is-open {
            display: block;
        }

        .pnav-menu-link {
            display: block;
            padding: 0.75rem 0;
            color: #4b5563;
            font-size: 0.875rem;
        }

        .pnav-menu-link.is-active {
            color: #111111;
            font-weight: 600;
        }

        .pnav-menu-form {
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f3f4f6;
        }

        .pnav-menu-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #111111;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pnav-menu-form div {
            display: flex;
            gap: 0.5rem;
        }

        .pnav-menu-form input {
            flex: 1;
            min-width: 0;
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-family: inherit;
            font-size: 0.875rem;
        }

        .pnav-menu-form button {
            padding: 0 1rem;
            border: 0;
            border-radius: 0.5rem;
            background: #111111;
            color: #ffffff;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
        }

        .pnav-menu-register {
            display: none;
            margin-top: 0.75rem;
            text-align: center;
        }

        @media (min-width: 640px) {
            .pnav-inner {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
                gap: 1.5rem;
            }

            .pnav-logo img {
                height: 2.25rem;
            }
        }

        @media (min-width: 1024px) {
            .pnav-inner {
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }

        /* Regla de acceso al buscador: nunca hay un ancho sin buscador visible
           ni ☰ para llegar a él.
             >=1240px      buscador en el navbar (cabe completo, >=176px)
             <1240px       el buscador pasa al menú ☰
             <=1069px      además, los 7 enlaces pasan al menú
           Umbrales medidos con el navbar real (con ~15-20px de margen). */
        @media (max-width: 1239px) {
            .pnav-search {
                display: none;
            }

            .pnav-burger {
                display: inline-flex;
            }
        }

        @media (max-width: 1069px) {
            .pnav-links {
                display: none;
            }
        }

        /* Mientras los enlaces siguen visibles en el navbar (1070-1239px), el
           menú ☰ solo trae el buscador. */
        @media (min-width: 1070px) {
            .pnav-menu-link {
                display: none;
            }

            .pnav-menu-form {
                margin-top: 0;
                padding-top: 0;
                border-top: 0;
            }
        }

        /* En móvil estrecho "Regístrate" pasa al menú desplegable. */
        @media (max-width: 639px) {
            .pnav-register {
                display: none;
            }

            .pnav-menu-register {
                display: block;
            }
        }

        @media (min-width: 1240px) {
            .pnav-menu,
            .pnav-menu.is-open {
                display: none;
            }
        }
    </style>
@endonce

<nav id="main-navbar" class="pnav" aria-label="Navegación principal">

    <div class="pnav-inner">

        <a href="{{ route('home') }}"
           class="pnav-logo"
           aria-label="Venexpress - Inicio">
            <img src="{{ asset('images/venexpress-logo.png') }}"
                 alt="Venexpress">
        </a>

        <div class="pnav-links">

            <a href="{{ route('home') }}"
               class="pnav-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
                Inicio
            </a>

            <a href="{{ route('home') }}#servicios"
               class="pnav-link">
                Servicios
            </a>

            <a href="{{ route('public.calculator') }}"
               class="pnav-link {{ request()->routeIs('public.calculator') ? 'is-active' : '' }}">
                Calcular precio
            </a>

            <a href="{{ route('public.offices') }}"
               class="pnav-link {{ request()->routeIs('public.offices') ? 'is-active' : '' }}">
                Agencias aliadas
            </a>

            <a href="{{ route('public.marketplace') }}"
               class="pnav-link {{ request()->routeIs('public.marketplace*') ? 'is-active' : '' }}">
                Tienda
            </a>

            <a href="{{ route('tracking.index') }}"
               class="pnav-link {{ request()->routeIs('tracking.*') ? 'is-active' : '' }}">
                Rastreo
            </a>

            <a href="{{ route('public.help') }}"
               class="pnav-link {{ request()->routeIs('public.help') ? 'is-active' : '' }}">
                Ayuda
            </a>

        </div>

        <div class="pnav-right">

            <form action="{{ route('tracking.show') }}"
                  method="GET"
                  class="pnav-search">
                <input type="text"
                       name="guia"
                       placeholder="Número de guía"
                       autocomplete="off"
                       spellcheck="false"
                       aria-label="Número de guía">
                <button type="submit" aria-label="Rastrear envío">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>

            <a href="{{ route('register') }}" class="pnav-register">
                Regístrate
            </a>

            <a href="{{ route('login') }}" class="pnav-login">
                Iniciar sesión
            </a>

            <button id="pnav-burger"
                    type="button"
                    class="pnav-burger"
                    aria-label="Abrir menú"
                    aria-expanded="false"
                    aria-controls="pnav-menu">
                <i class="fa-solid fa-bars pnav-icon-open"></i>
                <i class="fa-solid fa-xmark pnav-icon-close"></i>
            </button>

        </div>

    </div>

    <div id="pnav-menu" class="pnav-menu">

        <a href="{{ route('home') }}"
           class="pnav-menu-link {{ request()->routeIs('home') ? 'is-active' : '' }}">
            Inicio
        </a>

        <a href="{{ route('home') }}#servicios" class="pnav-menu-link">
            Servicios
        </a>

        <a href="{{ route('public.calculator') }}"
           class="pnav-menu-link {{ request()->routeIs('public.calculator') ? 'is-active' : '' }}">
            Calcular precio
        </a>

        <a href="{{ route('public.offices') }}"
           class="pnav-menu-link {{ request()->routeIs('public.offices') ? 'is-active' : '' }}">
            Agencias aliadas
        </a>

        <a href="{{ route('public.marketplace') }}"
           class="pnav-menu-link {{ request()->routeIs('public.marketplace*') ? 'is-active' : '' }}">
            Tienda
        </a>

        <a href="{{ route('tracking.index') }}"
           class="pnav-menu-link {{ request()->routeIs('tracking.*') ? 'is-active' : '' }}">
            Rastreo
        </a>

        <a href="{{ route('public.help') }}"
           class="pnav-menu-link {{ request()->routeIs('public.help') ? 'is-active' : '' }}">
            Ayuda
        </a>

        <form action="{{ route('tracking.show') }}"
              method="GET"
              class="pnav-menu-form">
            <label for="pnav-menu-guia">Rastrea tu envío</label>
            <div>
                <input id="pnav-menu-guia"
                       type="text"
                       name="guia"
                       placeholder="Número de guía"
                       autocomplete="off">
                <button type="submit">Rastrear</button>
            </div>
        </form>

        <a href="{{ route('register') }}"
           class="pnav-register pnav-menu-register">
            Regístrate
        </a>

    </div>

</nav>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const button = document.getElementById('pnav-burger');
            const menu = document.getElementById('pnav-menu');

            if (!button || !menu) return;

            const setOpen = open => {
                menu.classList.toggle('is-open', open);
                button.classList.toggle('is-open', open);
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            };

            button.addEventListener('click', () => {
                setOpen(!menu.classList.contains('is-open'));
            });

            menu.querySelectorAll('.pnav-menu-link').forEach(link => {
                link.addEventListener('click', () => setOpen(false));
            });

            /* Scroll-hide igual que la landing: al bajar >6px se oculta,
               al subir >6px reaparece y en el tope siempre se ve. */
            const navbar = document.getElementById('main-navbar');
            let lastScrollY = window.scrollY;
            let ticking = false;

            const updateOnScroll = () => {
                const currentScrollY = window.scrollY;
                const difference = currentScrollY - lastScrollY;

                if (currentScrollY <= 20) {
                    navbar?.classList.remove('nav-hidden');
                } else if (difference > 6) {
                    navbar?.classList.add('nav-hidden');
                    setOpen(false);
                } else if (difference < -6) {
                    navbar?.classList.remove('nav-hidden');
                }

                lastScrollY = currentScrollY;
                ticking = false;
            };

            window.addEventListener('scroll', () => {
                if (ticking) return;

                ticking = true;
                window.requestAnimationFrame(updateOnScroll);
            }, { passive: true });

        });
    </script>
@endonce
