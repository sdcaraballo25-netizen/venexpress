<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rastrea tu envío | Venexpress</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --blue: #1f3f95;
            --blue-dark: #172f73;
            --blue-soft: #edf3ff;
            --yellow: #fbbd24;
            --yellow-dark: #f3ad0b;
            --red: #e52b2f;
            --text: #14213d;
            --muted: #64748b;
            --line: #e2e8f0;
            --bg: #f6f8fc;
            --white: #ffffff;
            --green: #059669;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Inter", sans-serif;
            color: var(--text);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        img,
        svg {
            display: block;
            max-width: 100%;
        }

        :focus-visible {
            outline: 3px solid rgba(251, 189, 36, .55);
            outline-offset: 3px;
        }

        .container {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        /* HEADER */
        .site-header {
            height: 76px;
            background: var(--white);
            border-bottom: 1px solid #edf0f5;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 21px;
            color: var(--blue);
            letter-spacing: -.03em;
            white-space: nowrap;
        }

        .brand-mark {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
        }

        .brand-express {
            color: var(--red);
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 34px;
        }

        .main-nav a {
            color: #52627d;
            font-size: 14px;
            font-weight: 600;
            transition: color .2s ease;
        }

        .main-nav a:hover {
            color: var(--blue);
        }

        .login-btn {
            background: var(--yellow);
            color: #17213a !important;
            padding: 11px 22px;
            border-radius: 9px;
            font-weight: 700 !important;
        }

        .login-btn:hover {
            background: var(--yellow-dark);
        }

        /* HERO */
        .hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(circle at 85% 20%, rgba(31, 63, 149, .10), transparent 28%),
                linear-gradient(180deg, #ffffff 0%, #f6f9ff 100%);
            padding: 74px 0 82px;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            right: -240px;
            top: 40px;
            border-radius: 50%;
            border: 70px solid rgba(31, 63, 149, .045);
            pointer-events: none;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 64px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--blue);
            background: var(--blue-soft);
            border: 1px solid #dce7ff;
            border-radius: 999px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .eyebrow::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--yellow);
            box-shadow: 0 0 0 4px rgba(251,189,36,.16);
        }

        .hero-title {
            font-size: clamp(44px, 5.5vw, 72px);
            line-height: 1.02;
            letter-spacing: -.055em;
            font-weight: 800;
            color: var(--blue);
            max-width: 650px;
            margin-bottom: 20px;
        }

        .hero-title span {
            color: var(--red);
        }

        .hero-copy {
            color: #64748b;
            font-size: 17px;
            line-height: 1.65;
            max-width: 570px;
            margin-bottom: 32px;
        }

        /* SEARCH CARD */
        .search-card {
            width: min(640px, 100%);
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 18px 45px rgba(30, 55, 105, .10);
            padding: 10px;
        }

        .search-row {
            display: flex;
            min-height: 58px;
        }

        .input-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 17px;
            min-width: 0;
        }

        .input-icon {
            width: 21px;
            height: 21px;
            color: #94a3b8;
            flex: 0 0 auto;
        }

        .tracking-input {
            width: 100%;
            border: 0;
            outline: 0;
            color: var(--text);
            background: transparent;
            font-size: 15px;
            min-width: 0;
        }

        .tracking-input::placeholder {
            color: #94a3b8;
        }

        .search-btn {
            border: 0;
            border-radius: 11px;
            background: var(--blue);
            color: var(--white);
            min-width: 132px;
            padding: 0 22px;
            cursor: pointer;
            font-weight: 800;
            transition: background .2s ease, transform .2s ease;
        }

        .search-btn:hover {
            background: var(--blue-dark);
            transform: translateY(-1px);
        }

        .scan-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .scan-button {
            min-height: 60px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #dbe4f4;
            background: #f8faff;
            border-radius: 12px;
            padding: 10px 14px;
            cursor: pointer;
            text-align: left;
            transition: border-color .2s, background .2s, transform .2s;
        }

        .scan-button:hover {
            border-color: #b9c9eb;
            background: #f1f5ff;
            transform: translateY(-1px);
        }

        .scan-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--blue);
            color: var(--white);
            font-size: 19px;
            flex: 0 0 auto;
        }

        .scan-button.photo .scan-icon {
            background: #fff7dc;
            color: #b77900;
        }

        .scan-title {
            display: block;
            color: var(--text);
            font-size: 13px;
            font-weight: 800;
        }

        .scan-subtitle {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 3px;
        }

        .file-input {
            display: none;
        }

        .ocr-status {
            display: none;
            margin-top: 10px;
            border-radius: 11px;
            padding: 11px 13px;
            font-size: 12px;
            line-height: 1.5;
        }

        .ocr-status.show {
            display: block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
        }

        .ocr-status.success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }

        .ocr-status.error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        .try-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 17px;
        }

        .try-label {
            color: #94a3b8;
            font-size: 11px;
            font-weight: 700;
        }

        .try-chip {
            border: 1px solid #d8e0ec;
            background: var(--white);
            color: var(--blue);
            border-radius: 7px;
            padding: 6px 9px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }

        .try-chip:hover {
            border-color: #a9bce4;
            background: var(--blue-soft);
        }

        .try-chip-static {
            cursor: default;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.02em;
        }

        .try-chip-static:hover {
            border-color: #d8e0ec;
            background: var(--white);
        }

        /* HERO VISUAL */
        .hero-visual {
            min-height: 360px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visual-card {
            width: min(500px, 100%);
            aspect-ratio: 1.15 / 1;
            background: linear-gradient(145deg, #edf3ff, #ffffff);
            border: 1px solid #e0e8f7;
            border-radius: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(31, 63, 149, .12);
        }

        .visual-card::before {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            right: -90px;
            top: -100px;
            background: rgba(31, 63, 149, .08);
        }

        .visual-road {
            position: absolute;
            left: 8%;
            right: 8%;
            bottom: 19%;
            height: 2px;
            background: repeating-linear-gradient(
                to right,
                #aab8d2 0 8px,
                transparent 8px 17px
            );
        }

        .visual-route {
            position: absolute;
            left: 11%;
            right: 11%;
            bottom: 12%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .route-point {
            width: 13px;
            height: 13px;
            background: var(--yellow);
            border: 3px solid var(--white);
            border-radius: 50%;
            box-shadow: 0 2px 7px rgba(31,63,149,.2);
        }

        .route-point.active {
            width: 17px;
            height: 17px;
            background: var(--blue);
        }

        .van {
            position: absolute;
            left: 50%;
            top: 48%;
            transform: translate(-50%, -50%);
            width: 88%;
            max-width: 455px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 24px 20px rgba(31, 63, 149, .20));
            z-index: 2;
        }

        .visual-badge {
            position: absolute;
            left: 7%;
            top: 8%;
            background: var(--white);
            border: 1px solid #e1e7f1;
            box-shadow: 0 10px 25px rgba(30,55,105,.10);
            border-radius: 12px;
            padding: 11px 13px;
        }

        .visual-badge strong {
            display: block;
            color: var(--blue);
            font-size: 12px;
        }

        .visual-badge span {
            display: block;
            color: var(--muted);
            font-size: 10px;
            margin-top: 3px;
        }

        .visual-status {
            position: absolute;
            right: 7%;
            bottom: 8%;
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--white);
            border: 1px solid #e1e7f1;
            box-shadow: 0 10px 25px rgba(30,55,105,.10);
            border-radius: 12px;
            padding: 10px 12px;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 0 4px rgba(5,150,105,.12);
        }

        /* TRUST BAR */
        .trust-bar {
            background: var(--blue);
            color: var(--white);
            padding: 24px 0;
        }

        .trust-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 22px;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .trust-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: rgba(255,255,255,.10);
            color: var(--yellow);
            font-size: 18px;
            flex: 0 0 auto;
        }

        .trust-item strong {
            display: block;
            font-size: 12px;
            line-height: 1.35;
        }

        .trust-item span {
            display: block;
            margin-top: 2px;
            color: rgba(255,255,255,.65);
            font-size: 10px;
        }

        /* HOW IT WORKS */
        .section {
            padding: 82px 0;
            background: var(--white);
        }

        .section-heading {
            text-align: center;
            max-width: 650px;
            margin: 0 auto 45px;
        }

        .section-kicker {
            color: var(--blue);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: 9px;
        }

        .section-title {
            color: var(--text);
            font-size: clamp(28px, 4vw, 40px);
            letter-spacing: -.035em;
            margin-bottom: 12px;
        }

        .section-copy {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.6;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .step {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 24px 20px;
            background: var(--white);
            box-shadow: 0 8px 25px rgba(15,23,42,.045);
        }

        .step-number {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            background: var(--blue-soft);
            color: var(--blue);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 18px;
        }

        .step h3 {
            font-size: 15px;
            margin-bottom: 8px;
        }

        .step p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        /* FOOTER */
        footer {
            background: #f8fafc;
            border-top: 1px solid var(--line);
            padding: 30px 0;
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .footer-copy {
            color: var(--muted);
            font-size: 12px;
        }

        /* RESPONSIVE */
        @media (max-width: 950px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 42px;
            }

            .hero-copy,
            .hero-title {
                max-width: 700px;
            }

            .hero-visual {
                min-height: 330px;
            }

            .trust-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .steps {
                grid-template-columns: repeat(2, 1fr);
            }

            .main-nav {
                gap: 18px;
            }
        }

        @media (max-width: 700px) {
            .container {
                width: min(100% - 28px, 600px);
            }

            .site-header {
                height: 68px;
            }

            .brand {
                font-size: 18px;
            }

            .brand-mark {
                width: 34px;
                height: 34px;
            }

            .main-nav .nav-link {
                display: none;
            }

            .main-nav {
                gap: 0;
            }

            .login-btn {
                padding: 10px 15px;
            }

            .hero {
                padding: 48px 0 58px;
            }

            .hero-title {
                font-size: clamp(42px, 13vw, 58px);
            }

            .hero-copy {
                font-size: 15px;
                margin-bottom: 24px;
            }

            .search-card {
                border-radius: 15px;
                padding: 8px;
            }

            .search-row {
                display: grid;
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .input-wrap {
                min-height: 53px;
            }

            .search-btn {
                min-height: 50px;
                width: 100%;
            }

            .scan-options {
                grid-template-columns: 1fr;
            }

            .hero-visual {
                min-height: 290px;
            }

            .visual-card {
                border-radius: 24px;
            }

            .trust-grid {
                grid-template-columns: 1fr 1fr;
                gap: 18px 10px;
            }

            .trust-item strong {
                font-size: 10px;
            }

            .trust-item span {
                font-size: 9px;
            }

            .section {
                padding: 60px 0;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .footer-inner {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 420px) {
            .trust-grid {
                grid-template-columns: 1fr;
            }

            .hero-visual {
                min-height: 245px;
            }

            .visual-badge,
            .visual-status {
                transform: scale(.88);
            }
        }
    </style>
</head>

<body>

<header class="site-header">
    <div class="container header-inner">
        <a href="{{ url('/') }}" class="brand" aria-label="Venexpress">
            <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M8 13.5 25 5l15 7.5v19L23 40l-15-7.5v-19Z" fill="#FBBE24"/>
                <path d="M25 5v18l15-7.5" stroke="#1F3F95" stroke-width="3" stroke-linejoin="round"/>
                <path d="M8 13.5 23 21v19" stroke="#1F3F95" stroke-width="3" stroke-linejoin="round"/>
                <path d="m17 16 9-4.5v9l-9 4.5v-9Z" fill="#fff"/>
            </svg>
            VEN<span class="brand-express">EXPRESS</span>
        </a>

        <nav class="main-nav">
            <a href="{{ url('/') }}" class="nav-link">Inicio</a>
            <a href="{{ route('public.calculator') }}" class="nav-link">Servicios</a>
            <a href="{{ route('public.calculator') }}" class="nav-link">Calcular precio</a>
            <a href="{{ route('public.offices') }}" class="nav-link">Agencias aliadas</a>
            <a href="#rastreo" class="nav-link">Rastreo</a>
            <a href="{{ route('login') }}" class="login-btn">Iniciar sesión</a>
        </nav>
    </div>
</header>

<main>

<section class="hero" id="rastreo">
    <div class="container hero-grid">

        <div>
            <div class="eyebrow">Seguimiento de envíos</div>

            <h1 class="hero-title">
                Rastrea tu <span>envío</span>
            </h1>

            <p class="hero-copy">
                Consulta rápidamente dónde se encuentra tu paquete.
                Escribe tu número de guía o utiliza la cámara de tu teléfono
                para leerlo automáticamente.
            </p>

            <form
                id="tracking-form"
                class="search-card"
                method="GET"
                action="{{ route('tracking.show') }}"
            >
                <div class="search-row">
                    <div class="input-wrap">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                            <path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>

                        <input
                            id="tracking-guide"
                            class="tracking-input"
                            type="text"
                            name="guia"
                            value="{{ request('guia') }}"
                            placeholder="Ej. VEN-20260904-000123"
                            autocomplete="off"
                            spellcheck="false"
                            required
                        >
                    </div>

                    <button class="search-btn" type="submit">
                        Rastrear
                    </button>
                </div>

                <div class="scan-options">

                    <label class="scan-button" for="tracking-camera">
                        <span class="scan-icon">📷</span>
                        <span>
                            <span class="scan-title">Usar cámara</span>
                            <span class="scan-subtitle">Fotografiar la guía</span>
                        </span>
                    </label>

                    <input
                        id="tracking-camera"
                        class="file-input"
                        type="file"
                        accept="image/*"
                        capture="environment"
                    >

                    <label class="scan-button photo" for="tracking-photo">
                        <span class="scan-icon">🖼️</span>
                        <span>
                            <span class="scan-title">Subir una foto</span>
                            <span class="scan-subtitle">Elegir desde la galería</span>
                        </span>
                    </label>

                    <input
                        id="tracking-photo"
                        class="file-input"
                        type="file"
                        accept="image/*"
                    >
                </div>

                <div id="ocr-status" class="ocr-status" role="status"></div>
            </form>

            <div class="try-row">
                <span class="try-label">Formato de guía:</span>
                <span class="try-chip try-chip-static">VEN-20260904-000123</span>
            </div>
            {{--
                Antes había botones "Probar con: VE-00001 / VE-00002" que
                rellenaban el input con guías de ejemplo. Esos números no
                existen en la base de datos (el generador real produce
                VEN-YYYYMMDD-NNNNNN), así que siempre devolvían "no
                encontrado". Se reemplazan por un texto ilustrativo del
                formato en vez de un botón que siempre falla.
            --}}
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="visual-card">

                <div class="visual-badge">
                    <strong>Seguimiento activo</strong>
                    <span>Tu paquete está en ruta</span>
                </div>

                <!-- Misma camioneta utilizada en la landing principal -->
                <img
                    src="{{ asset('images/van-hero.png') }}"
                    alt="Camioneta Venexpress"
                    class="van"
                />

                <div class="visual-road"></div>

                <div class="visual-route">
                    <span class="route-point"></span>
                    <span class="route-point active"></span>
                    <span class="route-point"></span>
                </div>

                <div class="visual-status">
                    <span class="status-dot"></span>
                    Rastreo disponible
                </div>
            </div>
        </div>

    </div>
</section>

<section class="trust-bar">
    <div class="container trust-grid">

        <div class="trust-item">
            <span class="trust-icon">🛡</span>
            <span>
                <strong>Envíos seguros</strong>
                <span>Seguimiento de tu paquete</span>
            </span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">📍</span>
            <span>
                <strong>Cobertura nacional</strong>
                <span>Principales ciudades</span>
            </span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">🤝</span>
            <span>
                <strong>Agencias aliadas</strong>
                <span>Red de atención</span>
            </span>
        </div>

        <div class="trust-item">
            <span class="trust-icon">🎧</span>
            <span>
                <strong>Atención al cliente</strong>
                <span>Estamos para ayudarte</span>
            </span>
        </div>

    </div>
</section>

<section class="section">
    <div class="container">

        <div class="section-heading">
            <div class="section-kicker">Así funciona</div>
            <h2 class="section-title">Sigue tu paquete en pocos pasos</h2>
            <p class="section-copy">
                Desde que entregas tu paquete hasta que llega a su destino,
                puedes consultar su recorrido utilizando tu número de guía.
            </p>
        </div>

        <div class="steps">

            <article class="step">
                <div class="step-number">01</div>
                <h3>Obtén tu guía</h3>
                <p>
                    Encuentra el número de guía que aparece en tu comprobante de envío.
                </p>
            </article>

            <article class="step">
                <div class="step-number">02</div>
                <h3>Escríbela o escanéala</h3>
                <p>
                    Puedes escribirla manualmente o usar la cámara para leerla desde una foto.
                </p>
            </article>

            <article class="step">
                <div class="step-number">03</div>
                <h3>Consulta el estado</h3>
                <p>
                    Presiona “Rastrear” y revisa el estado más reciente registrado.
                </p>
            </article>

            <article class="step">
                <div class="step-number">04</div>
                <h3>Recibe tu paquete</h3>
                <p>
                    Consulta las actualizaciones de tu envío hasta completar la entrega.
                </p>
            </article>

        </div>
    </div>
</section>

</main>

<footer>
    <div class="container footer-inner">
        <a href="{{ url('/') }}" class="brand">
            <svg class="brand-mark" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M8 13.5 25 5l15 7.5v19L23 40l-15-7.5v-19Z" fill="#FBBE24"/>
                <path d="M25 5v18l15-7.5" stroke="#1F3F95" stroke-width="3" stroke-linejoin="round"/>
                <path d="M8 13.5 23 21v19" stroke="#1F3F95" stroke-width="3" stroke-linejoin="round"/>
                <path d="m17 16 9-4.5v9l-9 4.5v-9Z" fill="#fff"/>
            </svg>
            VEN<span class="brand-express">EXPRESS</span>
        </a>

        <p class="footer-copy">
            © {{ date('Y') }} Venexpress. Todos los derechos reservados.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('tracking-form');
    const input = document.getElementById('tracking-guide');
    const camera = document.getElementById('tracking-camera');
    const photo = document.getElementById('tracking-photo');
    const status = document.getElementById('ocr-status');

    if (!form || !input || !camera || !photo || !status) {
        return;
    }

    function setStatus(message, type = '') {
        status.textContent = message;
        status.className = 'ocr-status show' + (type ? ' ' + type : '');
    }

    function normalizeGuide(value) {
        return value
            .toUpperCase()
            .replace(/[|]/g, 'I')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .trim();
    }

    function extractGuide(text) {
        const clean = String(text || '')
            .toUpperCase()
            .replace(/\n/g, ' ')
            .replace(/\s+/g, ' ');

        const patterns = [
            // Formato real generado por el sistema: VEN-YYYYMMDD-NNNNNN
            // (8 dígitos de fecha + 4 a 8 dígitos de secuencia). Debe ir
            // primero: si no, los patrones más genéricos de abajo
            // capturan solo el bloque de fecha y truncan la guía.
            /\bVEN[-\s]?\d{8}[-\s]?\d{4,8}\b/,
            /\bVEN[-\s]?\d{4}[-\s]?\d{5,10}\b/,
            /\bVE[-\s]?\d{4}[-\s]?\d{5,10}\b/,
            /\bVEN[-\s]?\d{5,16}\b/,
            /\bVE[-\s]?\d{5,16}\b/
        ];

        for (const pattern of patterns) {
            const match = clean.match(pattern);
            if (match) {
                return normalizeGuide(match[0]);
            }
        }

        // Tolerancia para errores comunes del OCR:
        // VK -> VE, VFN -> VEN
        const tolerant = clean.match(/\bV[A-Z]{1,2}[-\s]?\d{4}[-\s]?\d{5,10}\b/);

        if (tolerant) {
            let guide = normalizeGuide(tolerant[0]);
            guide = guide.replace(/^VK-/, 'VE-');
            guide = guide.replace(/^VFN-/, 'VEN-');
            return guide;
        }

        return null;
    }

    async function processImage(file) {
        if (!file) {
            return;
        }

        setStatus('🔎 Analizando la foto y buscando el número de guía...');

        try {
            const result = await Tesseract.recognize(
                file,
                'eng',
                {
                    logger: function (info) {
                        if (info.status === 'recognizing text') {
                            const progress = Math.round((info.progress || 0) * 100);
                            setStatus('🔎 Reconociendo la guía... ' + progress + '%');
                        }
                    }
                }
            );

            const guide = extractGuide(result.data.text);

            if (!guide) {
                setStatus(
                    'No pude identificar la guía. Intenta con una foto más clara o escríbela manualmente.',
                    'error'
                );
                input.focus();
                return;
            }

            input.value = guide;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));

            setStatus(
                '✓ Guía detectada: ' + guide + '. Presiona “Rastrear” para consultar el envío.',
                'success'
            );

        } catch (error) {
            console.error('OCR tracking error:', error);

            setStatus(
                'No se pudo leer la imagen. Puedes escribir la guía manualmente.',
                'error'
            );
        }
    }

    camera.addEventListener('change', function () {
        processImage(this.files[0]);
        this.value = '';
    });

    photo.addEventListener('change', function () {
        processImage(this.files[0]);
        this.value = '';
    });

    form.addEventListener('submit', function (event) {
        if (!input.value.trim()) {
            event.preventDefault();
            setStatus('Escribe o escanea un número de guía para continuar.', 'error');
            input.focus();
        }
    });
});
</script>

</body>
</html>
