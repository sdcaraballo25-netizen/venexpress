<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Rastrea tu envío | Venexpress</title>

    <link rel="icon" href="{{ asset('images/venexpress-logo-solo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        #rastreo,
        #como-funciona {
            scroll-margin-top: 90px;
        }

        /* =====================================================
           HERO
        ====================================================== */

        .tracking-hero {
            position: relative;
            overflow: hidden;
            background: #ffffff;
        }

        .tracking-hero-bg {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .tracking-hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(
                    90deg,
                    rgba(255,255,255,0.96) 0%,
                    rgba(255,255,255,0.92) 40%,
                    rgba(255,255,255,0.72) 68%,
                    rgba(255,255,255,0.45) 100%
                );
        }

        .tracking-hero-content {
            position: relative;
            z-index: 10;
            min-height: 575px;
        }

        .tracking-title {
            font-size: 3.75rem;
            line-height: 0.98;
            letter-spacing: -0.045em;
        }

        .tracking-description {
            max-width: 590px;
        }

        /* =====================================================
           TRACKING CARD
        ====================================================== */

        .tracking-card {
            width: 100%;
            max-width: 610px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 18px;
            box-shadow:
                0 20px 50px rgba(15, 23, 42, 0.10),
                0 4px 14px rgba(15, 23, 42, 0.04);
            padding: 10px;
        }

        .tracking-input-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1;
            min-height: 56px;
            padding: 0 16px;
        }

        .tracking-input {
            width: 100%;
            min-width: 0;
            border: 0;
            outline: none;
            background: transparent;
            color: #0A0A09;
            font-size: 14px;
            font-weight: 500;
        }

        .tracking-input::placeholder {
            color: #B8B8B2;
        }

        .tracking-search-button {
            min-height: 54px;
            min-width: 128px;
            border: 0;
            border-radius: 11px;
            background: #0A0A09;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            padding: 0 22px;
            cursor: pointer;
            transition:
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .tracking-search-button:hover {
            background: #111111;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(23, 37, 84, 0.18);
        }

        .tracking-search-button:active {
            transform: translateY(0);
        }

        /* =====================================================
           SCAN OPTIONS
        ====================================================== */

        .scan-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 10px;
        }

        .scan-button {
            min-height: 66px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid #E5E5E0;
            background: #F7F7F4;
            border-radius: 12px;
            padding: 10px 13px;
            cursor: pointer;
            text-align: left;
            transition:
                border-color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        button.scan-button {
            width: 100%;
            font-family: inherit;
        }

        .scan-button:hover {
            border-color: #D9D9D3;
            background: #F7F7F4;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.05);
        }

        .scan-button:focus-visible {
            outline: 3px solid rgba(59, 130, 246, 0.25);
            outline-offset: 2px;
        }

        .scan-button.photo:hover {
            border-color: #FFFC70;
            background: #FFFEEB;
        }

        .scan-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #0A0A09;
            color: #ffffff;
        }

        .scan-button.photo .scan-icon {
            background: #FFFDBA;
            color: #B8BF00;
        }

        .scan-title {
            display: block;
            color: #0A0A09;
            font-size: 12px;
            font-weight: 700;
        }

        .scan-subtitle {
            display: block;
            color: #6B6B66;
            font-size: 10px;
            margin-top: 2px;
        }

        .file-input {
            display: none;
        }

        /* =====================================================
           OCR STATUS
        ====================================================== */

        .ocr-status {
            display: none;
            margin-top: 10px;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 11px;
            line-height: 1.55;
        }

        .ocr-status.show {
            display: block;
            background: #F7F7F4;
            border: 1px solid #D9D9D3;
            color: #2A2A26;
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

        /* =====================================================
           CAMERA MODAL
        ====================================================== */

        .camera-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            background: rgba(0, 0, 0, 0.94);
        }

        .camera-modal.is-open {
            display: block;
        }

        .camera-modal-inner {
            width: 100%;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        .camera-header {
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            color: #ffffff;
        }

        .camera-header-title {
            font-size: 17px;
            font-weight: 700;
            line-height: 1.3;
        }

        .camera-header-subtitle {
            margin-top: 3px;
            color: rgba(255,255,255,0.65);
            font-size: 11px;
        }

        .camera-close-button {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .camera-close-button:hover {
            background: rgba(255,255,255,0.16);
            transform: scale(1.03);
        }

        .camera-preview-area {
            position: relative;
            flex: 1 1 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            min-height: 0;
        }

        .camera-preview-wrapper {
            position: relative;
            width: 100%;
            max-width: 760px;
            overflow: hidden;
            border-radius: 18px;
            background: #000000;
            box-shadow: 0 25px 70px rgba(0,0,0,0.45);
        }

        #qr-reader {
            width: 100%;
            min-height: 260px;
            background: #000000;
            border: 0 !important;
        }

        /* html5-qrcode fija el ancho del <video> en línea; se ajusta al visor. */
        #qr-reader video {
            display: block;
            width: 100% !important;
            height: auto;
            max-height: 70dvh;
            min-height: 260px;
            object-fit: cover;
            background: #000000;
        }

        .camera-guide-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .camera-guide-box {
            position: relative;
            width: min(62vw, 250px);
            aspect-ratio: 1 / 1;
            border: 1px solid rgba(255,255,255,0.65);
            border-radius: 12px;
            box-shadow: 0 0 0 9999px rgba(0,0,0,0.18);
        }

        .camera-guide-corner {
            position: absolute;
            width: 26px;
            height: 26px;
            border-color: #ffffff;
            border-style: solid;
        }

        .camera-guide-corner.top-left {
            left: -2px;
            top: -2px;
            border-width: 4px 0 0 4px;
            border-radius: 7px 0 0 0;
        }

        .camera-guide-corner.top-right {
            right: -2px;
            top: -2px;
            border-width: 4px 4px 0 0;
            border-radius: 0 7px 0 0;
        }

        .camera-guide-corner.bottom-left {
            left: -2px;
            bottom: -2px;
            border-width: 0 0 4px 4px;
            border-radius: 0 0 0 7px;
        }

        .camera-guide-corner.bottom-right {
            right: -2px;
            bottom: -2px;
            border-width: 0 4px 4px 0;
            border-radius: 0 0 7px 0;
        }

        .camera-scan-line {
            position: absolute;
            left: 4%;
            right: 4%;
            top: 8%;
            height: 2px;
            border-radius: 999px;
            background: rgba(255,255,255,0.75);
            box-shadow: 0 0 12px rgba(255,255,255,0.6);
            animation: cameraScanLine 2s ease-in-out infinite;
        }

        @keyframes cameraScanLine {
            0%,
            100% {
                top: 8%;
                opacity: 0.55;
            }

            50% {
                top: 92%;
                opacity: 1;
            }
        }

        .camera-bottom {
            flex: 0 0 auto;
            padding: 14px 20px 28px;
        }

        .camera-error {
            width: 100%;
            max-width: 620px;
            margin: 0 auto 12px;
            border-radius: 10px;
            padding: 10px 12px;
            background: rgba(127,29,29,0.45);
            border: 1px solid rgba(252,165,165,0.35);
            color: #fecaca;
            text-align: center;
            font-size: 11px;
            line-height: 1.5;
        }

        .camera-error.hidden {
            display: none;
        }

        .camera-hint {
            margin-top: 10px;
            color: rgba(255,255,255,0.55);
            font-size: 10px;
            text-align: center;
        }

        /* =====================================================
           FORMAT GUIDE
        ====================================================== */

        .format-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 13px;
        }

        .format-label {
            color: #B8B8B2;
            font-size: 10px;
            font-weight: 600;
        }

        .format-chip {
            display: inline-flex;
            align-items: center;
            min-height: 27px;
            padding: 4px 9px;
            border: 1px solid #E5E5E0;
            border-radius: 7px;
            background: #ffffff;
            color: #111111;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        /* =====================================================
           HERO VISUAL
        ====================================================== */

        /* Imagen de fondo de la mitad derecha del hero (sin caja, borde ni
           radio). El lado izquierdo se funde con el blanco mediante una
           máscara CSS: el archivo no trae el degradado horneado. */
        .tracking-visual {
            position: relative;
            z-index: 5;
        }

        .tracking-van {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1672 / 940;
            object-fit: cover;
            object-position: 80% center;
            /* < 1024px: la imagen va bajo el contenido y se funde por arriba */
            -webkit-mask-image: linear-gradient(to bottom, transparent 0%, #000 24%);
            mask-image: linear-gradient(to bottom, transparent 0%, #000 24%);
        }

        @media (min-width: 1024px) {

            .tracking-visual {
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 55%;
            }

            .tracking-van {
                height: 100%;
                aspect-ratio: auto;
                -webkit-mask-image: linear-gradient(
                    to right,
                    transparent 0%,
                    rgba(0,0,0,0.2) 8%,
                    rgba(0,0,0,0.5) 18%,
                    rgba(0,0,0,0.85) 30%,
                    #000 40%
                );
                mask-image: linear-gradient(
                    to right,
                    transparent 0%,
                    rgba(0,0,0,0.2) 8%,
                    rgba(0,0,0,0.5) 18%,
                    rgba(0,0,0,0.85) 30%,
                    #000 40%
                );
            }
        }

        .tracking-badge {
            position: absolute;
            left: 8%;
            top: 9%;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            background: rgba(255,255,255,0.95);
            border: 1px solid #E5E5E0;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
        }

        .tracking-badge-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #F7F7F4;
            color: #111111;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tracking-badge strong {
            display: block;
            color: #0A0A09;
            font-size: 11px;
            line-height: 1.3;
        }

        .tracking-badge span {
            display: block;
            margin-top: 2px;
            color: #6B6B66;
            font-size: 9px;
        }

        .tracking-status {
            position: absolute;
            left: 34%;
            bottom: 5%;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            background: rgba(255,255,255,0.96);
            border: 1px solid #E5E5E0;
            border-radius: 10px;
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
            color: #2A2A26;
            font-size: 10px;
            font-weight: 600;
        }

        .tracking-status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #059669;
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.10);
        }

        /* =====================================================
           BENEFITS
        ====================================================== */

        .benefits-section {
            background: #0A0A09;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .benefit-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 auto;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F7FF00;
        }

        .benefit-text {
            color: #ffffff;
            font-size: 12px;
            line-height: 1.45;
        }

        .benefit-text small {
            display: block;
            margin-top: 2px;
            color: #D9D9D3;
            font-size: 9px;
        }

        /* =====================================================
           HOW IT WORKS
        ====================================================== */

        .steps-line {
            position: absolute;
            top: 39px;
            left: 13%;
            right: 13%;
            border-top: 2px dashed #d1d5db;
            z-index: 0;
        }

        .step-icon {
            position: relative;
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            background: #F0F0EC;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step-number {
            position: absolute;
            top: -5px;
            left: -4px;
            width: 27px;
            height: 27px;
            border-radius: 50%;
            background: #0A0A09;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        .step-title {
            margin-top: 15px;
            color: #0A0A09;
            font-size: 14px;
            font-weight: 600;
        }

        .step-description {
            margin-top: 3px;
            color: #6B6B66;
            font-size: 11px;
            line-height: 1.55;
        }

        /* =====================================================
           FOOTER
        ====================================================== */

        .footer-social {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
        }

        .footer-social:hover {
            background: rgba(255,255,255,0.20);
        }

        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (min-width: 1280px) {

            .tracking-title {
                font-size: 4rem;
            }
        }

        @media (max-width: 1023px) {

            .tracking-title {
                font-size: 3.45rem;
            }
        }

        @media (max-width: 767px) {

            .tracking-hero-content {
                min-height: auto;
                padding-top: 52px;
                padding-bottom: 55px;
            }

            .tracking-title {
                font-size: 3rem;
            }

            .tracking-description {
                font-size: 15px;
                line-height: 1.7;
            }

            .tracking-card {
                padding: 8px;
                border-radius: 15px;
            }

            .tracking-search-row {
                display: flex;
                flex-direction: column;
                gap: 7px;
            }

            .tracking-input-wrapper {
                min-height: 52px;
            }

            .tracking-search-button {
                width: 100%;
                min-height: 50px;
            }

            .scan-options {
                grid-template-columns: 1fr;
            }

            .tracking-badge {
                transform: scale(.88);
                transform-origin: left top;
            }

            .tracking-status {
                transform: scale(.88);
                transform-origin: right bottom;
            }

            .steps-line {
                display: none;
            }

            .camera-header {
                padding: 14px 15px;
            }

            .camera-preview-area {
                padding: 5px 10px;
            }

            .camera-preview-wrapper {
                border-radius: 12px;
            }

            #qr-reader,
            #qr-reader video {
                max-height: 68dvh;
                min-height: 230px;
            }

            .camera-bottom {
                padding: 10px 15px 22px;
            }
        }

        @media (max-width: 480px) {

            .tracking-title {
                font-size: 2.65rem;
            }

            .tracking-hero-content {
                padding-top: 42px;
                padding-bottom: 45px;
            }

            .benefit-text {
                font-size: 10px;
            }

            .benefit-text small {
                font-size: 8px;
            }

            .camera-header-title {
                font-size: 15px;
            }

            .camera-header-subtitle {
                font-size: 10px;
            }
        }
    </style>
</head>


<body class="antialiased bg-white">


{{-- =========================================================
     NAVBAR
========================================================= --}}

<x-public-navbar />



{{-- =========================================================
     HERO
========================================================= --}}

<section
    id="rastreo"
    class="tracking-hero"
>

    {{-- Fondo skyline --}}
    <div class="tracking-hero-bg">

        <img
            src="{{ asset('images/skyline-hero.png') }}"
            alt=""
            class="absolute inset-0 w-full h-full object-cover object-right opacity-60 grayscale"
        >

    </div>


    <div
        class="tracking-hero-content max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-8 lg:gap-10 items-center"
    >


        {{-- =================================================
             TEXTO + RASTREO
        ================================================== --}}

        <div class="relative z-20 max-w-2xl">

            <div
                class="inline-flex items-center gap-2 mb-5 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-900 text-[10px] font-bold uppercase tracking-[0.14em]"
            >

                <span
                    class="w-1.5 h-1.5 rounded-full bg-amber-400"
                ></span>

                Seguimiento de envíos

            </div>


            <h1 class="tracking-title font-extrabold text-[#111111]">

                Rastrea tu

                <span class="relative inline-block">
                    <span class="absolute inset-x-0 bottom-1 h-[0.32em] bg-amber-400 -z-10"></span>
                    envío.
                </span>

            </h1>


            <p
                class="tracking-description mt-6 text-gray-600 text-base md:text-lg leading-7 font-medium"
            >
                Consulta el estado de tu paquete de forma rápida y sencilla.
                Ingresa tu número de guía o escanea el código QR de la guía
                con la cámara de tu teléfono.
            </p>


            {{-- =================================================
                 SEARCH CARD
            ================================================== --}}

            <form
                id="tracking-form"
                class="tracking-card mt-7"
                method="GET"
                action="{{ route('tracking.show') }}"
            >

                <div class="tracking-search-row flex items-center">

                    <div class="tracking-input-wrapper">

                        <i
                            class="fa-solid fa-magnifying-glass text-gray-400 text-sm shrink-0"
                        ></i>

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


                    <button
                        type="submit"
                        class="tracking-search-button"
                    >
                        <i class="fa-solid fa-location-crosshairs mr-2 text-xs"></i>
                        Rastrear
                    </button>

                </div>


                {{-- =================================================
                     OCR / CÁMARA
                ================================================== --}}

                <div class="scan-options">

                    {{-- LECTOR QR EN TIEMPO REAL --}}
                    <button
                        id="open-camera"
                        type="button"
                        class="scan-button"
                        aria-label="Abrir la cámara para escanear el código QR de la guía"
                    >

                        <span class="scan-icon">
                            <i class="fa-solid fa-qrcode"></i>
                        </span>

                        <span>

                            <span class="scan-title">
                                Escanear QR
                            </span>

                            <span class="scan-subtitle">
                                Lectura automática con la cámara
                            </span>

                        </span>

                    </button>


                    {{-- SUBIR FOTO --}}
                    <label
                        class="scan-button photo"
                        for="tracking-photo"
                    >

                        <span class="scan-icon">
                            <i class="fa-regular fa-image"></i>
                        </span>

                        <span>

                            <span class="scan-title">
                                Subir una foto
                            </span>

                            <span class="scan-subtitle">
                                Elegir desde la galería
                            </span>

                        </span>

                    </label>


                    <input
                        id="tracking-photo"
                        class="file-input"
                        type="file"
                        accept="image/*"
                    >

                </div>


                {{-- OCR STATUS --}}
                <div
                    id="ocr-status"
                    class="ocr-status"
                    role="status"
                    aria-live="polite"
                ></div>

            </form>


            {{-- FORMATO --}}
            <div class="format-row">

                <span class="format-label">
                    Formato de guía:
                </span>

                <span class="format-chip">
                    VEN-20260904-000123
                </span>

            </div>

        </div>


    </div>


    {{-- =====================================================
         IMAGEN HERO (mitad derecha en escritorio, bajo el
         contenido en tablet/móvil)
    ====================================================== --}}

    <div class="tracking-visual">

        {{-- BADGE --}}
        <div class="tracking-badge">

            <div class="tracking-badge-icon">

                <i class="fa-solid fa-route text-xs"></i>

            </div>

            <div>

                <strong>
                    Seguimiento activo
                </strong>

                <span>
                    Consulta tu envío en tiempo real
                </span>

            </div>

        </div>


        <img
            src="{{ asset('images/van-hero1.png') }}"
            alt=""
            width="1672"
            height="940"
            class="tracking-van"
        >


        {{-- STATUS --}}
        <div class="tracking-status">

            <span class="tracking-status-dot"></span>

            Rastreo disponible

        </div>

    </div>

</section>



{{-- =========================================================
     BENEFICIOS
========================================================= --}}

<section class="benefits-section">

    <div class="max-w-7xl mx-auto px-6 py-6 grid grid-cols-2 md:grid-cols-4 gap-6">

        <div class="benefit-item">

            <div class="benefit-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>

            <div class="benefit-text">

                Envíos seguros

                <small>
                    Seguimiento de tu paquete
                </small>

            </div>

        </div>


        <div class="benefit-item">

            <div class="benefit-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>

            <div class="benefit-text">

                Cobertura nacional

                <small>
                    Principales ciudades
                </small>

            </div>

        </div>


        <div class="benefit-item">

            <div class="benefit-icon">
                <i class="fa-solid fa-handshake"></i>
            </div>

            <div class="benefit-text">

                Agencias aliadas

                <small>
                    Red de atención
                </small>

            </div>

        </div>


        <div class="benefit-item">

            <div class="benefit-icon">
                <i class="fa-solid fa-headset"></i>
            </div>

            <div class="benefit-text">

                Atención al cliente

                <small>
                    Estamos para ayudarte
                </small>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
     CÓMO FUNCIONA
========================================================= --}}

<section
    id="como-funciona"
    class="bg-white"
>

    <div class="max-w-7xl mx-auto px-6 py-20">

        <div class="text-center mb-16">

            <h2
                class="text-3xl font-extrabold text-blue-950 inline-block relative pb-3"
            >

                Sigue tu paquete en pocos pasos

                <span
                    class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-amber-400 rounded-full"
                ></span>

            </h2>


            <p class="mt-4 text-sm text-gray-500">
                Consulta el recorrido de tu envío utilizando tu número de guía.
            </p>

        </div>


        <div class="grid grid-cols-2 md:grid-cols-4 gap-10 relative">

            <div class="steps-line"></div>


            {{-- PASO 1 --}}
            <div class="relative z-10 text-center">

                <div class="step-icon">

                    <i class="fa-solid fa-receipt text-blue-950 text-2xl"></i>

                    <span class="step-number">
                        1
                    </span>

                </div>

                <h3 class="step-title">
                    Obtén tu guía
                </h3>

                <p class="step-description">
                    Encuentra el número de guía en tu comprobante de envío.
                </p>

            </div>


            {{-- PASO 2 --}}
            <div class="relative z-10 text-center">

                <div class="step-icon">

                    <i class="fa-solid fa-barcode text-blue-950 text-2xl"></i>

                    <span class="step-number">
                        2
                    </span>

                </div>

                <h3 class="step-title">
                    Escríbela o escanéala
                </h3>

                <p class="step-description">
                    Ingresa la guía manualmente o utiliza la cámara.
                </p>

            </div>


            {{-- PASO 3 --}}
            <div class="relative z-10 text-center">

                <div class="step-icon">

                    <i class="fa-solid fa-location-crosshairs text-blue-950 text-2xl"></i>

                    <span class="step-number">
                        3
                    </span>

                </div>

                <h3 class="step-title">
                    Consulta el estado
                </h3>

                <p class="step-description">
                    Revisa la última actualización registrada de tu paquete.
                </p>

            </div>


            {{-- PASO 4 --}}
            <div class="relative z-10 text-center">

                <div class="step-icon bg-amber-400">

                    <i class="fa-solid fa-box-open text-blue-950 text-2xl"></i>

                    <span class="step-number">
                        4
                    </span>

                </div>

                <h3 class="step-title">
                    Recibe tu paquete
                </h3>

                <p class="step-description">
                    Sigue las actualizaciones hasta completar la entrega.
                </p>

            </div>

        </div>


        <div class="mt-12 text-center">

            <a
                href="{{ route('public.calculator') }}"
                class="bg-blue-950 hover:bg-blue-900 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition inline-flex items-center justify-center"
            >

                Calcula un nuevo envío

                <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>

            </a>

        </div>

    </div>

</section>



{{-- =========================================================
     CTA
========================================================= --}}

<section class="bg-gray-50 border-t border-gray-100">

    <div class="max-w-7xl mx-auto px-6 py-14">

        <div
            class="rounded-2xl bg-blue-950 px-6 py-10 md:px-10 md:py-12 flex flex-col md:flex-row items-center justify-between gap-8"
        >

            <div>

                <p
                    class="text-amber-400 text-[11px] font-bold uppercase tracking-[0.15em]"
                >
                    ¿Necesitas enviar un paquete?
                </p>

                <h2
                    class="mt-2 text-2xl md:text-3xl font-extrabold text-white"
                >
                    Calcula tu envío con Venexpress.
                </h2>

                <p class="mt-2 text-sm text-blue-200 max-w-xl">
                    Consulta el precio estimado y encuentra una agencia
                    cercana para entregar tu paquete.
                </p>

            </div>


            <div class="flex flex-wrap items-center gap-3 shrink-0">

                <a
                    href="{{ route('public.calculator') }}"
                    class="bg-amber-400 hover:bg-amber-500 text-blue-950 font-semibold text-sm px-5 py-3 rounded-lg transition inline-flex items-center justify-center"
                >

                    Calcular precio

                    <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>

                </a>


                <a
                    href="{{ route('public.offices') }}"
                    class="bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-sm px-5 py-3 rounded-lg transition inline-flex items-center justify-center"
                >

                    <i class="fa-solid fa-location-dot mr-2 text-amber-400"></i>

                    Agencias

                </a>

            </div>

        </div>

    </div>

</section>



{{-- =========================================================
     FOOTER
========================================================= --}}

<footer
    id="ayuda"
    class="bg-blue-950"
>

    <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-5 gap-10">

        {{-- MARCA --}}
        <div>

            <img
                src="{{ asset('images/venexpress-logo-white.png') }}"
                alt="Venexpress"
                class="h-8 mb-4"
            >

            <p class="text-sm text-blue-200">
                Conectamos a Venezuela con soluciones de envío rápidas,
                seguras y confiables.
            </p>


            <div class="flex items-center gap-3 mt-5">

                <a
                    href="#"
                    class="footer-social"
                    aria-label="Facebook"
                >
                    <i class="fa-brands fa-facebook-f text-white text-xs"></i>
                </a>

                <a
                    href="#"
                    class="footer-social"
                    aria-label="Instagram"
                >
                    <i class="fa-brands fa-instagram text-white text-xs"></i>
                </a>

                <a
                    href="#"
                    class="footer-social"
                    aria-label="X"
                >
                    <i class="fa-brands fa-x-twitter text-white text-xs"></i>
                </a>

                <a
                    href="#"
                    class="footer-social"
                    aria-label="WhatsApp"
                >
                    <i class="fa-brands fa-whatsapp text-white text-xs"></i>
                </a>

            </div>

        </div>


        {{-- ENLACES --}}
        <div>

            <h4 class="text-white font-semibold text-sm mb-4">
                Enlaces rápidos
            </h4>

            <ul class="space-y-2 text-sm text-blue-200">

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


        {{-- SERVICIOS --}}
        <div>

            <h4 class="text-white font-semibold text-sm mb-4">
                Servicios
            </h4>

            <ul class="space-y-2 text-sm text-blue-200">

                <li>
                    <a
                        href="{{ route('public.calculator') }}"
                        class="hover:text-white transition"
                    >
                        Envíos nacionales
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('public.calculator') }}"
                        class="hover:text-white transition"
                    >
                        Envíos express
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('login') }}"
                        class="hover:text-white transition"
                    >
                        Carga empresarial
                    </a>
                </li>

            </ul>

        </div>


        {{-- AYUDA --}}
        <div>

            <h4 class="text-white font-semibold text-sm mb-4">
                Ayuda
            </h4>

            <ul class="space-y-2 text-sm text-blue-200">

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
                        href="#"
                        class="hover:text-white transition"
                    >
                        Políticas
                    </a>
                </li>

                <li>
                    <a
                        href="#"
                        class="hover:text-white transition"
                    >
                        Términos y condiciones
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


        {{-- CONTACTO --}}
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

        <div
            class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-blue-300"
        >
            &copy; {{ date('Y') }} Venexpress. Todos los derechos reservados.
        </div>

    </div>

</footer>



{{-- =========================================================
     MODAL DE CÁMARA
========================================================= --}}

<div
    id="camera-modal"
    class="camera-modal"
    aria-hidden="true"
>

    <div class="camera-modal-inner">

        {{-- HEADER --}}
        <div class="camera-header">

            <div>

                <div class="camera-header-title">
                    Escanear QR
                </div>

                <div class="camera-header-subtitle">
                    Coloca el código QR de la guía dentro del recuadro
                </div>

            </div>


            <button
                id="close-camera"
                type="button"
                class="camera-close-button"
                aria-label="Cerrar cámara"
            >

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>


        {{-- PREVIEW --}}
        <div class="camera-preview-area">

            <div class="camera-preview-wrapper">

                {{-- html5-qrcode inserta aquí la vista previa de la cámara --}}
                <div id="qr-reader"></div>


                {{-- MARCO DE GUÍA --}}
                <div class="camera-guide-overlay">

                    <div class="camera-guide-box">

                        <span class="camera-guide-corner top-left"></span>
                        <span class="camera-guide-corner top-right"></span>
                        <span class="camera-guide-corner bottom-left"></span>
                        <span class="camera-guide-corner bottom-right"></span>

                        <span class="camera-scan-line"></span>

                    </div>

                </div>

            </div>

        </div>


        {{-- FOOTER CÁMARA --}}
        <div class="camera-bottom">

            <div
                id="camera-error"
                class="camera-error hidden"
                role="alert"
            ></div>


            <p class="camera-hint">
                El código se lee automáticamente. Asegúrate de que tenga buena iluminación.
            </p>

        </div>

    </div>

</div>



{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       TRACKING + OCR
    ====================================================== */

    const form = document.getElementById('tracking-form');
    const input = document.getElementById('tracking-guide');
    const photo = document.getElementById('tracking-photo');
    const status = document.getElementById('ocr-status');


    if (!form || !input || !photo || !status) {
        return;
    }


    /* =====================================================
       CAMERA ELEMENTS
    ====================================================== */

    const openCameraButton =
        document.getElementById('open-camera');

    const cameraModal =
        document.getElementById('camera-modal');

    const closeCameraButton =
        document.getElementById('close-camera');

    const cameraError =
        document.getElementById('camera-error');


    /*
     * Lector QR (html5-qrcode, la misma biblioteca que usan los
     * escáneres del repartidor y del almacén). Aquí solo se lee la
     * guía y se envía el formulario público de rastreo.
     */
    let qrScanner = null;

    let scanHandled = false;

    let lastInvalidScanAt = 0;


    /* =====================================================
       STATUS
    ====================================================== */

    function setStatus(message, type = '') {

        status.textContent = message;

        status.className =
            'ocr-status show' +
            (type ? ' ' + type : '');

    }


    /* =====================================================
       NORMALIZAR GUÍA
    ====================================================== */

    function normalizeGuide(value) {

        return String(value || '')
            .toUpperCase()
            .replace(/[|]/g, 'I')
            .replace(/\s+/g, '-')
            .replace(/--+/g, '-')
            .trim();

    }


    /* =====================================================
       EXTRAER GUÍA DEL OCR
    ====================================================== */

    function extractGuide(text) {

        const clean = String(text || '')
            .toUpperCase()
            .replace(/\n/g, ' ')
            .replace(/\s+/g, ' ');


        const patterns = [

            /*
             * Formato principal:
             *
             * VEN-20260904-000123
             */
            /\bVEN[-\s]?\d{8}[-\s]?\d{4,8}\b/,

            /*
             * Formato alternativo:
             *
             * VEN-2026-123456
             */
            /\bVEN[-\s]?\d{4}[-\s]?\d{5,10}\b/,

            /*
             * VE-2026-123456
             */
            /\bVE[-\s]?\d{4}[-\s]?\d{5,10}\b/,

            /*
             * VEN + números
             */
            /\bVEN[-\s]?\d{5,16}\b/,

            /*
             * VE + números
             */
            /\bVE[-\s]?\d{5,16}\b/
        ];


        for (const pattern of patterns) {

            const match = clean.match(pattern);

            if (match) {

                return normalizeGuide(match[0]);

            }

        }


        /*
         * Tolerancia para errores comunes del OCR.
         */
        const tolerant = clean.match(
            /\bV[A-Z]{1,2}[-\s]?\d{4}[-\s]?\d{5,10}\b/
        );


        if (tolerant) {

            let guide = normalizeGuide(
                tolerant[0]
            );

            guide = guide.replace(
                /^VK-/,
                'VE-'
            );

            guide = guide.replace(
                /^VFN-/,
                'VEN-'
            );

            return guide;

        }


        return null;

    }


    /* =====================================================
       PROCESAR IMAGEN CON TESSERACT
    ====================================================== */

    async function processImage(file) {

        if (!file) {
            return;
        }


        if (
            typeof Tesseract === 'undefined'
        ) {

            setStatus(
                'El lector de imágenes todavía no está disponible. Recarga la página e inténtalo nuevamente.',
                'error'
            );

            return;
        }


        setStatus(
            'Analizando la foto y buscando el número de guía...'
        );


        try {

            const result =
                await Tesseract.recognize(

                    file,

                    'eng',

                    {
                        logger: function (info) {

                            if (
                                info.status ===
                                'recognizing text'
                            ) {

                                const progress =
                                    Math.round(
                                        (info.progress || 0) * 100
                                    );

                                setStatus(
                                    'Reconociendo la guía... ' +
                                    progress +
                                    '%'
                                );

                            }

                        }
                    }

                );


            const guide =
                extractGuide(
                    result.data.text
                );


            if (!guide) {

                setStatus(
                    'No pude identificar la guía. Intenta con una foto más clara o escríbela manualmente.',
                    'error'
                );

                input.focus();

                return;

            }


            /*
             * Colocar la guía en el input.
             */
            input.value = guide;


            /*
             * Disparar eventos por compatibilidad
             * con posibles listeners externos.
             */
            input.dispatchEvent(
                new Event(
                    'input',
                    {
                        bubbles: true
                    }
                )
            );


            input.dispatchEvent(
                new Event(
                    'change',
                    {
                        bubbles: true
                    }
                )
            );


            setStatus(
                '✓ Guía detectada: ' +
                guide +
                '. Presiona "Rastrear" para consultar el envío.',
                'success'
            );


        } catch (error) {

            console.error(
                'OCR tracking error:',
                error
            );


            setStatus(
                'No se pudo leer la imagen. Puedes escribir la guía manualmente.',
                'error'
            );

        }

    }


    /* =====================================================
       EXTRAER GUÍA DEL CÓDIGO QR
    ====================================================== */

    /*
     * Las etiquetas de Venexpress codifican el número de guía tal cual
     * (PackageService::generateTrackingNumber → VEN-AAAAMMDD-NNNNNN).
     * Si algún QR trae una URL de rastreo, se toma su parámetro "guia".
     */
    function extractGuideFromQr(text) {

        let value = String(text || '').trim();

        try {

            value = new URL(value).searchParams.get('guia') || '';

        } catch (error) {
            // No es una URL: el QR contiene directamente la guía.
        }

        value = value.trim().toUpperCase();

        return /^VEN-\d{8}-\d{6}$/.test(value)
            ? value
            : null;

    }


    /* =====================================================
       ERRORES DEL LECTOR
    ====================================================== */

    function showCameraError(message) {

        cameraError.textContent = message;

        cameraError.classList.remove('hidden');

    }


    function hideCameraError() {

        cameraError.textContent = '';

        cameraError.classList.add('hidden');

    }


    function cameraErrorMessage(error) {

        const detail =
            String((error && error.name) || '') +
            ' ' +
            String(error || '');

        if (/NotAllowedError|Permission/i.test(detail)) {
            return 'El acceso a la cámara fue bloqueado. Permite el uso de la cámara en tu navegador e inténtalo nuevamente.';
        }

        if (/NotFoundError|DevicesNotFound|Requested device not found/i.test(detail)) {
            return 'No encontramos una cámara disponible en este dispositivo.';
        }

        if (/NotReadableError|TrackStartError|Could not start video/i.test(detail)) {
            return 'La cámara está siendo utilizada por otra aplicación. Ciérrala e inténtalo nuevamente.';
        }

        if (/OverconstrainedError/i.test(detail)) {
            return 'La cámara disponible no es compatible con el lector.';
        }

        if (/SecurityError/i.test(detail)) {
            return 'El navegador bloqueó la cámara por motivos de seguridad.';
        }

        return 'No se pudo iniciar la cámara. Puedes escribir la guía manualmente.';

    }


    /* =====================================================
       ABRIR LECTOR QR
    ====================================================== */

    async function openCamera() {

        if (qrScanner) {
            return;
        }

        if (
            !window.isSecureContext ||
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            setStatus(
                'Tu navegador no permite acceder a la cámara en esta página. Escribe la guía manualmente.',
                'error'
            );

            return;
        }

        if (typeof Html5Qrcode === 'undefined') {

            setStatus(
                'El lector QR todavía no está disponible. Recarga la página e inténtalo nuevamente.',
                'error'
            );

            return;
        }


        hideCameraError();

        scanHandled = false;


        /*
         * El visor se muestra antes de arrancar para que el lector
         * tenga dimensiones y para que los errores sean visibles.
         */
        cameraModal.classList.add('is-open');

        cameraModal.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';


        const config = { verbose: false };

        if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
            config.formatsToSupport = [
                Html5QrcodeSupportedFormats.QR_CODE
            ];
        }

        const instance =
            new Html5Qrcode('qr-reader', config);

        qrScanner = instance;


        try {

            await instance.start(
                { facingMode: 'environment' },
                { fps: 10 },
                onQrDecoded,
                function () {}
            );

            /*
             * Si el visor se cerró mientras la cámara arrancaba,
             * se libera ahora para no dejarla encendida.
             */
            if (qrScanner !== instance) {
                await releaseScanner(instance);
            }

        } catch (error) {

            console.error('QR scanner error:', error);

            if (qrScanner === instance) {
                qrScanner = null;
            }

            await releaseScanner(instance);

            showCameraError(
                cameraErrorMessage(error)
            );

        }

    }


    /* =====================================================
       LECTURA DEL QR
    ====================================================== */

    async function onQrDecoded(decodedText) {

        if (scanHandled) {
            return;
        }

        const guide =
            extractGuideFromQr(decodedText);

        if (!guide) {

            /*
             * El lector sigue activo; el aviso se limita para no
             * repetirse en cada fotograma.
             */
            const now = Date.now();

            if (now - lastInvalidScanAt > 2500) {

                lastInvalidScanAt = now;

                showCameraError(
                    'Este código QR no corresponde a una guía de Venexpress.'
                );

            }

            return;
        }


        /*
         * Una sola lectura válida: se bloquean las siguientes, se
         * libera la cámara y se usa el formulario de rastreo existente.
         */
        scanHandled = true;

        await closeCamera();

        input.value = guide;

        input.dispatchEvent(
            new Event('input', { bubbles: true })
        );

        setStatus(
            '✓ Guía detectada: ' + guide + '. Consultando el envío...',
            'success'
        );

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }

    }


    /* =====================================================
       DETENER LECTOR
    ====================================================== */

    async function releaseScanner(instance) {

        if (!instance) {
            return;
        }

        try {

            if (instance.isScanning) {
                await instance.stop();
            }

        } catch (error) {
            console.warn('No se pudo detener el lector QR:', error);
        }

        try {
            instance.clear();
        } catch (error) {
            console.warn('No se pudo limpiar el lector QR:', error);
        }

    }


    function stopCamera() {

        const instance = qrScanner;

        qrScanner = null;

        return releaseScanner(instance);

    }


    /* =====================================================
       CERRAR VISOR
    ====================================================== */

    async function closeCamera() {

        cameraModal.classList.remove('is-open');

        cameraModal.setAttribute('aria-hidden', 'true');

        document.body.style.overflow = '';

        await stopCamera();

    }


    /* =====================================================
       BOTÓN ABRIR CÁMARA
    ====================================================== */

    if (openCameraButton) {

        openCameraButton.addEventListener(
            'click',
            openCamera
        );

    }


    /* =====================================================
       BOTÓN CERRAR CÁMARA
    ====================================================== */

    if (closeCameraButton) {

        closeCameraButton.addEventListener(
            'click',
            closeCamera
        );

    }


    /* =====================================================
       CERRAR AL HACER CLICK FUERA
    ====================================================== */

    if (cameraModal) {

        cameraModal.addEventListener(
            'click',
            function (event) {

                if (
                    event.target ===
                    cameraModal
                ) {

                    closeCamera();

                }

            }
        );

    }


    /* =====================================================
       CERRAR CON ESCAPE
    ====================================================== */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                cameraModal.classList.contains(
                    'is-open'
                )
            ) {

                closeCamera();

            }

        }
    );


    /* =====================================================
       SUBIR FOTO
    ====================================================== */

    photo.addEventListener(
        'change',
        function () {

            const file =
                this.files[0];


            if (file) {

                processImage(file);

            }


            /*
             * Permite volver a seleccionar
             * la misma imagen posteriormente.
             */
            this.value = '';

        }
    );


    /* =====================================================
       FORM SUBMIT
    ====================================================== */

    form.addEventListener(
        'submit',
        function (event) {

            if (
                !input.value.trim()
            ) {

                event.preventDefault();


                setStatus(
                    'Escribe o escanea un número de guía para continuar.',
                    'error'
                );


                input.focus();

            }

        }
    );


    /* =====================================================
       LIBERAR CÁMARA AL SALIR
    ====================================================== */

    /*
     * pagehide también cubre la caché de retroceso (bfcache): al volver
     * desde el resultado, el visor no reaparece abierto.
     */
    window.addEventListener(
        'pagehide',
        function () {

            closeCamera();

        }
    );


    /* =====================================================
       LIBERAR CÁMARA SI LA PÁGINA PASA A BACKGROUND
       EN ALGUNOS NAVEGADORES MÓVILES
    ====================================================== */

    document.addEventListener(
        'visibilitychange',
        function () {

            if (
                document.hidden &&
                qrScanner
            ) {

                closeCamera();

            }

        }
    );

});

</script>


{{-- =========================================================
     TESSERACT OCR
========================================================= --}}

<script
    src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"
></script>


{{-- =========================================================
     LECTOR QR (misma biblioteca que los escáneres del repartidor
     y del almacén, con versión fija)
========================================================= --}}

<script
    src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"
></script>

</body>
</html>