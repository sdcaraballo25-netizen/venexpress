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
           NAVBAR
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
            color: #172554;
            font-size: 14px;
            font-weight: 500;
        }

        .tracking-input::placeholder {
            color: #94a3b8;
        }

        .tracking-search-button {
            min-height: 54px;
            min-width: 128px;
            border: 0;
            border-radius: 11px;
            background: #172554;
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
            background: #1e3a8a;
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
            border: 1px solid #e2e8f0;
            background: #f8fafc;
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
            border-color: #bfdbfe;
            background: #eff6ff;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(15, 23, 42, 0.05);
        }

        .scan-button:focus-visible {
            outline: 3px solid rgba(59, 130, 246, 0.25);
            outline-offset: 2px;
        }

        .scan-button.photo:hover {
            border-color: #fde68a;
            background: #fffbeb;
        }

        .scan-icon {
            width: 40px;
            height: 40px;
            flex: 0 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #172554;
            color: #ffffff;
        }

        .scan-button.photo .scan-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .scan-title {
            display: block;
            color: #172554;
            font-size: 12px;
            font-weight: 700;
        }

        .scan-subtitle {
            display: block;
            color: #64748b;
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
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
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

        #camera-video {
            display: block;
            width: 100%;
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
            width: 86%;
            max-width: 620px;
            height: 125px;
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
            top: 50%;
            height: 2px;
            border-radius: 999px;
            background: rgba(255,255,255,0.75);
            box-shadow: 0 0 12px rgba(255,255,255,0.6);
            animation: cameraScanLine 2s ease-in-out infinite;
        }

        @keyframes cameraScanLine {
            0%,
            100% {
                transform: translateY(-48px);
                opacity: 0.55;
            }

            50% {
                transform: translateY(48px);
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

        .camera-action-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .camera-take-button {
            min-height: 58px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 0;
            border-radius: 999px;
            background: #ffffff;
            color: #172554;
            padding: 7px 22px 7px 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 35px rgba(0,0,0,0.28);
            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .camera-take-button:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }

        .camera-take-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #172554;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: #94a3b8;
            font-size: 10px;
            font-weight: 600;
        }

        .format-chip {
            display: inline-flex;
            align-items: center;
            min-height: 27px;
            padding: 4px 9px;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            background: #ffffff;
            color: #1e3a8a;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        /* =====================================================
           HERO VISUAL
        ====================================================== */

        .tracking-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .tracking-visual-card {
            position: relative;
            width: 100%;
            max-width: 560px;
            min-height: 390px;
        }

        .tracking-circle {
            position: absolute;
            width: 430px;
            height: 430px;
            right: 3%;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 50%;
            background: rgba(239, 246, 255, 0.9);
        }

        .tracking-circle::before {
            content: '';
            position: absolute;
            inset: 38px;
            border-radius: 50%;
            border: 2px dashed rgba(30, 64, 175, 0.16);
        }

        .tracking-van {
            position: absolute;
            width: 100%;
            max-width: 550px;
            right: -3%;
            bottom: 25px;
            z-index: 5;
            transform: translateY(25px) scale(1.04);
            filter: drop-shadow(
                0 30px 25px rgba(15, 23, 55, 0.22)
            );
        }

        .tracking-shadow {
            position: absolute;
            right: 7%;
            bottom: 18px;
            width: 70%;
            height: 22px;
            border-radius: 999px;
            background: rgba(15, 23, 55, 0.18);
            filter: blur(9px);
            z-index: 2;
        }

        .tracking-badge {
            position: absolute;
            left: 5%;
            top: 16%;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            background: rgba(255,255,255,0.95);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
        }

        .tracking-badge-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #eff6ff;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tracking-badge strong {
            display: block;
            color: #172554;
            font-size: 11px;
            line-height: 1.3;
        }

        .tracking-badge span {
            display: block;
            margin-top: 2px;
            color: #64748b;
            font-size: 9px;
        }

        .tracking-status {
            position: absolute;
            right: 2%;
            bottom: 17%;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            background: rgba(255,255,255,0.96);
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
            color: #334155;
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
            background: #172554;
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
            color: #fbbf24;
        }

        .benefit-text {
            color: #ffffff;
            font-size: 12px;
            line-height: 1.45;
        }

        .benefit-text small {
            display: block;
            margin-top: 2px;
            color: #bfdbfe;
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
            background: #f1f5f9;
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
            background: #172554;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
        }

        .step-title {
            margin-top: 15px;
            color: #172554;
            font-size: 14px;
            font-weight: 600;
        }

        .step-description {
            margin-top: 3px;
            color: #64748b;
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

            .tracking-van {
                transform: translateY(30px) scale(1.08);
            }
        }

        @media (max-width: 1023px) {

            .main-nav-links {
                gap: 1.35rem;
            }

            .tracking-title {
                font-size: 3.45rem;
            }

            .tracking-visual-card {
                min-height: 330px;
            }

            .tracking-circle {
                width: 350px;
                height: 350px;
            }

            .tracking-van {
                transform: translateY(15px) scale(1.02);
            }
        }

        @media (max-width: 767px) {

            .main-nav-links {
                display: none;
            }

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

            .tracking-visual {
                margin-top: 25px;
            }

            .tracking-visual-card {
                min-height: 290px;
            }

            .tracking-circle {
                width: 290px;
                height: 290px;
                right: 50%;
                transform: translate(50%, -50%);
            }

            .tracking-van {
                width: 100%;
                right: 0;
                bottom: 12px;
                transform: none;
            }

            .tracking-badge {
                left: 0;
                top: 8%;
                transform: scale(.88);
                transform-origin: left top;
            }

            .tracking-status {
                right: 0;
                bottom: 8%;
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

            #camera-video {
                max-height: 68dvh;
                min-height: 230px;
            }

            .camera-guide-box {
                height: 105px;
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

            .tracking-visual-card {
                min-height: 245px;
            }

            .tracking-circle {
                width: 235px;
                height: 235px;
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

            .camera-guide-box {
                height: 90px;
            }
        }
    </style>
</head>


<body class="antialiased bg-white">


{{-- =========================================================
     NAVBAR
========================================================= --}}

<nav
    id="main-navbar"
    class="bg-white border-b border-gray-100 sticky top-0 z-50"
>

    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        {{-- LOGO --}}
        <a
            href="{{ route('home') }}"
            class="shrink-0"
            aria-label="Venexpress - Inicio"
        >
            <img
                src="{{ asset('images/venexpress-logo.png') }}"
                alt="Venexpress"
                class="h-9 w-auto"
            >
        </a>


        {{-- NAV DESKTOP --}}
        <div class="main-nav-links">

            <a
                href="{{ route('home') }}"
                class="main-nav-link"
            >
                Inicio
            </a>

            <a
                href="{{ route('home') }}#servicios"
                class="main-nav-link"
            >
                Servicios
            </a>

            <a
                href="{{ route('public.calculator') }}"
                class="main-nav-link"
            >
                Calcular precio
            </a>

            <a
                href="{{ route('public.offices') }}"
                class="main-nav-link"
            >
                Agencias aliadas
            </a>

            <a
                href="{{ route('tracking.index') }}"
                class="main-nav-link is-active"
            >
                Rastreo
            </a>

            <a
                href="{{ route('home') }}#ayuda"
                class="main-nav-link"
            >
                Ayuda
            </a>

        </div>


        {{-- LOGIN + MOBILE --}}
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
                <i
                    id="mobile-menu-icon"
                    class="fa-solid fa-bars"
                ></i>
            </button>

        </div>

    </div>


    {{-- MENÚ MÓVIL --}}
    <div
        id="mobile-menu"
        class="hidden border-t border-gray-100 bg-white md:hidden"
    >

        <div class="max-w-7xl mx-auto px-6 py-3">

            <a
                href="{{ route('home') }}"
                class="mobile-menu-link block py-3 text-sm text-gray-600"
            >
                Inicio
            </a>

            <a
                href="{{ route('home') }}#servicios"
                class="mobile-menu-link block py-3 text-sm text-gray-600"
            >
                Servicios
            </a>

            <a
                href="{{ route('public.calculator') }}"
                class="mobile-menu-link block py-3 text-sm text-gray-600"
            >
                Calcular precio
            </a>

            <a
                href="{{ route('public.offices') }}"
                class="mobile-menu-link block py-3 text-sm text-gray-600"
            >
                Agencias aliadas
            </a>

            <a
                href="{{ route('tracking.index') }}"
                class="mobile-menu-link block py-3 text-sm font-semibold text-blue-950"
            >
                Rastreo
            </a>

            <a
                href="{{ route('home') }}#ayuda"
                class="mobile-menu-link block py-3 text-sm text-gray-600"
            >
                Ayuda
            </a>

        </div>

    </div>

</nav>



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
            class="absolute inset-0 w-full h-full object-cover object-right opacity-70"
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


            <h1 class="tracking-title font-extrabold text-blue-950">

                Rastrea tu

                <span class="block text-red-600">
                    envío.
                </span>

            </h1>


            <p
                class="tracking-description mt-6 text-gray-600 text-base md:text-lg leading-7 font-medium"
            >
                Consulta el estado de tu paquete de forma rápida y sencilla.
                Ingresa tu número de guía o utiliza la cámara de tu teléfono
                para leerlo automáticamente.
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

                    {{-- CÁMARA REAL --}}
                    <button
                        id="open-camera"
                        type="button"
                        class="scan-button"
                        aria-label="Abrir cámara para fotografiar la guía"
                    >

                        <span class="scan-icon">
                            <i class="fa-solid fa-camera"></i>
                        </span>

                        <span>

                            <span class="scan-title">
                                Usar cámara
                            </span>

                            <span class="scan-subtitle">
                                Fotografiar la guía
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



        {{-- =================================================
             VEHÍCULO
        ================================================== --}}

        <div class="tracking-visual">

            <div class="tracking-visual-card">

                <div class="tracking-circle"></div>


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


                {{-- CAMIONETA --}}
                <img
                    src="{{ asset('images/van-hero.png') }}"
                    alt="Furgoneta Venexpress"
                    class="tracking-van"
                >


                {{-- SOMBRA --}}
                <div class="tracking-shadow"></div>


                {{-- STATUS --}}
                <div class="tracking-status">

                    <span class="tracking-status-dot"></span>

                    Rastreo disponible

                </div>

            </div>

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
                    class="absolute left-1/2 -translate-x-1/2 bottom-0 w-14 h-1 bg-red-600 rounded-full"
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
                    Escanear guía
                </div>

                <div class="camera-header-subtitle">
                    Coloca el número de guía dentro del recuadro
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

                <video
                    id="camera-video"
                    autoplay
                    playsinline
                    muted
                ></video>


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
            ></div>


            <div class="camera-action-row">

                <button
                    id="take-photo"
                    type="button"
                    class="camera-take-button"
                >

                    <span class="camera-take-icon">
                        <i class="fa-solid fa-camera"></i>
                    </span>

                    Tomar foto

                </button>

            </div>


            <p class="camera-hint">
                Asegúrate de que la guía tenga buena iluminación y esté enfocada.
            </p>

        </div>

    </div>

</div>


{{-- CANVAS OCULTO PARA CAPTURAR LA FOTO --}}
<canvas
    id="camera-canvas"
    class="hidden"
></canvas>



{{-- =========================================================
     JAVASCRIPT
========================================================= --}}

<script>

document.addEventListener('DOMContentLoaded', function () {

    /* =====================================================
       MENÚ MÓVIL
    ====================================================== */

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

            const isOpen = !menu.classList.contains('hidden');

            if (isOpen) {

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

    const takePhotoButton =
        document.getElementById('take-photo');

    const cameraVideo =
        document.getElementById('camera-video');

    const cameraCanvas =
        document.getElementById('camera-canvas');

    const cameraError =
        document.getElementById('camera-error');


    let cameraStream = null;


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
       ABRIR CÁMARA REAL
    ====================================================== */

    async function openCamera() {

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            setStatus(
                'Tu navegador no permite acceder a la cámara. Puedes subir una foto de la guía.',
                'error'
            );

            return;
        }


        cameraError.classList.add('hidden');
        cameraError.textContent = '';


        try {

            /*
             * Si existía una cámara anterior,
             * la cerramos primero.
             */
            stopCamera();


            /*
             * Solicitar cámara trasera.
             *
             * "ideal" permite que el navegador
             * elija otra cámara si esta no existe.
             */
            cameraStream =
                await navigator.mediaDevices.getUserMedia({

                    video: {

                        facingMode: {
                            ideal: 'environment'
                        },

                        width: {
                            ideal: 1920
                        },

                        height: {
                            ideal: 1080
                        }

                    },

                    audio: false

                });


            /*
             * Conectar stream al video.
             */
            cameraVideo.srcObject =
                cameraStream;


            /*
             * Mostrar modal.
             */
            cameraModal.classList.add(
                'is-open'
            );

            cameraModal.setAttribute(
                'aria-hidden',
                'false'
            );


            /*
             * Evitar scroll detrás del modal.
             */
            document.body.style.overflow =
                'hidden';


            /*
             * Iniciar reproducción.
             */
            await cameraVideo.play();


        } catch (error) {

            console.error(
                'Camera error:',
                error
            );


            stopCamera();


            let message =
                'No se pudo acceder a la cámara.';


            if (
                error.name ===
                'NotAllowedError'
            ) {

                message =
                    'El acceso a la cámara fue bloqueado. Permite el uso de la cámara en tu navegador e inténtalo nuevamente.';

            } else if (
                error.name ===
                'NotFoundError'
            ) {

                message =
                    'No encontramos una cámara disponible en este dispositivo.';

            } else if (
                error.name ===
                'NotReadableError'
            ) {

                message =
                    'La cámara está siendo utilizada por otra aplicación. Cierra otras aplicaciones que estén usando la cámara e inténtalo nuevamente.';

            } else if (
                error.name ===
                'SecurityError'
            ) {

                message =
                    'El navegador bloqueó la cámara por motivos de seguridad.';

            } else if (
                error.name ===
                'OverconstrainedError'
            ) {

                message =
                    'La cámara disponible no es compatible con la configuración solicitada.';

            }


            cameraError.textContent =
                message;

            cameraError.classList.remove(
                'hidden'
            );

        }

    }


    /* =====================================================
       DETENER CÁMARA
    ====================================================== */

    function stopCamera() {

        if (cameraStream) {

            cameraStream
                .getTracks()
                .forEach(function (track) {

                    track.stop();

                });

            cameraStream = null;

        }


        if (cameraVideo) {

            cameraVideo.pause();

            cameraVideo.srcObject = null;

        }

    }


    /* =====================================================
       CERRAR MODAL
    ====================================================== */

    function closeCamera() {

        stopCamera();


        cameraModal.classList.remove(
            'is-open'
        );

        cameraModal.setAttribute(
            'aria-hidden',
            'true'
        );


        document.body.style.overflow =
            '';

    }


    /* =====================================================
       TOMAR FOTO
    ====================================================== */

    function takePhoto() {

        if (!cameraStream) {

            setStatus(
                'La cámara no está activa.',
                'error'
            );

            return;

        }


        const width =
            cameraVideo.videoWidth;

        const height =
            cameraVideo.videoHeight;


        if (!width || !height) {

            setStatus(
                'La cámara todavía no está lista. Espera un momento e inténtalo nuevamente.',
                'error'
            );

            return;

        }


        /*
         * Preparar canvas con la misma
         * resolución de la cámara.
         */
        cameraCanvas.width =
            width;

        cameraCanvas.height =
            height;


        const context =
            cameraCanvas.getContext(
                '2d'
            );


        if (!context) {

            setStatus(
                'No se pudo preparar la captura.',
                'error'
            );

            return;

        }


        /*
         * Dibujar el frame actual.
         */
        context.drawImage(
            cameraVideo,
            0,
            0,
            width,
            height
        );


        /*
         * Convertir la captura en JPEG.
         */
        cameraCanvas.toBlob(
            function (blob) {

                if (!blob) {

                    setStatus(
                        'No se pudo capturar la imagen.',
                        'error'
                    );

                    return;

                }


                /*
                 * Crear un File real.
                 */
                const file =
                    new File(

                        [blob],

                        'guia-camera.jpg',

                        {
                            type:
                                'image/jpeg',
                            lastModified:
                                Date.now()
                        }

                    );


                /*
                 * Cerramos cámara
                 * antes del OCR.
                 */
                closeCamera();


                /*
                 * Procesamos la imagen
                 * exactamente igual que
                 * una foto subida.
                 */
                processImage(file);

            },

            'image/jpeg',

            0.92

        );

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
       BOTÓN TOMAR FOTO
    ====================================================== */

    if (takePhotoButton) {

        takePhotoButton.addEventListener(
            'click',
            takePhoto
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

    window.addEventListener(
        'beforeunload',
        function () {

            stopCamera();

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
                cameraStream
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

</body>
</html>