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
        :root {
            --brand-yellow: #F7D900;
            --brand-yellow-strong: #FFD400;
            --ink: #111111;
            --muted: #656565;
            --line: #E6E6E2;
            --soft: #F7F7F4;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--ink);
            background: #fff;
        }

        #servicios,
        #rastreo,
        #cobertura,
        #aliados,
        #como-funciona,
        #empresas,
        #ayuda {
            scroll-margin-top: 92px;
        }

        .main-navbar {
            position: sticky;
            top: 0;
            background: #ffffff !important;
            opacity: 1;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            transition: transform 0.28s ease, box-shadow 0.28s ease;
            will-change: transform;
            box-shadow: 0 1px 0 rgba(17,17,17,.06);
        }

        .main-navbar.nav-hidden {
            transform: translateY(-100%);
        }

        .main-nav-links {
            display: flex;
            align-items: center;
            gap: 1.45rem;
        }

        .main-nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            padding: 0.4rem 0;
            color: #696965;
            font-size: 0.96rem;
            font-weight: 600;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .main-nav-link:hover,
        .main-nav-link.is-active {
            color: var(--ink);
        }

        .main-nav-link:hover {
            transform: translateY(-1px);
        }

        .main-nav-link.is-active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -0.25rem;
            height: 2px;
            border-radius: 999px;
            background: var(--brand-yellow);
        }

        /* El carrusel mantiene una altura fija para que ningún slide cambie el tamaño de la página. */
        .hero-shell {
            height: 510px;
            min-height: 510px;
            max-height: 510px;
        }

        .hero-slide {
            height: 510px;
            min-height: 510px;
            max-height: 510px;
            overflow: hidden;
        }

        .hero-image-panel {
            position: absolute;
            inset: 0 0 0 48%;
            overflow: hidden;
        }

        .hero-image-panel::before {
            content: '';
            position: absolute;
            width: 54%;
            height: 120%;
            top: -8%;
            left: 18%;
            background: var(--brand-yellow);
            transform: skewX(-17deg);
            transform-origin: center;
            opacity: 0.98;
            z-index: 0;
        }

        .hero-image-panel::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(255,255,255,1) 0%, rgba(255,255,255,.90) 7%, rgba(255,255,255,.08) 38%, rgba(255,255,255,0) 60%);
            z-index: 2;
            pointer-events: none;
        }

        .hero-image {
            position: absolute;
            z-index: 1;
            right: -4%;
            bottom: -10%;
            width: 80%;
            height: 115%;
            object-fit: cover;
            object-position: center;
            filter: saturate(0.96);
        }

        /* Slide 1: hero.png completa (sin recorte), a la derecha y centrada verticalmente. */
        .hero-image.hero-image-contain {
            top: 0;
            bottom: 0;
            right: 2%;
            left: auto;
            width: 90%;
            height: 100%;
            object-fit: contain;
            object-position: right center;
            filter: none;
        }

        .hero-content {
            position: relative;
            z-index: 5;
            height: 510px;
            min-height: 510px;
            max-height: 510px;
            display: flex;
            align-items: center;
        }

        .hero-copy {
            width: min(100%, 560px);
            padding: 4.2rem 0;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            background: var(--brand-yellow);
            color: var(--ink);
            border-radius: 999px;
            padding: 0.24rem 0.75rem;
            font-size: 0.67rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.01em;
        }

        .hero-title {
            margin-top: 1rem;
            font-size: clamp(2.8rem, 4.9vw, 5rem);
            line-height: 0.94;
            letter-spacing: -0.055em;
            font-weight: 800;
            max-width: 650px;
        }

        .hero-text {
            margin-top: 1.2rem;
            max-width: 530px;
            color: #575757;
            font-size: 0.98rem;
            line-height: 1.7;
        }

        .hero-actions {
            margin-top: 1.6rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .hero-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: var(--brand-yellow-strong);
            color: var(--ink);
            padding: 0.8rem 1.15rem;
            border-radius: 0.7rem;
            font-size: 0.78rem;
            font-weight: 800;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            background: #fff;
            color: var(--ink);
            border: 1px solid #1A1A1A;
            padding: 0.8rem 1.15rem;
            border-radius: 0.7rem;
            font-size: 0.78rem;
            font-weight: 700;
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .hero-primary:hover,
        .hero-secondary:hover {
            transform: translateY(-1px);
        }

        .hero-primary:hover {
            box-shadow: 0 10px 24px rgba(0,0,0,.10);
        }

        .hero-secondary:hover {
            background: #fafafa;
        }

        /* CTA de aliados y repartidores: un poco más grande para darles
           mayor presencia en sus slides sin agrandar el CTA principal
           del slide de cliente. */
        .hero-role-button {
            padding: 0.95rem 1.4rem;
            font-size: 0.86rem;
        }

        .hero-dots {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 0.35rem;
            padding: 0.4rem 0.55rem;
            border-radius: 999px;
            background: rgba(17,17,17,.84);
            backdrop-filter: blur(8px);
        }

        .hero-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: rgba(255,255,255,.38);
            transition: width 0.2s ease, background 0.2s ease;
        }

        .hero-dot.is-active {
            width: 1.45rem;
            background: #fff;
        }

        .utility-strip {
            background: var(--ink);
        }

        .utility-item {
            min-height: 86px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .utility-item + .utility-item {
            border-left: 1px solid rgba(255,255,255,.14);
        }

        .utility-content {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            width: min(100%, 285px);
        }

        .utility-icon {
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 999px;
            background: var(--brand-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--ink);
            flex: 0 0 auto;
            box-shadow: 0 0 0 5px rgba(247,217,0,.08);
        }

        .utility-title {
            color: #fff;
            font-size: 0.82rem;
            line-height: 1.25;
            font-weight: 700;
        }

        .utility-subtitle {
            color: rgba(255,255,255,.58);
            font-size: 0.67rem;
            line-height: 1.45;
            margin-top: 0.18rem;
        }

        .section-wrap {
            width: 100%;
            max-width: none;
            margin: 0 auto;
            padding-left: clamp(1rem, 4vw, 4.5rem);
            padding-right: clamp(1rem, 4vw, 4.5rem);
            box-sizing: border-box;
        }

        .section {
            padding: 4.8rem 0;
        }

        .section-soft {
            background: var(--soft);
        }

        .section-heading {
            max-width: 650px;
        }

        .section-title {
            margin-top: 0.65rem;
            font-size: clamp(1.9rem, 3.1vw, 2.55rem);
            line-height: 1.02;
            letter-spacing: -0.045em;
            font-weight: 800;
        }

        .section-subtitle {
            margin-top: 0.75rem;
            color: #6a6a67;
            font-size: 0.84rem;
            line-height: 1.7;
        }

        .quick-grid {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .quick-card {
            min-height: 190px;
            border: 1px solid var(--line);
            border-radius: 0.85rem;
            background: #fff;
            padding: 1.3rem;
            display: flex;
            flex-direction: column;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .quick-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 35px rgba(0,0,0,.07);
            border-color: #d7d7d2;
        }

        .icon-chip {
            width: 2.4rem;
            height: 2.4rem;
            border-radius: 999px;
            background: var(--brand-yellow);
            color: var(--ink);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.1rem;
        }

        .card-kicker {
            font-size: 0.64rem;
            line-height: 1;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7a7a74;
        }

        .card-title {
            margin-top: 0.45rem;
            font-size: 0.98rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .card-text {
            margin-top: 0.45rem;
            font-size: 0.75rem;
            line-height: 1.6;
            color: #70706b;
        }

        .card-link {
            margin-top: auto;
            padding-top: 1rem;
            font-size: 0.72rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .tracking-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
            gap: 2rem;
            align-items: center;
        }

        .tracking-box {
            margin-top: 1.5rem;
            display: flex;
            max-width: 620px;
            gap: 0.55rem;
        }

        .tracking-box input {
            width: 100%;
            border: 1px solid #ddddda;
            border-radius: 999px;
            background: #fff;
            padding: 0.92rem 1.15rem;
            outline: none;
            font-size: 0.8rem;
        }

        .tracking-box input:focus {
            border-color: #bdbdb8;
            box-shadow: 0 0 0 4px rgba(247,217,0,.18);
        }

        .tracking-box button {
            flex: 0 0 auto;
            border-radius: 999px;
            background: var(--brand-yellow-strong);
            padding: 0.92rem 1.3rem;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .tracking-note {
            margin-top: 0.65rem;
            font-size: 0.67rem;
            color: #777772;
        }

        .tracking-visual {
            position: relative;
            min-height: 280px;
            overflow: hidden;
            border-radius: 1rem;
            background: linear-gradient(135deg, #f0f0ed 0%, #fafaf7 50%, #eeeeea 100%);
        }

        .tracking-visual::before,
        .tracking-visual::after {
            content: '';
            position: absolute;
            background: var(--brand-yellow);
            z-index: 0;
        }

        .tracking-visual::before {
            width: 55%;
            height: 30%;
            left: -5%;
            bottom: 6%;
            transform: skewX(-28deg);
        }

        .tracking-visual::after {
            width: 30%;
            height: 16%;
            right: -3%;
            top: 6%;
            transform: skewX(-28deg);
        }

        .tracking-placeholder {
            position: absolute;
            left: 50%;
            top: 52%;
            transform: translate(-50%, -50%);
            z-index: 1;
            width: min(62%, 340px);
            aspect-ratio: 1.35 / 1;
            border-radius: 0.7rem;
            border: 1px dashed rgba(17,17,17,.26);
            background: rgba(255,255,255,.88);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 1rem;
        }

        .tracking-placeholder strong {
            font-size: 0.78rem;
        }

        .tracking-placeholder span {
            margin-top: 0.35rem;
            font-size: 0.64rem;
            color: #74746f;
        }

        .coverage-layout {
            display: grid;
            grid-template-columns: minmax(270px, 0.74fr) minmax(0, 1.26fr);
            gap: 2rem;
            align-items: center;
        }

        .outline-map {
            position: relative;
            min-height: 330px;
            border-radius: 1rem;
            background: linear-gradient(145deg, #fafaf8 0%, #f1f1ee 100%);
            overflow: hidden;
        }

        .outline-map::before {
            content: '';
            position: absolute;
            width: 68%;
            height: 76%;
            left: 15%;
            top: 13%;
            border-radius: 42% 48% 55% 36% / 50% 42% 60% 48%;
            background: #e1e1dc;
            transform: rotate(-9deg) skewX(-8deg);
            opacity: 0.88;
        }

        .route-line {
            position: absolute;
            height: 2px;
            background: var(--brand-yellow-strong);
            transform-origin: left center;
            z-index: 2;
            box-shadow: 0 0 0 1px rgba(255,255,255,.55);
        }

        .route-dot {
            position: absolute;
            width: 0.52rem;
            height: 0.52rem;
            border-radius: 999px;
            background: var(--ink);
            border: 2px solid #fff;
            z-index: 3;
            box-shadow: 0 0 0 2px rgba(17,17,17,.05);
        }

        .route-label {
            position: absolute;
            z-index: 4;
            font-size: 0.62rem;
            font-weight: 700;
            color: #222;
        }

        .agency-header {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
        }

        .agency-grid {
            margin-top: 1.4rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .agency-card {
            border: 1px solid var(--line);
            border-radius: 0.85rem;
            background: #fff;
            padding: 0.8rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .agency-thumb {
            width: 4.2rem;
            height: 4.2rem;
            border-radius: 0.6rem;
            background: linear-gradient(135deg, #dcdcd7, #f4f4f0);
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6f6f6a;
            font-size: 0.6rem;
            text-align: center;
            padding: 0.4rem;
        }

        .agency-name {
            font-size: 0.74rem;
            line-height: 1.25;
            font-weight: 800;
        }

        .agency-location,
        .agency-link {
            font-size: 0.61rem;
            color: #767670;
        }

        .agency-link {
            margin-top: 0.35rem;
            font-weight: 700;
            color: #222;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .steps {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 1.6rem;
            left: 11%;
            right: 11%;
            border-top: 1px dashed #ccccca;
        }

        .step {
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .step-number {
            width: 3rem;
            height: 3rem;
            margin: 0 auto;
            border-radius: 999px;
            background: #fff;
            border: 1px solid #dddcd7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            position: relative;
        }

        .step-number::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: inherit;
            background: var(--brand-yellow);
            z-index: -1;
        }

        .step-title {
            margin-top: 0.8rem;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .step-text {
            margin: 0.35rem auto 0;
            max-width: 180px;
            color: #70706b;
            font-size: 0.66rem;
            line-height: 1.55;
        }

        .partner-grid {
            display: grid;
            grid-template-columns: 1.35fr 0.65fr 0.65fr;
            gap: 0.9rem;
            margin-top: 1.75rem;
        }

        .partner-main {
            min-height: 280px;
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            background: #eee;
        }

        .partner-main::before {
            content: '';
            position: absolute;
            width: 55%;
            height: 130%;
            left: 35%;
            top: -15%;
            background: var(--brand-yellow);
            transform: skewX(-18deg);
            z-index: 0;
        }

        .partner-main-content {
            position: relative;
            z-index: 2;
            width: 55%;
            height: 100%;
            padding: 1.7rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .partner-main-image {
            position: absolute;
            width: 52%;
            height: 100%;
            right: 0;
            top: 0;
            object-fit: cover;
            object-position: center;
            z-index: 1;
            mix-blend-mode: normal;
        }

        .partner-small {
            min-height: 280px;
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: #fff;
            padding: 1.4rem;
            display: flex;
            flex-direction: column;
        }

        .partner-small .icon-chip {
            margin-bottom: auto;
        }

        .faq-layout {
            display: grid;
            grid-template-columns: 0.8fr 1.2fr;
            gap: 2.2rem;
            align-items: start;
        }

        .faq-list {
            border-top: 1px solid var(--line);
        }

        .faq-item {
            border-bottom: 1px solid var(--line);
        }

        .faq-question {
            width: 100%;
            padding: 0.9rem 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.72rem;
            font-weight: 600;
            text-align: left;
        }

        .faq-answer {
            display: none;
            padding: 0 0 1rem;
            color: #6c6c67;
            font-size: 0.66rem;
            line-height: 1.65;
            max-width: 750px;
        }

        .faq-item.is-open .faq-answer {
            display: block;
        }

        .faq-item.is-open .faq-icon {
            transform: rotate(45deg);
        }

        .faq-icon {
            transition: transform 0.2s ease;
        }

        .final-cta {
            background: var(--brand-yellow-strong);
        }

        .footer {
            background: var(--ink);
            color: #fff;
        }

        .footer-link {
            color: #a9a9a4;
            font-size: 0.7rem;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #fff;
        }

        /* Navbar: el rastreo de guía debe permanecer visible en escritorio. */
        .desktop-tracking-form {
            display: flex !important;
            align-items: center;
        }

        /* Servicios: acercamos la siguiente sección al bloque negro sin perder aire. */
        .services-section {
            padding-top: 3.7rem;
            padding-bottom: 4.2rem;
        }

        @media (max-width: 1100px) {
            .desktop-tracking-form {
                display: none !important;
            }
        }

        @media (min-width: 1280px) {
            .hero-copy {
                padding-left: 0.2rem;
            }
        }

        @media (max-width: 1100px) {
            .main-nav-links {
                gap: 1.2rem;
            }

            .hero-image-panel {
                left: 46%;
            }

            .quick-grid,
            .agency-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .partner-grid {
                grid-template-columns: 1fr 1fr;
            }

            .partner-main {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 900px) {
            .main-nav-links {
                display: none;
            }

            .hero-image-panel {
                inset: 0;
                opacity: 0.23;
            }

            .hero-image-panel::after {
                background: linear-gradient(90deg, rgba(255,255,255,1) 0%, rgba(255,255,255,.86) 58%, rgba(255,255,255,.25) 100%);
            }

            .hero-copy {
                width: 100%;
                max-width: 720px;
            }

            .tracking-layout,
            .coverage-layout,
            .faq-layout {
                grid-template-columns: 1fr;
            }

            .tracking-visual {
                min-height: 250px;
            }
        }

        @media (max-width: 767px) {
            .utility-item {
                min-height: 74px;
            }

            .utility-item + .utility-item {
                border-left: 0;
                border-top: 1px solid rgba(255,255,255,.14);
            }

            .utility-content {
                width: min(100%, 360px);
            }

            .section-wrap {
                width: 100%;
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .section {
                padding: 3.5rem 0;
            }

            .services-section {
                padding-top: 3.1rem;
                padding-bottom: 3.5rem;
            }

            .hero-shell,
            .hero-slide,
            .hero-content {
                height: 540px;
                min-height: 540px;
                max-height: 540px;
            }

            .hero-copy {
                padding: 3.4rem 0 4.2rem;
            }

            .hero-title {
                font-size: clamp(2.7rem, 13vw, 4rem);
            }

            .quick-grid,
            .agency-grid,
            .steps,
            .partner-grid {
                grid-template-columns: 1fr;
            }

            .partner-main {
                min-height: 330px;
            }

            .partner-main-content {
                width: 68%;
            }

            .partner-main-image {
                width: 45%;
            }

            .utility-item + .utility-item {
                border-left: 0;
                border-top: 1px solid rgba(255,255,255,.18);
            }

            .tracking-box {
                flex-direction: column;
            }

            .tracking-box button {
                width: 100%;
            }

            .steps::before {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-primary,
            .hero-secondary {
                justify-content: center;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</head>

<body class="antialiased">

    {{-- =========================================================
         NAVBAR
    ========================================================== --}}
    <nav id="main-navbar" class="main-navbar z-50 border-b border-gray-100">
        <div class="!w-full !max-w-none px-4 sm:px-6 lg:px-10 py-3.5 flex items-center gap-4 lg:gap-6">

            <a href="{{ route('home') }}" class="shrink-0" aria-label="Venexpress - Inicio">
                <img src="{{ asset('images/venexpress-logo.png') }}" alt="Venexpress" class="h-8 sm:h-9 w-auto">
            </a>

            <div class="main-nav-links">
                <a href="{{ route('home') }}" class="main-nav-link is-active">Inicio</a>
                <a href="#servicios" class="main-nav-link">Servicios</a>
                <a href="{{ route('public.calculator') }}" class="main-nav-link">Calcular precio</a>
                <a href="{{ route('public.offices') }}" class="main-nav-link">Agencias aliadas</a>
                <a href="{{ route('public.marketplace') }}" class="main-nav-link">Tienda</a>
                <a href="#rastreo" class="main-nav-link">Rastreo</a>
                <a href="#ayuda" class="main-nav-link">Ayuda</a>            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 ml-auto">
                <form action="{{ route('tracking.show') }}" method="GET" class="desktop-tracking-form items-center border-l border-gray-200 pl-4">
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                        <input
                            type="text"
                            name="guia"
                            placeholder="Número de guía"
                            autocomplete="off"
                            spellcheck="false"
                            class="w-44 xl:w-52 border-0 text-[0.86rem] placeholder:text-gray-400 focus:ring-0 py-2.5 pl-3 pr-1"
                        >
                        <button type="submit" aria-label="Rastrear envío" class="h-full px-3 py-2.5 bg-[#111111] text-white hover:bg-amber-400 hover:text-[#111111] transition">
                            <i class="fa-solid fa-magnifying-glass text-[0.72rem]"></i>
                        </button>
                    </div>
                </form>

                <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center justify-center border border-[#111111] text-[#111111] hover:bg-[#111111] hover:text-white font-semibold text-[0.86rem] px-3.5 sm:px-4.5 py-2.5 rounded-lg transition whitespace-nowrap">
                    Regístrate
                </a>

                <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-amber-400 hover:bg-amber-500 text-[#111111] font-semibold text-[0.86rem] px-4 sm:px-5 py-2.5 rounded-lg transition whitespace-nowrap">
                    Iniciar sesión
                </a>

                <button id="mobile-menu-button" type="button" class="md:hidden w-10 h-10 shrink-0 rounded-lg border border-gray-200 text-[#111111] flex items-center justify-center" aria-label="Abrir menú" aria-expanded="false" aria-controls="mobile-menu">
                    <i id="mobile-menu-icon" class="fa-solid fa-bars"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-gray-100 bg-white md:hidden">
            <div class="w-full px-5 py-3">
                <a href="{{ route('home') }}" class="mobile-menu-link block py-3 text-sm font-semibold text-[#111111]">Inicio</a>
                <a href="#servicios" class="mobile-menu-link block py-3 text-sm text-gray-600">Servicios</a>
                <a href="{{ route('public.calculator') }}" class="mobile-menu-link block py-3 text-sm text-gray-600">Calcular precio</a>
                <a href="{{ route('public.offices') }}" class="mobile-menu-link block py-3 text-sm text-gray-600">Agencias aliadas</a>
                <a href="#rastreo" class="mobile-menu-link block py-3 text-sm text-gray-600">Rastreo</a>
                <a href="#ayuda" class="mobile-menu-link block py-3 text-sm text-gray-600">Ayuda</a>

                <form action="{{ route('tracking.show') }}" method="GET" class="py-3 border-t border-gray-100 mt-2">
                    <label for="mobile-guia" class="block text-xs font-semibold mb-2">Rastrea tu envío</label>
                    <div class="flex gap-2">
                        <input id="mobile-guia" type="text" name="guia" placeholder="Número de guía" class="flex-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-300 focus:border-amber-400">
                        <button type="submit" class="px-4 rounded-lg bg-[#111111] text-white font-semibold text-sm">Rastrear</button>
                    </div>
                </form>
            </div>
        </div>
    </nav>


    {{-- =========================================================
         HERO / CARRUSEL
    ========================================================== --}}
    <section id="hero-carousel" class="hero-shell relative overflow-hidden bg-white">

        {{-- Slide 1: Cliente / Envíos --}}
        <div id="hero-slide-0" class="hero-slide hero-slide-item relative">
            <div class="hero-image-panel">
                <img src="{{ asset('images/hero.png') }}" alt="" class="hero-image hero-image-contain">
            </div>

            <div class="section-wrap hero-content">
                <div class="hero-copy">
                    <span class="eyebrow">Envíos nacionales</span>

                    <h1 class="hero-title">
                        Conectamos<br>
                        a Venezuela.
                    </h1>

                    <p class="hero-text">
                        Envía paquetes y documentos de forma rápida, segura y sencilla, con agencias aliadas y seguimiento en línea.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('public.calculator') }}" class="hero-primary">
                            Enviar un paquete
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                        <a href="#rastreo" class="hero-secondary">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            Rastrear envío
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slide 2: Agencias aliadas --}}
        <div id="hero-slide-1" class="hero-slide hero-slide-item relative hidden">
            <div class="hero-image-panel">
                {{-- Placeholder visual: la imagen definitiva se integrará después. --}}
                <img src="{{ asset('images/skyline-hero.png') }}" alt="" class="hero-image opacity-70">
            </div>

            <div class="section-wrap hero-content">
                <div class="hero-copy">
                    <span class="eyebrow">Conviértete en aliado</span>

                    <h2 class="hero-title">
                        ¿Tienes un<br>
                        negocio?
                    </h2>

                    <p class="hero-text">
                        Convierte tu local en un punto de atención de nuestra red y ofrece nuevos servicios a tus clientes.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('register', ['role' => 'aliado']) }}" class="hero-primary hero-role-button">
                            Quiero ser aliado
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Slide 3: Repartidores --}}
        <div id="hero-slide-2" class="hero-slide hero-slide-item relative hidden">
            <div class="hero-image-panel">
                {{-- Placeholder visual: la imagen definitiva se integrará después. --}}
                <img src="{{ asset('images/skyline-hero.png') }}" alt="" class="hero-image opacity-65">
            </div>

            <div class="section-wrap hero-content">
                <div class="hero-copy">
                    <span class="eyebrow">Únete a nuestra red</span>

                    <h2 class="hero-title">
                        ¿Quieres repartir<br>
                        con nosotros?
                    </h2>

                    <p class="hero-text">
                        Conecta tu vehículo con nuestra red de distribución y forma parte de las entregas en tu ciudad.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('register', ['role' => 'repartidor']) }}" class="hero-primary hero-role-button">
                            Quiero ser repartidor
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="hero-dots" aria-label="Navegación del carrusel">
            <button type="button" data-hero-dot="0" class="hero-dot is-active" aria-label="Ver slide de cliente y envíos"></button>
            <button type="button" data-hero-dot="1" class="hero-dot" aria-label="Ver slide de agencias aliadas"></button>
            <button type="button" data-hero-dot="2" class="hero-dot" aria-label="Ver slide de repartidores"></button>
        </div>
    </section>


    {{-- =========================================================
         FRANJA DE VALOR
    ========================================================== --}}
    <section class="utility-strip">
        <div class="w-full px-4 sm:px-6 lg:px-10 grid md:grid-cols-3">

            <div class="utility-item px-3 sm:px-5">
                <div class="utility-content">
                    <div class="utility-icon" aria-hidden="true">
                        <i class="fa-solid fa-location-dot text-base"></i>
                    </div>
                    <div>
                        <p class="utility-title">Cobertura nacional</p>
                        <p class="utility-subtitle">Conectamos ciudades y estados.</p>
                    </div>
                </div>
            </div>

            <div class="utility-item px-3 sm:px-5">
                <div class="utility-content">
                    <div class="utility-icon" aria-hidden="true">
                        <i class="fa-solid fa-store text-base"></i>
                    </div>
                    <div>
                        <p class="utility-title">Agencias aliadas</p>
                        <p class="utility-subtitle">Recibe y entrega cerca de ti.</p>
                    </div>
                </div>
            </div>

            <div class="utility-item px-3 sm:px-5">
                <div class="utility-content">
                    <div class="utility-icon" aria-hidden="true">
                        <i class="fa-solid fa-box text-base"></i>
                    </div>
                    <div>
                        <p class="utility-title">Seguimiento en línea</p>
                        <p class="utility-subtitle">Consulta tu envío en cualquier momento.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- =========================================================
         SERVICIOS / ACCIONES
    ========================================================== --}}
    <section id="servicios" class="section services-section bg-white">
        <div class="section-wrap">
            <div class="section-heading">
                <span class="eyebrow">Todo en un solo lugar</span>
                <h2 class="section-title">Enviar nunca debería ser complicado.</h2>
                <p class="section-subtitle">Desde crear tu envío hasta recibirlo, encuentra en un solo lugar las acciones que más necesitas.</p>
            </div>

            <div class="quick-grid">
                <a href="{{ route('public.calculator') }}" class="quick-card group">
                    <div class="icon-chip"><i class="fa-solid fa-box"></i></div>
                    <span class="card-kicker">Tu envío</span>
                    <h3 class="card-title">Envía</h3>
                    <p class="card-text">Calcula el precio y crea el envío para llevarlo a una agencia.</p>
                    <span class="card-link">Crear envío <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i></span>
                </a>

                <a href="{{ route('public.offices') }}" class="quick-card group">
                    <div class="icon-chip"><i class="fa-solid fa-location-dot"></i></div>
                    <span class="card-kicker">Punto cercano</span>
                    <h3 class="card-title">Encuentra una agencia</h3>
                    <p class="card-text">Localiza el punto más cercano para entregar o recibir tu paquete.</p>
                    <span class="card-link">Ver agencias <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i></span>
                </a>

                <a href="#rastreo" class="quick-card group">
                    <div class="icon-chip"><i class="fa-solid fa-magnifying-glass"></i></div>
                    <span class="card-kicker">Seguimiento</span>
                    <h3 class="card-title">Rastrea tu envío</h3>
                    <p class="card-text">Consulta el estado de tu paquete con tu número de guía.</p>
                    <span class="card-link">Rastrear <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i></span>
                </a>

                <a href="#empresas" class="quick-card group">
                    <div class="icon-chip"><i class="fa-solid fa-building"></i></div>
                    <span class="card-kicker">Soluciones B2B</span>
                    <h3 class="card-title">Para empresas</h3>
                    <p class="card-text">Soluciones logísticas para negocios que necesitan mover productos.</p>
                    <span class="card-link">Conocer soluciones <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i></span>
                </a>
            </div>
        </div>
    </section>


    {{-- =========================================================
         RASTREO
    ========================================================== --}}
    <section id="rastreo" class="section section-soft">
        <div class="section-wrap tracking-layout">
            <div>
                <span class="eyebrow">¿Dónde está tu envío?</span>
                <h2 class="section-title">Introduce tu número de guía y consulta el estado de tu paquete.</h2>
                <p class="section-subtitle">Consulta rápidamente la información disponible de tu envío desde cualquier dispositivo.</p>

                <form action="{{ route('tracking.show') }}" method="GET" class="tracking-box">
                    <input type="text" name="guia" placeholder="Ej. VEN-2026-000123" autocomplete="off" spellcheck="false" aria-label="Número de guía">
                    <button type="submit">Rastrear envío</button>
                </form>

                <p class="tracking-note">
                    <i class="fa-regular fa-circle-question mr-1"></i>
                    ¿No encuentras tu número de guía? Consulta con la agencia donde realizaste tu envío.
                </p>
            </div>

            <div class="tracking-visual" aria-hidden="true">
                {{-- Placeholder para imagen de paquete final. --}}
                <div class="tracking-placeholder">
                    <i class="fa-solid fa-box text-2xl mb-2"></i>
                    <strong>Imagen de paquete</strong>
                    <span>Placeholder temporal para el diseño.</span>
                </div>
            </div>
        </div>
    </section>


    {{-- =========================================================
         COBERTURA
    ========================================================== --}}
    <section id="cobertura" class="section bg-white">
        <div class="section-wrap coverage-layout">
            <div>
                <span class="eyebrow">Llegamos más lejos</span>
                <h2 class="section-title">De una ciudad a otra, seguimos conectando Venezuela.</h2>
                <p class="section-subtitle">Conoce nuestra cobertura y encuentra el destino más cercano dentro de nuestra red.</p>

                <a href="{{ route('public.offices') }}" class="hero-primary mt-6">
                    Ver agencias y cobertura
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>

            <div class="outline-map" aria-label="Mapa conceptual de cobertura nacional">
                <span class="route-line" style="width: 31%; left: 29%; top: 37%; transform: rotate(3deg);"></span>
                <span class="route-line" style="width: 24%; left: 42%; top: 48%; transform: rotate(13deg);"></span>
                <span class="route-line" style="width: 27%; left: 47%; top: 47%; transform: rotate(-25deg);"></span>
                <span class="route-line" style="width: 21%; left: 48%; top: 49%; transform: rotate(37deg);"></span>

                <span class="route-dot" style="left: 27%; top: 35%;"></span>
                <span class="route-dot" style="left: 40%; top: 46%;"></span>
                <span class="route-dot" style="left: 49%; top: 45%;"></span>
                <span class="route-dot" style="left: 68%; top: 37%;"></span>
                <span class="route-dot" style="left: 69%; top: 63%;"></span>

                <span class="route-label" style="left: 23%; top: 27%;">Maracaibo</span>
                <span class="route-label" style="left: 38%; top: 51%;">Barquisimeto</span>
                <span class="route-label" style="left: 49%; top: 37%;">Valencia</span>
                <span class="route-label" style="left: 67%; top: 27%;">Caracas</span>
                <span class="route-label" style="left: 67%; top: 67%;">Barcelona</span>
            </div>
        </div>
    </section>


    {{-- =========================================================
         AGENCIAS
    ========================================================== --}}
    <section id="aliados" class="section section-soft">
        <div class="section-wrap">
            <div class="agency-header">
                <div class="section-heading">
                    <span class="eyebrow">Nuestra red</span>
                    <h2 class="section-title">Encuentra una agencia cerca de ti.</h2>
                    <p class="section-subtitle">Nuestras agencias aliadas son puntos donde puedes realizar y recibir tus envíos.</p>
                </div>

                <a href="{{ route('public.offices') }}" class="hidden sm:inline-flex items-center gap-2 text-[0.72rem] font-bold">
                    Ver todas las agencias
                    <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>
                </a>
            </div>

            <div class="agency-grid">
                <a href="{{ route('public.offices') }}" class="agency-card hover:shadow-md transition">
                    <div class="agency-thumb">Foto de agencia</div>
                    <div>
                        <p class="agency-name">Librería El Profe</p>
                        <p class="agency-location mt-1"><i class="fa-solid fa-location-dot mr-1"></i>Cumaná, Sucre</p>
                        <span class="agency-link">Ver ubicación <i class="fa-solid fa-arrow-right text-[0.5rem]"></i></span>
                    </div>
                </a>

                <a href="{{ route('public.offices') }}" class="agency-card hover:shadow-md transition">
                    <div class="agency-thumb">Foto de agencia</div>
                    <div>
                        <p class="agency-name">Papelería Los Amigos</p>
                        <p class="agency-location mt-1"><i class="fa-solid fa-location-dot mr-1"></i>Carúpano, Sucre</p>
                        <span class="agency-link">Ver ubicación <i class="fa-solid fa-arrow-right text-[0.5rem]"></i></span>
                    </div>
                </a>

                <a href="{{ route('public.offices') }}" class="agency-card hover:shadow-md transition">
                    <div class="agency-thumb">Foto de agencia</div>
                    <div>
                        <p class="agency-name">Tecnología 2000</p>
                        <p class="agency-location mt-1"><i class="fa-solid fa-location-dot mr-1"></i>Barcelona, Anzoátegui</p>
                        <span class="agency-link">Ver ubicación <i class="fa-solid fa-arrow-right text-[0.5rem]"></i></span>
                    </div>
                </a>

                <a href="{{ route('public.offices') }}" class="agency-card hover:shadow-md transition">
                    <div class="agency-thumb">Foto de agencia</div>
                    <div>
                        <p class="agency-name">Variedades San Rafael</p>
                        <p class="agency-location mt-1"><i class="fa-solid fa-location-dot mr-1"></i>Maturín, Monagas</p>
                        <span class="agency-link">Ver ubicación <i class="fa-solid fa-arrow-right text-[0.5rem]"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </section>


    {{-- =========================================================
         CÓMO FUNCIONA
    ========================================================== --}}
    <section id="como-funciona" class="section bg-white">
        <div class="section-wrap">
            <div class="section-heading">
                <span class="eyebrow">Tu envío, paso a paso</span>
                <h2 class="section-title">Así de fácil es enviar.</h2>
                <p class="section-subtitle">Un recorrido claro desde la entrega de tu paquete hasta su llegada a destino.</p>
            </div>

            <div class="steps">
                <div class="step">
                    <div class="step-number">01</div>
                    <h3 class="step-title">Entrega</h3>
                    <p class="step-text">Lleva tu paquete a una agencia aliada.</p>
                </div>
                <div class="step">
                    <div class="step-number">02</div>
                    <h3 class="step-title">Recolección</h3>
                    <p class="step-text">Nuestro equipo recibe y procesa tu envío.</p>
                </div>
                <div class="step">
                    <div class="step-number">03</div>
                    <h3 class="step-title">Traslado</h3>
                    <p class="step-text">Tu paquete viaja hacia su destino.</p>
                </div>
                <div class="step">
                    <div class="step-number">04</div>
                    <h3 class="step-title">Entrega</h3>
                    <p class="step-text">Llega a la agencia de destino para su retiro.</p>
                </div>
            </div>
        </div>
    </section>


    {{-- =========================================================
         EMPRESAS / ALIADOS / REPARTIDORES
    ========================================================== --}}
    <section id="empresas" class="section section-soft">
        <div class="section-wrap">
            <div class="section-heading">
                <span class="eyebrow">Más que envíos</span>
                <h2 class="section-title">Una red para clientes, empresas y aliados.</h2>
                <p class="section-subtitle">Elige la forma en que quieres formar parte del ecosistema logístico.</p>
            </div>

            <div class="partner-grid">
                <article class="partner-main">
                    <div class="partner-main-content">
                        <span class="text-[0.64rem] font-extrabold uppercase tracking-[0.08em]">Para empresas</span>
                        <h3 class="text-2xl lg:text-3xl font-extrabold leading-[1.02] tracking-tight mt-2">Haz que tus envíos trabajen para tu negocio.</h3>
                        <p class="text-[0.72rem] leading-6 mt-3 max-w-md text-black/70">Soluciones logísticas para negocios que necesitan enviar productos con mayor facilidad.</p>
                        <a href="{{ route('login') }}" class="mt-5 inline-flex items-center gap-2 text-[0.72rem] font-extrabold">Conocer soluciones <i class="fa-solid fa-arrow-right text-[0.58rem]"></i></a>
                    </div>
                    <img src="{{ asset('images/van-hero.png') }}" alt="" class="partner-main-image opacity-75">
                </article>

                <article class="partner-small">
                    <div class="icon-chip"><i class="fa-solid fa-store"></i></div>
                    <div>
                        <span class="card-kicker">Aliados</span>
                        <h3 class="card-title text-base">¿Tienes un negocio?</h3>
                        <p class="card-text">Conviértelo en un punto aliado y forma parte de nuestra red.</p>
                        <a href="{{ route('register', ['role' => 'aliado']) }}" class="card-link">Quiero ser aliado <i class="fa-solid fa-arrow-right text-[0.6rem]"></i></a>
                    </div>
                </article>

                <article class="partner-small">
                    <div class="icon-chip"><i class="fa-solid fa-motorcycle"></i></div>
                    <div>
                        <span class="card-kicker">Repartidores</span>
                        <h3 class="card-title text-base">¿Quieres repartir?</h3>
                        <p class="card-text">Únete a nuestra red y conecta tu vehículo con las entregas.</p>
                        <a href="{{ route('register', ['role' => 'repartidor']) }}" class="card-link">Quiero ser repartidor <i class="fa-solid fa-arrow-right text-[0.6rem]"></i></a>
                    </div>
                </article>
            </div>
        </div>
    </section>


    {{-- =========================================================
         FAQ
    ========================================================== --}}
    <section id="ayuda" class="section bg-white">
        <div class="section-wrap faq-layout">
            <div>
                <span class="eyebrow">Preguntas frecuentes</span>
                <h2 class="section-title">Todo lo que necesitas saber antes de enviar.</h2>
                <p class="section-subtitle">Respuestas rápidas para las dudas más comunes sobre tus envíos.</p>
            </div>

            <div class="faq-list">
                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>¿Cómo puedo realizar un envío?</span>
                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>
                    </button>
                    <div class="faq-answer">Puedes comenzar calculando el precio de tu envío y luego llevar el paquete a una agencia aliada para registrarlo.</div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>¿Dónde puedo entregar mi paquete?</span>
                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>
                    </button>
                    <div class="faq-answer">Consulta la sección de agencias para encontrar el punto disponible que te resulte más conveniente.</div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>¿Cómo puedo rastrear mi envío?</span>
                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>
                    </button>
                    <div class="faq-answer">Introduce tu número de guía en el buscador del menú superior o en la sección de rastreo de esta página.</div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>¿Cuánto cuesta un envío?</span>
                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>
                    </button>
                    <div class="faq-answer">El precio depende de las características del envío y su destino. Puedes utilizar el calculador para obtener una estimación.</div>
                </div>

                <div class="faq-item">
                    <button type="button" class="faq-question" aria-expanded="false">
                        <span>¿Qué puedo enviar?</span>
                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>
                    </button>
                    <div class="faq-answer">Las condiciones de envío dependen del tipo de contenido y de las políticas vigentes. Consulta las condiciones antes de registrar tu paquete.</div>
                </div>
            </div>
        </div>
    </section>


    {{-- =========================================================
         CTA FINAL
    ========================================================== --}}
    <section class="final-cta">
        <div class="w-full px-5 sm:px-6 lg:px-10 py-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-full bg-[#111111] text-amber-400 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-location-arrow"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">¿Listo para enviar?</h2>
                    <p class="text-xs sm:text-sm text-black/65 mt-1">Conecta con Venezuela a través de nuestra red.</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('public.calculator') }}" class="inline-flex items-center gap-2 rounded-full bg-[#111111] text-white px-5 py-2.5 text-[0.72rem] font-bold hover:bg-[#222] transition">
                    Crear un envío
                    <i class="fa-solid fa-arrow-right text-[0.58rem]"></i>
                </a>
                <a href="{{ route('public.offices') }}" class="inline-flex items-center gap-2 rounded-full border border-[#111111] text-[#111111] px-5 py-2.5 text-[0.72rem] font-bold hover:bg-white/55 transition">
                    <i class="fa-solid fa-location-dot text-[0.6rem]"></i>
                    Encontrar una agencia
                </a>
            </div>
        </div>
    </section>


    {{-- =========================================================
         FOOTER
    ========================================================== --}}
    <footer class="footer">
        <div class="w-full px-5 sm:px-6 lg:px-10 py-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-10">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center">
                    <img src="{{ asset('images/venexpress-logo.png') }}" alt="Venexpress" class="h-9 w-auto brightness-0 invert">
                </a>
                <p class="text-white/55 text-[0.69rem] leading-5 mt-4 max-w-xs">Conectamos a Venezuela con soluciones de envío rápidas, seguras y confiables.</p>

                <div class="flex items-center gap-2 mt-5">
                    <a href="#" aria-label="Facebook" class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <i class="fa-brands fa-facebook-f text-white text-xs"></i>
                    </a>
                    <a href="#" aria-label="Instagram" class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <i class="fa-brands fa-instagram text-white text-xs"></i>
                    </a>
                    <a href="#" aria-label="X" class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <i class="fa-brands fa-x-twitter text-white text-xs"></i>
                    </a>
                    <a href="#" aria-label="WhatsApp" class="w-8 h-8 rounded-full border border-white/10 flex items-center justify-center hover:bg-white/10 transition">
                        <i class="fa-brands fa-whatsapp text-white text-xs"></i>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-white text-[0.75rem] font-bold mb-4">Servicios</h3>
                <ul class="space-y-2.5">
                    <li><a href="{{ route('public.calculator') }}" class="footer-link">Envíos</a></li>
                    <li><a href="#rastreo" class="footer-link">Rastreo</a></li>
                    <li><a href="{{ route('public.offices') }}" class="footer-link">Agencias</a></li>
                    <li><a href="#empresas" class="footer-link">Empresas</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white text-[0.75rem] font-bold mb-4">Venexpress</h3>
                <ul class="space-y-2.5">
                    <li><a href="#" class="footer-link">Sobre nosotros</a></li>
                    <li><a href="{{ route('register', ['role' => 'repartidor']) }}" class="footer-link">Únete a la red</a></li>
                    <li><a href="{{ route('register', ['role' => 'aliado']) }}" class="footer-link">Sé aliado</a></li>
                    <li><a href="{{ route('login') }}" class="footer-link">Acceso</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white text-[0.75rem] font-bold mb-4">Ayuda</h3>
                <ul class="space-y-2.5">
                    <li><a href="#ayuda" class="footer-link">Preguntas frecuentes</a></li>
                    <li><a href="{{ route('public.privacy') }}" class="footer-link">Política de privacidad</a></li>
                    <li><a href="{{ route('public.terms') }}" class="footer-link">Términos y condiciones</a></li>
                    <li><a href="mailto:info@venexpress.com" class="footer-link">Contáctanos</a></li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="w-full px-5 sm:px-6 lg:px-10 py-5 flex flex-col sm:flex-row justify-between gap-2 text-[0.64rem] text-white/40">
                <span>&copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.</span>
                <span>Conectamos a Venezuela.</span>
            </div>
        </div>
    </footer>


    {{-- =========================================================
         JAVASCRIPT
    ========================================================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Menú móvil
            const menuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuIcon = document.getElementById('mobile-menu-icon');
            let closeMobileMenu = null;

            if (menuButton && mobileMenu && mobileMenuIcon) {
                const closeMenu = () => {
                    mobileMenu.classList.add('hidden');
                    mobileMenuIcon.classList.remove('fa-xmark');
                    mobileMenuIcon.classList.add('fa-bars');
                    menuButton.setAttribute('aria-expanded', 'false');
                };

                closeMobileMenu = closeMenu;

                menuButton.addEventListener('click', function () {
                    const isOpen = !mobileMenu.classList.contains('hidden');

                    if (isOpen) {
                        closeMenu();
                        return;
                    }

                    mobileMenu.classList.remove('hidden');
                    mobileMenuIcon.classList.remove('fa-bars');
                    mobileMenuIcon.classList.add('fa-xmark');
                    menuButton.setAttribute('aria-expanded', 'true');
                });

                document.querySelectorAll('.mobile-menu-link').forEach(link => {
                    link.addEventListener('click', closeMenu);
                });
            }

            // Navbar: se oculta al bajar y reaparece al subir.
            const mainNavbar = document.getElementById('main-navbar');
            let lastScrollY = window.scrollY;
            let scrollTicking = false;

            const updateNavbarOnScroll = () => {
                const currentScrollY = window.scrollY;
                const scrollDifference = currentScrollY - lastScrollY;

                if (currentScrollY <= 20) {
                    mainNavbar?.classList.remove('nav-hidden');
                } else if (scrollDifference > 6) {
                    mainNavbar?.classList.add('nav-hidden');
                    closeMobileMenu?.();
                } else if (scrollDifference < -6) {
                    mainNavbar?.classList.remove('nav-hidden');
                }

                lastScrollY = currentScrollY;
                scrollTicking = false;
            };

            window.addEventListener('scroll', function () {
                if (scrollTicking) return;

                scrollTicking = true;
                window.requestAnimationFrame(updateNavbarOnScroll);
            }, { passive: true });

            // Carrusel
            const heroSlides = Array.from(document.querySelectorAll('.hero-slide-item'));
            const heroDots = Array.from(document.querySelectorAll('.hero-dot'));
            let heroCurrent = 0;
            let heroTimer = null;

            function showHeroSlide(index) {
                if (!heroSlides.length) return;

                heroSlides.forEach((slide, i) => {
                    slide.classList.toggle('hidden', i !== index);
                });

                heroDots.forEach((dot, i) => {
                    dot.classList.toggle('is-active', i === index);
                });

                heroCurrent = index;
            }

            function startHeroAutoplay() {
                if (heroSlides.length < 2) return;

                clearInterval(heroTimer);
                heroTimer = setInterval(() => {
                    showHeroSlide((heroCurrent + 1) % heroSlides.length);
                }, 6000);
            }

            if (heroSlides.length && heroDots.length) {
                heroDots.forEach(dot => {
                    dot.addEventListener('click', function () {
                        showHeroSlide(Number(dot.dataset.heroDot));
                        startHeroAutoplay();
                    });
                });

                showHeroSlide(0);
                startHeroAutoplay();
            }

            // FAQ
            document.querySelectorAll('.faq-question').forEach(button => {
                button.addEventListener('click', function () {
                    const item = button.closest('.faq-item');
                    if (!item) return;

                    const isOpen = item.classList.contains('is-open');

                    document.querySelectorAll('.faq-item.is-open').forEach(openItem => {
                        openItem.classList.remove('is-open');
                        const openButton = openItem.querySelector('.faq-question');
                        if (openButton) openButton.setAttribute('aria-expanded', 'false');
                    });

                    if (!isOpen) {
                        item.classList.add('is-open');
                        button.setAttribute('aria-expanded', 'true');
                    }
                });
            });
        });
    </script>

</body>
</html>
