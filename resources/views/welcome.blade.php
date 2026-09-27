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
        #cobertura,
        #aliados,
        #como-funciona,
        #empresas,
        #ayuda {
            scroll-margin-top: 92px;
        }

        /* =========================================================
           NAVBAR
        ========================================================== */

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
            gap: 1.1rem;
        }

        .main-nav-link {
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

        .main-nav-link:hover {
            color: var(--ink);
            transform: translateY(-1px);
        }

        .main-nav-link.is-active {
            color: var(--ink);
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

        /* =========================================================
           HERO
        ========================================================== */

        .hero-shell {
            height: 510px;
            min-height: 510px;
            max-height: 510px;
        }

        /* Pista horizontal: los 4 slides conviven lado a lado y solo
           cambia el translateX, así el arrastre puede seguir al dedo
           y la altura nunca salta entre slides. */
        .hero-track {
            display: flex;
            height: 100%;
            transition: transform 0.5s cubic-bezier(.22,.61,.36,1);
            cursor: grab;
            touch-action: pan-y;
            user-select: none;
            -webkit-user-select: none;
        }

        .hero-shell.is-dragging .hero-track {
            transition: none;
            cursor: grabbing;
        }

        .hero-track img {
            -webkit-user-drag: none;
        }

        .hero-slide {
            flex: 0 0 100%;
            min-width: 0;
            height: 510px;
            min-height: 510px;
            max-height: 510px;
            overflow: hidden;
        }

        .hero-image-panel {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .hero-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
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

        .hero-role-button {
            padding: 0.95rem 1.4rem;
            font-size: 0.86rem;
        }

        /* Flechas: solo un chevron sobre los laterales. Sin círculo,
           fondo ni borde; el área clicable es generosa pero invisible.
           El halo blanco lo mantiene legible sobre la foto. */
        .hero-arrow {
            position: absolute;
            top: 50%;
            z-index: 10;
            width: 2.75rem;
            height: 4rem;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            color: var(--ink);
            font-size: 1.35rem;
            opacity: 0.7;
            filter: drop-shadow(0 0 3px rgba(255,255,255,.95));
            transition: opacity 0.2s ease;
        }

        .hero-arrow:hover,
        .hero-arrow:focus-visible {
            opacity: 1;
        }

        .hero-arrow:focus-visible {
            outline: 2px solid var(--ink);
            outline-offset: -4px;
            border-radius: 0.5rem;
        }

        .hero-arrow-prev { left: 0.25rem; }
        .hero-arrow-next { right: 0.25rem; }

        @media (min-width: 768px) {
            /* deja libre el hueco de las flechas junto al texto */
            .hero-content.section-wrap {
                padding-left: max(clamp(1rem, 4vw, 4.5rem), 3rem);
                padding-right: 3rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .hero-track {
                transition: none;
            }
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

        /* =========================================================
           FRANJA DE VALOR
        ========================================================== */

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

        /* =========================================================
           SECCIONES
        ========================================================== */

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

        /* =========================================================
           SERVICIOS
        ========================================================== */

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

        /* =========================================================
           COBERTURA
        ========================================================== */

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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .outline-map-image {
            width: 78%;
            height: 78%;
            object-fit: contain;
            object-position: center;
            display: block;
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

        /* =========================================================
           AGENCIAS
        ========================================================== */

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

        /* =========================================================
           MARKETPLACE (vista previa)
        ========================================================== */

        .market-grid {
            margin-top: 2rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .market-card {
            display: flex;
            flex-direction: column;
            border: 1px solid var(--line);
            border-radius: 0.85rem;
            background: #fff;
            overflow: hidden;
            transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
        }

        .market-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 35px rgba(0,0,0,.07);
            border-color: #d7d7d2;
        }

        .market-card-image {
            aspect-ratio: 1 / 1;
            background: #fafaf8;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .market-card-placeholder {
            font-size: 2.4rem;
            color: #e2e2dc;
        }

        .market-card-body {
            padding: 1rem 1.1rem 1.2rem;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .market-card-seller {
            font-size: 0.62rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #9a9a95;
        }

        .market-card-name {
            margin-top: 0.1rem;
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.3;
            color: var(--ink);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .market-card-price {
            margin-top: 0.35rem;
            font-size: 1rem;
            font-weight: 800;
            color: var(--ink);
        }

        /* =========================================================
           COMO FUNCIONA
        ========================================================== */

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

        /* =========================================================
           EMPRESAS / ALIADOS / REPARTIDORES
        ========================================================== */

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

        /* =========================================================
           HERO6 - NUEVA IMAGEN PARA EMPRESAS
        ========================================================== */

        .partner-main-image {
            position: absolute;
            width: 62%;
            height: 100%;
            right: -2%;
            top: 0;
            object-fit: contain;
            object-position: right center;
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

        /* =========================================================
           FAQ
        ========================================================== */

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

        /* =========================================================
           CTA / FOOTER
        ========================================================== */

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

        /* =========================================================
           BUSCADOR NAVBAR
        ========================================================== */

        .desktop-tracking-form {
            display: flex !important;
            align-items: center;
            margin-left: 0.4rem;
            padding-left: 0 !important;
            border-left: 0 !important;
        }

        .navbar-search {
            width: 235px;
            height: 42px;
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #d8d8d3 !important;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: none !important;
            transition: none;
        }

        .navbar-search:focus-within {
            border-color: #d8d8d3 !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .navbar-search input {
            flex: 1;
            min-width: 0;
            width: auto !important;
            height: 100%;
            border: 0 !important;
            outline: 0 !important;
            box-shadow: none !important;
            background: transparent;
            color: #111;
            font-family: inherit;
            font-size: .78rem;
            padding: 0 13px;
        }

        .navbar-search input:focus {
            border: 0 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .navbar-search input::placeholder {
            color: #9a9a95;
        }

        .navbar-search button {
            width: 44px;
            height: 100%;
            flex: 0 0 44px;
            border: 0;
            background: #111;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }

        .navbar-search button:hover {
            background: var(--brand-yellow);
            color: #111;
        }

        .navbar-search button i {
            font-size: .78rem;
        }

        /* =========================================================
           SERVICIOS
        ========================================================== */

        .services-section {
            padding-top: 3.7rem;
            padding-bottom: 4.2rem;
        }

        /* =========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 1100px) {

            .desktop-tracking-form {
                display: none !important;
            }

            .main-nav-links {
                gap: 1.2rem;
            }

            .hero-image-panel {
                left: 46%;
            }

            .quick-grid,
            .agency-grid,
            .market-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .partner-grid {
                grid-template-columns: 1fr 1fr;
            }

            .partner-main {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 1280px) {
            .hero-copy {
                padding-left: 0.2rem;
            }
        }

        @media (max-width: 900px) {

            .main-nav-links {
                display: none;
            }

            .hero-copy {
                width: 100%;
                max-width: 720px;
            }

            .hero-image {
                object-position: center;
            }

            .coverage-layout,
            .faq-layout {
                grid-template-columns: 1fr;
            }

            .partner-main-image {
                width: 58%;
                right: 0;
            }

            .partner-main-content {
                width: 60%;
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

            /* Móvil: la imagen (con el degradado blanco horneado a la
               izquierda) se recorta desde la derecha, sin deformarse, y
               el texto vive debajo sobre blanco. La altura la marca el
               slide más alto; todos se estiran a esa altura. */
            .hero-shell {
                height: auto;
                min-height: 0;
                max-height: none;
            }

            .hero-slide {
                height: auto;
                min-height: 0;
                max-height: none;
                display: flex;
                flex-direction: column;
            }

            .hero-image-panel {
                position: relative;
                inset: auto;
                left: 0;
                flex: 0 0 auto;
                height: 220px;
            }

            .hero-image {
                object-position: right center;
            }

            .hero-content {
                height: auto;
                min-height: 0;
                max-height: none;
                flex: 1 1 auto;
                align-items: flex-start;
            }

            .hero-arrow {
                top: 110px;
                width: 2.5rem;
                height: 3.5rem;
                font-size: 1.15rem;
            }

            .hero-copy {
                padding: 1.4rem 0 4rem;
            }

            .hero-title {
                font-size: clamp(2.7rem, 13vw, 4rem);
            }

            .quick-grid,
            .agency-grid,
            .market-grid,
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
                right: 0;
                object-position: right center;
            }

            .utility-item + .utility-item {
                border-left: 0;
                border-top: 1px solid rgba(255,255,255,.18);
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

            .partner-main-content {
                width: 65%;
            }

            .partner-main-image {
                width: 48%;
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

            <a href="{{ route('home') }}"
               class="shrink-0"
               aria-label="Venexpress - Inicio">

                <img src="{{ asset('images/venexpress-logo.png') }}"
                     alt="Venexpress"
                     class="h-8 sm:h-9 w-auto">

            </a>

            <div class="main-nav-links">

                <a href="{{ route('home') }}"
                   class="main-nav-link is-active">
                    Inicio
                </a>

                <a href="{{ route('public.marketplace') }}"
                   class="main-nav-link">
                    Tienda
                </a>

                <a href="#servicios"
                   class="main-nav-link">
                    Servicios
                </a>

                <a href="{{ route('public.calculator') }}"
                   class="main-nav-link">
                    Calcular precio
                </a>

                <a href="{{ route('public.offices') }}"
                   class="main-nav-link">
                    Agencias aliadas
                </a>

                <a href="{{ route('tracking.index') }}"
                   class="main-nav-link">
                    Rastreo
                </a>

                <a href="#ayuda"
                   class="main-nav-link">
                    Ayuda
                </a>

            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 ml-auto">

                {{-- BUSCADOR DESKTOP --}}

                <form action="{{ route('tracking.show') }}"
                      method="GET"
                      class="desktop-tracking-form">

                    <div class="navbar-search">

                        <input
                            type="text"
                            name="guia"
                            placeholder="Número de guía"
                            autocomplete="off"
                            spellcheck="false"
                            aria-label="Número de guía"
                        >

                        <button type="submit"
                                aria-label="Rastrear envío">

                            <i class="fa-solid fa-magnifying-glass"></i>

                        </button>

                    </div>

                </form>

                {{-- REGISTRO --}}

                <a href="{{ route('register') }}"
                   class="hidden sm:inline-flex items-center justify-center border border-[#111111] text-[#111111] hover:bg-[#111111] hover:text-white font-semibold text-[0.86rem] px-3.5 sm:px-4.5 py-2.5 rounded-lg transition whitespace-nowrap">

                    Regístrate

                </a>

                {{-- LOGIN --}}

                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center bg-amber-400 hover:bg-amber-500 text-[#111111] font-semibold text-[0.86rem] px-4 sm:px-5 py-2.5 rounded-lg transition whitespace-nowrap">

                    Iniciar sesión

                </a>

                {{-- MENÚ MÓVIL --}}

                <button
                    id="mobile-menu-button"
                    type="button"
                    class="md:hidden w-10 h-10 shrink-0 rounded-lg border border-gray-200 text-[#111111] flex items-center justify-center"
                    aria-label="Abrir menú"
                    aria-expanded="false"
                    aria-controls="mobile-menu">

                    <i id="mobile-menu-icon"
                       class="fa-solid fa-bars"></i>

                </button>

            </div>

        </div>

        {{-- MENÚ MÓVIL --}}

        <div id="mobile-menu"
             class="hidden border-t border-gray-100 bg-white md:hidden">

            <div class="w-full px-5 py-3">

                <a href="{{ route('home') }}"
                   class="mobile-menu-link block py-3 text-sm font-semibold text-[#111111]">
                    Inicio
                </a>

                <a href="{{ route('public.marketplace') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Tienda
                </a>

                <a href="#servicios"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Servicios
                </a>

                <a href="{{ route('public.calculator') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Calcular precio
                </a>

                <a href="{{ route('public.offices') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Agencias aliadas
                </a>

                <a href="{{ route('tracking.index') }}"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Rastreo
                </a>

                <a href="#ayuda"
                   class="mobile-menu-link block py-3 text-sm text-gray-600">
                    Ayuda
                </a>

                <form action="{{ route('tracking.show') }}"
                      method="GET"
                      class="py-3 border-t border-gray-100 mt-2">

                    <label for="mobile-guia"
                           class="block text-xs font-semibold mb-2">

                        Rastrea tu envío

                    </label>

                    <div class="flex gap-2">

                        <input
                            id="mobile-guia"
                            type="text"
                            name="guia"
                            placeholder="Número de guía"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-300 focus:border-amber-400"
                        >

                        <button type="submit"
                                class="px-4 rounded-lg bg-[#111111] text-white font-semibold text-sm">

                            Rastrear

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </nav>

    {{-- =========================================================
         HERO / CARRUSEL
    ========================================================== --}}

    <section id="hero-carousel"
             class="hero-shell relative overflow-hidden bg-white">

        <div id="hero-track"
             class="hero-track">

        {{-- Slide 1: Cliente --}}

        <div id="hero-slide-0"
             class="hero-slide hero-slide-item relative">

            <div class="hero-image-panel">

                <img
                    src="{{ asset('images/hero1.png') }}"
                    alt="Envíos Venexpress"
                    class="hero-image">

            </div>

            <div class="section-wrap hero-content">

                <div class="hero-copy">

                    <span class="eyebrow">
                        Envíos nacionales
                    </span>

                    <h1 class="hero-title">

                        Conectamos<br>
                        a Venezuela.

                    </h1>

                    <p class="hero-text">

                        Envía paquetes y documentos de forma rápida,
                        segura y sencilla, con agencias aliadas y
                        seguimiento en línea.

                    </p>

                    <div class="hero-actions">

                        <a href="{{ route('public.calculator') }}"
                           class="hero-primary">

                            Enviar un paquete

                            <i class="fa-solid fa-arrow-right text-xs"></i>

                        </a>

                        <a href="{{ route('tracking.index') }}"
                           class="hero-secondary">

                            <i class="fa-solid fa-magnifying-glass text-xs"></i>

                            Rastrear envío

                        </a>

                    </div>

                </div>

            </div>

        </div>

        {{-- Slide 2: Agencias --}}

        <div id="hero-slide-1"
             class="hero-slide hero-slide-item relative">

            <div class="hero-image-panel">

                <img
                    src="{{ asset('images/hero2.png') }}"
                    alt="Agencia aliada Venexpress"
                    class="hero-image">

            </div>

            <div class="section-wrap hero-content">

                <div class="hero-copy">

                    <span class="eyebrow">
                        Conviértete en aliado
                    </span>

                    <h2 class="hero-title">

                        ¿Tienes un<br>
                        negocio?

                    </h2>

                    <p class="hero-text">

                        Convierte tu local en un punto de atención
                        de nuestra red y ofrece nuevos servicios
                        a tus clientes.

                    </p>

                    <div class="hero-actions">

                        <a href="{{ route('register', ['role' => 'aliado']) }}"
                           class="hero-primary hero-role-button">

                            Quiero ser aliado

                            <i class="fa-solid fa-arrow-right text-xs"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

        {{-- Slide 3: Repartidores --}}

        <div id="hero-slide-2"
             class="hero-slide hero-slide-item relative">

            <div class="hero-image-panel">

                <img
                    src="{{ asset('images/hero3.png') }}"
                    alt="Repartidor Venexpress"
                    class="hero-image">

            </div>

            <div class="section-wrap hero-content">

                <div class="hero-copy">

                    <span class="eyebrow">
                        Únete a nuestra red
                    </span>

                    <h2 class="hero-title">

                        ¿Quieres repartir<br>
                        con nosotros?

                    </h2>

                    <p class="hero-text">

                        Conecta tu vehículo con nuestra red de
                        distribución y forma parte de las entregas
                        en tu ciudad.

                    </p>

                    <div class="hero-actions">

                        <a href="{{ route('register', ['role' => 'repartidor']) }}"
                           class="hero-primary hero-role-button">

                            Quiero ser repartidor

                            <i class="fa-solid fa-arrow-right text-xs"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

        {{-- Slide 4: Emprendedores --}}

        <div id="hero-slide-3"
             class="hero-slide hero-slide-item relative">

            <div class="hero-image-panel">

                <img
                    src="{{ asset('images/hero4.png') }}"
                    alt="Emprendimiento en Venexpress"
                    class="hero-image">

            </div>

            <div class="section-wrap hero-content">

                <div class="hero-copy">

                    <span class="eyebrow">
                        Para emprendedores
                    </span>

                    <h2 class="hero-title">

                        Haz crecer tu<br>
                        emprendimiento.

                    </h2>

                    <p class="hero-text">

                        Únete a nuestra tienda virtual y encuentra
                        nuevas oportunidades para hacer crecer
                        tu negocio.

                    </p>

                    <div class="hero-actions">

                        <a href="{{ route('register', ['role' => 'emprendedor']) }}"
                           class="hero-primary hero-role-button">

                            Quiero ser emprendedor

                            <i class="fa-solid fa-arrow-right text-xs"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

        </div>

        {{-- FLECHAS --}}

        <button type="button"
                class="hero-arrow hero-arrow-prev"
                data-hero-arrow="-1"
                aria-label="Slide anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <button type="button"
                class="hero-arrow hero-arrow-next"
                data-hero-arrow="1"
                aria-label="Slide siguiente">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        {{-- DOTS --}}

        <div class="hero-dots"
             aria-label="Navegación del carrusel">

            <button type="button"
                    data-hero-dot="0"
                    class="hero-dot is-active"
                    aria-label="Ver slide de cliente y envíos">
            </button>

            <button type="button"
                    data-hero-dot="1"
                    class="hero-dot"
                    aria-label="Ver slide de agencias aliadas">
            </button>

            <button type="button"
                    data-hero-dot="2"
                    class="hero-dot"
                    aria-label="Ver slide de repartidores">
            </button>

            <button type="button"
                    data-hero-dot="3"
                    class="hero-dot"
                    aria-label="Ver slide de emprendedores">
            </button>

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

                        <p class="utility-title">
                            Cobertura nacional
                        </p>

                        <p class="utility-subtitle">
                            Conectamos ciudades y estados.
                        </p>

                    </div>

                </div>

            </div>

            <div class="utility-item px-3 sm:px-5">

                <div class="utility-content">

                    <div class="utility-icon" aria-hidden="true">

                        <i class="fa-solid fa-store text-base"></i>

                    </div>

                    <div>

                        <p class="utility-title">
                            Agencias aliadas
                        </p>

                        <p class="utility-subtitle">
                            Recibe y entrega cerca de ti.
                        </p>

                    </div>

                </div>

            </div>

            <div class="utility-item px-3 sm:px-5">

                <div class="utility-content">

                    <div class="utility-icon" aria-hidden="true">

                        <i class="fa-solid fa-box text-base"></i>

                    </div>

                    <div>

                        <p class="utility-title">
                            Seguimiento en línea
                        </p>

                        <p class="utility-subtitle">
                            Consulta tu envío en cualquier momento.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- =========================================================
         SERVICIOS
    ========================================================== --}}

    <section id="servicios"
             class="section services-section bg-white">

        <div class="section-wrap">

            <div class="section-heading">

                <span class="eyebrow">
                    Todo en un solo lugar
                </span>

                <h2 class="section-title">
                    Enviar nunca debería ser complicado.
                </h2>

                <p class="section-subtitle">
                    Desde crear tu envío hasta recibirlo,
                    encuentra en un solo lugar las acciones
                    que más necesitas.
                </p>

            </div>

            <div class="quick-grid">

                <a href="{{ route('public.calculator') }}"
                   class="quick-card group">

                    <div class="icon-chip">
                        <i class="fa-solid fa-box"></i>
                    </div>

                    <span class="card-kicker">
                        Tu envío
                    </span>

                    <h3 class="card-title">
                        Envía
                    </h3>

                    <p class="card-text">
                        Calcula el precio y crea el envío
                        para llevarlo a una agencia.
                    </p>

                    <span class="card-link">

                        Crear envío

                        <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i>

                    </span>

                </a>

                <a href="{{ route('public.offices') }}"
                   class="quick-card group">

                    <div class="icon-chip">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>

                    <span class="card-kicker">
                        Punto cercano
                    </span>

                    <h3 class="card-title">
                        Encuentra una agencia
                    </h3>

                    <p class="card-text">
                        Localiza el punto más cercano para
                        entregar o recibir tu paquete.
                    </p>

                    <span class="card-link">

                        Ver agencias

                        <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i>

                    </span>

                </a>

                <a href="{{ route('tracking.index') }}"
                   class="quick-card group">

                    <div class="icon-chip">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <span class="card-kicker">
                        Seguimiento
                    </span>

                    <h3 class="card-title">
                        Rastrea tu envío
                    </h3>

                    <p class="card-text">
                        Consulta el estado de tu paquete
                        con tu número de guía.
                    </p>

                    <span class="card-link">

                        Rastrear

                        <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i>

                    </span>

                </a>

                <a href="#empresas"
                   class="quick-card group">

                    <div class="icon-chip">
                        <i class="fa-solid fa-building"></i>
                    </div>

                    <span class="card-kicker">
                        Soluciones B2B
                    </span>

                    <h3 class="card-title">
                        Para empresas
                    </h3>

                    <p class="card-text">
                        Soluciones logísticas para negocios
                        que necesitan mover productos.
                    </p>

                    <span class="card-link">

                        Conocer soluciones

                        <i class="fa-solid fa-arrow-right text-[0.6rem] transition group-hover:translate-x-1"></i>

                    </span>

                </a>

            </div>

        </div>

    </section>

    {{-- =========================================================
         MARKETPLACE (vista previa)
    ========================================================== --}}

    <section id="marketplace"
             class="section section-soft">

        <div class="section-wrap">

            <div class="section-heading">

                <span class="eyebrow">
                    Tienda Venexpress
                </span>

                <h2 class="section-title">
                    Descubre la Tienda Venexpress.
                </h2>

                <p class="section-subtitle">
                    Productos de emprendedores venezolanos, con envío
                    por nuestra red de Venexpress.
                </p>

                <a href="{{ route('public.marketplace') }}"
                   class="hero-primary mt-6">

                    Explorar la tienda

                    <i class="fa-solid fa-arrow-right text-xs"></i>

                </a>

            </div>

            @if ($productosDestacados->isNotEmpty())

                <div class="market-grid">

                    @foreach ($productosDestacados as $producto)
                        <x-marketplace-preview-card :producto="$producto" />
                    @endforeach

                </div>

            @endif

        </div>

    </section>

    {{-- =========================================================
         COBERTURA
    ========================================================== --}}

    <section id="cobertura"
             class="section bg-white">

        <div class="section-wrap coverage-layout">

            <div>

                <span class="eyebrow">
                    Llegamos más lejos
                </span>

                <h2 class="section-title">
                    De una ciudad a otra,
                    seguimos conectando Venezuela.
                </h2>

                <p class="section-subtitle">
                    Conoce nuestra cobertura y encuentra
                    el destino más cercano dentro de nuestra red.
                </p>

                <a href="{{ route('public.offices') }}"
                   class="hero-primary mt-6">

                    Ver agencias y cobertura

                    <i class="fa-solid fa-arrow-right text-xs"></i>

                </a>

            </div>

            <div class="outline-map"
                 aria-label="Mapa de cobertura nacional">

                <img
                    src="{{ asset('images/venezuela-map.png') }}"
                    alt="Mapa de cobertura nacional de Venexpress"
                    class="outline-map-image"
                >

            </div>

        </div>

    </section>

    {{-- =========================================================
         AGENCIAS
    ========================================================== --}}

    <section id="aliados"
             class="section section-soft">

        <div class="section-wrap">

            <div class="agency-header">

                <div class="section-heading">

                    <span class="eyebrow">
                        Nuestra red
                    </span>

                    <h2 class="section-title">
                        Encuentra una agencia cerca de ti.
                    </h2>

                    <p class="section-subtitle">
                        Nuestras agencias aliadas son puntos
                        donde puedes realizar y recibir tus envíos.
                    </p>

                </div>

                <a href="{{ route('public.offices') }}"
                   class="hidden sm:inline-flex items-center gap-2 text-[0.72rem] font-bold">

                    Ver todas las agencias

                    <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>

                </a>

            </div>

            <div class="agency-grid">

                <a href="{{ route('public.offices') }}"
                   class="agency-card hover:shadow-md transition">

                    <div class="agency-thumb">
                        Foto de agencia
                    </div>

                    <div>

                        <p class="agency-name">
                            Librería El Profe
                        </p>

                        <p class="agency-location mt-1">

                            <i class="fa-solid fa-location-dot mr-1"></i>

                            Cumaná, Sucre

                        </p>

                        <span class="agency-link">

                            Ver ubicación

                            <i class="fa-solid fa-arrow-right text-[0.5rem]"></i>

                        </span>

                    </div>

                </a>

                <a href="{{ route('public.offices') }}"
                   class="agency-card hover:shadow-md transition">

                    <div class="agency-thumb">
                        Foto de agencia
                    </div>

                    <div>

                        <p class="agency-name">
                            Papelería Los Amigos
                        </p>

                        <p class="agency-location mt-1">

                            <i class="fa-solid fa-location-dot mr-1"></i>

                            Carúpano, Sucre

                        </p>

                        <span class="agency-link">

                            Ver ubicación

                            <i class="fa-solid fa-arrow-right text-[0.5rem]"></i>

                        </span>

                    </div>

                </a>

                <a href="{{ route('public.offices') }}"
                   class="agency-card hover:shadow-md transition">

                    <div class="agency-thumb">
                        Foto de agencia
                    </div>

                    <div>

                        <p class="agency-name">
                            Tecnología 2000
                        </p>

                        <p class="agency-location mt-1">

                            <i class="fa-solid fa-location-dot mr-1"></i>

                            Barcelona, Anzoátegui

                        </p>

                        <span class="agency-link">

                            Ver ubicación

                            <i class="fa-solid fa-arrow-right text-[0.5rem]"></i>

                        </span>

                    </div>

                </a>

                <a href="{{ route('public.offices') }}"
                   class="agency-card hover:shadow-md transition">

                    <div class="agency-thumb">
                        Foto de agencia
                    </div>

                    <div>

                        <p class="agency-name">
                            Variedades San Rafael
                        </p>

                        <p class="agency-location mt-1">

                            <i class="fa-solid fa-location-dot mr-1"></i>

                            Maturín, Monagas

                        </p>

                        <span class="agency-link">

                            Ver ubicación

                            <i class="fa-solid fa-arrow-right text-[0.5rem]"></i>

                        </span>

                    </div>

                </a>

            </div>

        </div>

    </section>

    {{-- =========================================================
         COMO FUNCIONA
    ========================================================== --}}

    <section id="como-funciona"
             class="section bg-white">

        <div class="section-wrap">

            <div class="section-heading">

                <span class="eyebrow">
                    Tu envío, paso a paso
                </span>

                <h2 class="section-title">
                    Así de fácil es enviar.
                </h2>

                <p class="section-subtitle">
                    Un recorrido claro desde la entrega de tu
                    paquete hasta su llegada a destino.
                </p>

            </div>

            <div class="steps">

                <div class="step">

                    <div class="step-number">
                        01
                    </div>

                    <h3 class="step-title">
                        Entrega
                    </h3>

                    <p class="step-text">
                        Lleva tu paquete a una agencia aliada.
                    </p>

                </div>

                <div class="step">

                    <div class="step-number">
                        02
                    </div>

                    <h3 class="step-title">
                        Recolección
                    </h3>

                    <p class="step-text">
                        Nuestro equipo recibe y procesa tu envío.
                    </p>

                </div>

                <div class="step">

                    <div class="step-number">
                        03
                    </div>

                    <h3 class="step-title">
                        Traslado
                    </h3>

                    <p class="step-text">
                        Tu paquete viaja hacia su destino.
                    </p>

                </div>

                <div class="step">

                    <div class="step-number">
                        04
                    </div>

                    <h3 class="step-title">
                        Entrega
                    </h3>

                    <p class="step-text">
                        Llega a la agencia de destino para su retiro.
                    </p>

                </div>

            </div>

        </div>

    </section>

    {{-- =========================================================
         EMPRESAS / ALIADOS / REPARTIDORES
    ========================================================== --}}

    <section id="empresas"
             class="section section-soft">

        <div class="section-wrap">

            <div class="section-heading">

                <span class="eyebrow">
                    Más que envíos
                </span>

                <h2 class="section-title">
                    Una red para clientes,
                    empresas y aliados.
                </h2>

                <p class="section-subtitle">
                    Elige la forma en que quieres formar parte
                    del ecosistema logístico.
                </p>

            </div>

            <div class="partner-grid">

                {{-- =============================================
                     TARJETA GRANDE EMPRESAS + HERO6
                ============================================== --}}

                <article class="partner-main">

                    <div class="partner-main-content">

                        <span class="text-[0.64rem] font-extrabold uppercase tracking-[0.08em]">
                            Para empresas
                        </span>

                        <h3 class="text-2xl lg:text-3xl font-extrabold leading-[1.02] tracking-tight mt-2">

                            Haz que tus envíos
                            trabajen para tu negocio.

                        </h3>

                        <p class="text-[0.72rem] leading-6 mt-3 max-w-md text-black/70">

                            Soluciones logísticas para negocios
                            que necesitan enviar productos con
                            mayor facilidad.

                        </p>

                        <a href="{{ route('login') }}"
                           class="mt-5 inline-flex items-center gap-2 text-[0.72rem] font-extrabold">

                            Conocer soluciones

                            <i class="fa-solid fa-arrow-right text-[0.58rem]"></i>

                        </a>

                    </div>

                    {{-- HERO6 --}}

                    <img
                        src="{{ asset('images/hero6.png') }}"
                        alt="Entrega de paquetes Venexpress"
                        class="partner-main-image"
                    >

                </article>

                {{-- ALIADOS --}}

                <article class="partner-small">

                    <div class="icon-chip">

                        <i class="fa-solid fa-store"></i>

                    </div>

                    <div>

                        <span class="card-kicker">
                            Aliados
                        </span>

                        <h3 class="card-title text-base">
                            ¿Tienes un negocio?
                        </h3>

                        <p class="card-text">
                            Conviértelo en un punto aliado
                            y forma parte de nuestra red.
                        </p>

                        <a href="{{ route('register', ['role' => 'aliado']) }}"
                           class="card-link">

                            Quiero ser aliado

                            <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>

                        </a>

                    </div>

                </article>

                {{-- REPARTIDORES --}}

                <article class="partner-small">

                    <div class="icon-chip">

                        <i class="fa-solid fa-motorcycle"></i>

                    </div>

                    <div>

                        <span class="card-kicker">
                            Repartidores
                        </span>

                        <h3 class="card-title text-base">
                            ¿Quieres repartir?
                        </h3>

                        <p class="card-text">
                            Únete a nuestra red y conecta
                            tu vehículo con las entregas.
                        </p>

                        <a href="{{ route('register', ['role' => 'repartidor']) }}"
                           class="card-link">

                            Quiero ser repartidor

                            <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>

                        </a>

                    </div>

                </article>

            </div>

        </div>

    </section>

    {{-- =========================================================
         FAQ
    ========================================================== --}}

    <section id="ayuda"
             class="section bg-white">

        <div class="section-wrap faq-layout">

            <div>

                <span class="eyebrow">
                    Preguntas frecuentes
                </span>

                <h2 class="section-title">
                    Todo lo que necesitas saber
                    antes de enviar.
                </h2>

                <p class="section-subtitle">
                    Respuestas rápidas para las dudas
                    más comunes sobre tus envíos.
                </p>

            </div>

            <div class="faq-list">

                <div class="faq-item">

                    <button type="button"
                            class="faq-question"
                            aria-expanded="false">

                        <span>
                            ¿Cómo puedo realizar un envío?
                        </span>

                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>

                    </button>

                    <div class="faq-answer">

                        Puedes comenzar calculando el precio
                        de tu envío y luego llevar el paquete
                        a una agencia aliada para registrarlo.

                    </div>

                </div>

                <div class="faq-item">

                    <button type="button"
                            class="faq-question"
                            aria-expanded="false">

                        <span>
                            ¿Dónde puedo entregar mi paquete?
                        </span>

                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>

                    </button>

                    <div class="faq-answer">

                        Consulta la sección de agencias para
                        encontrar el punto disponible que te
                        resulte más conveniente.

                    </div>

                </div>

                <div class="faq-item">

                    <button type="button"
                            class="faq-question"
                            aria-expanded="false">

                        <span>
                            ¿Cómo puedo rastrear mi envío?
                        </span>

                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>

                    </button>

                    <div class="faq-answer">

                        Introduce tu número de guía en el
                        buscador del menú superior o en la
                        página de rastreo.

                    </div>

                </div>

                <div class="faq-item">

                    <button type="button"
                            class="faq-question"
                            aria-expanded="false">

                        <span>
                            ¿Cuánto cuesta un envío?
                        </span>

                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>

                    </button>

                    <div class="faq-answer">

                        El precio depende de las características
                        del envío y su destino. Puedes utilizar
                        el calculador para obtener una estimación.

                    </div>

                </div>

                <div class="faq-item">

                    <button type="button"
                            class="faq-question"
                            aria-expanded="false">

                        <span>
                            ¿Qué puedo enviar?
                        </span>

                        <i class="faq-icon fa-solid fa-plus text-[0.65rem]"></i>

                    </button>

                    <div class="faq-answer">

                        Las condiciones de envío dependen del
                        tipo de contenido y de las políticas vigentes.
                        Consulta las condiciones antes de registrar
                        tu paquete.

                    </div>

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

                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                        ¿Listo para enviar?
                    </h2>

                    <p class="text-xs sm:text-sm text-black/65 mt-1">
                        Conecta con Venezuela a través de nuestra red.
                    </p>

                </div>

            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('public.calculator') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-[#111111] text-white px-5 py-2.5 text-[0.72rem] font-bold hover:bg-[#222] transition">

                    Crear un envío

                    <i class="fa-solid fa-arrow-right text-[0.58rem]"></i>

                </a>

                <a href="{{ route('public.offices') }}"
                   class="inline-flex items-center gap-2 rounded-full border border-[#111111] text-[#111111] px-5 py-2.5 text-[0.72rem] font-bold hover:bg-white/55 transition">

                    <i class="fa-solid fa-location-dot text-[0.6rem]"></i>

                    Encontrar una agencia

                </a>

            </div>

        </div>

    </section>

    {{-- =========================================================
     FOOTER
========================================================= --}}

<footer class="footer">

    <div class="w-full px-5 sm:px-6 lg:px-10 py-12
                grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

        {{-- COLUMNA 1 --}}
        <div class="flex flex-col items-start">

            <a href="{{ route('home') }}"
               class="inline-flex items-center mb-4">

                <img
                    src="{{ asset('images/venexpress-logo-white.png') }}"
                    alt="Venexpress"
                    class="h-10 w-auto block"
                >

            </a>

            <p class="text-white/55 text-[0.69rem] leading-5 max-w-xs">
                Conectamos a Venezuela con soluciones
                de envío rápidas, seguras y confiables.
            </p>

            <div class="flex items-center gap-2 mt-5">

                <a href="#"
                   aria-label="Facebook"
                   class="w-8 h-8 rounded-full border border-white/10
                          flex items-center justify-center
                          hover:bg-white/10 transition">
                    <i class="fa-brands fa-facebook-f text-white text-xs"></i>
                </a>

                <a href="#"
                   aria-label="Instagram"
                   class="w-8 h-8 rounded-full border border-white/10
                          flex items-center justify-center
                          hover:bg-white/10 transition">
                    <i class="fa-brands fa-instagram text-white text-xs"></i>
                </a>

                <a href="#"
                   aria-label="X"
                   class="w-8 h-8 rounded-full border border-white/10
                          flex items-center justify-center
                          hover:bg-white/10 transition">
                    <i class="fa-brands fa-x-twitter text-white text-xs"></i>
                </a>

                <a href="#"
                   aria-label="WhatsApp"
                   class="w-8 h-8 rounded-full border border-white/10
                          flex items-center justify-center
                          hover:bg-white/10 transition">
                    <i class="fa-brands fa-whatsapp text-white text-xs"></i>
                </a>

            </div>

        </div>


        {{-- COLUMNA 2 --}}
        <div>

            <h3 class="text-white text-[0.75rem] font-bold mb-4">
                Servicios
            </h3>

            <ul class="space-y-2.5">

                <li>
                    <a href="{{ route('public.calculator') }}"
                       class="footer-link">
                        Envíos
                    </a>
                </li>

                <li>
                    <a href="{{ route('tracking.index') }}"
                       class="footer-link">
                        Rastreo
                    </a>
                </li>

                <li>
                    <a href="{{ route('public.offices') }}"
                       class="footer-link">
                        Agencias
                    </a>
                </li>

                <li>
                    <a href="{{ route('public.marketplace') }}"
                       class="footer-link">
                        Tienda
                    </a>
                </li>

                <li>
                    <a href="#empresas"
                       class="footer-link">
                        Empresas
                    </a>
                </li>

            </ul>

        </div>


        {{-- COLUMNA 3 --}}
        <div>

            <h3 class="text-white text-[0.75rem] font-bold mb-4">
                Venexpress
            </h3>

            <ul class="space-y-2.5">

                <li>
                    <a href="#"
                       class="footer-link">
                        Sobre nosotros
                    </a>
                </li>

                <li>
                    <a href="{{ route('register', ['role' => 'repartidor']) }}"
                       class="footer-link">
                        Únete a la red
                    </a>
                </li>

                <li>
                    <a href="{{ route('register', ['role' => 'aliado']) }}"
                       class="footer-link">
                        Sé aliado
                    </a>
                </li>

                <li>
                    <a href="{{ route('login') }}"
                       class="footer-link">
                        Acceso
                    </a>
                </li>

            </ul>

        </div>


        {{-- COLUMNA 4 --}}
        <div>

            <h3 class="text-white text-[0.75rem] font-bold mb-4">
                Ayuda
            </h3>

            <ul class="space-y-2.5">

                <li>
                    <a href="#ayuda"
                       class="footer-link">
                        Preguntas frecuentes
                    </a>
                </li>

                <li>
                    <a href="{{ route('public.privacy') }}"
                       class="footer-link">
                        Política de privacidad
                    </a>
                </li>

                <li>
                    <a href="{{ route('public.terms') }}"
                       class="footer-link">
                        Términos y condiciones
                    </a>
                </li>

                <li>
                    <a href="mailto:info@venexpress.com"
                       class="footer-link">
                        Contáctanos
                    </a>
                </li>

            </ul>

        </div>

    </div>


    {{-- BARRA INFERIOR --}}
    <div class="border-t border-white/10">

        <div class="w-full px-5 sm:px-6 lg:px-10 py-5
                    flex flex-col sm:flex-row
                    justify-between gap-2
                    text-[0.64rem] text-white/40">

            <span>
                &copy; {{ date('Y') }} Venexpress.
                Todos los derechos reservados.
            </span>

            <span>
                Conectamos a Venezuela.
            </span>

        </div>

    </div>

</footer>

    {{-- =========================================================
         JAVASCRIPT
    ========================================================== --}}

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            /* =====================================================
               MENÚ MÓVIL
            ====================================================== */

            const menuButton =
                document.getElementById('mobile-menu-button');

            const mobileMenu =
                document.getElementById('mobile-menu');

            const mobileMenuIcon =
                document.getElementById('mobile-menu-icon');

            let closeMobileMenu = null;

            if (
                menuButton &&
                mobileMenu &&
                mobileMenuIcon
            ) {

                const closeMenu = () => {

                    mobileMenu.classList.add('hidden');

                    mobileMenuIcon.classList.remove('fa-xmark');
                    mobileMenuIcon.classList.add('fa-bars');

                    menuButton.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                };

                closeMobileMenu = closeMenu;

                menuButton.addEventListener(
                    'click',
                    function () {

                        const isOpen =
                            !mobileMenu.classList.contains('hidden');

                        if (isOpen) {

                            closeMenu();

                            return;

                        }

                        mobileMenu.classList.remove('hidden');

                        mobileMenuIcon.classList.remove('fa-bars');
                        mobileMenuIcon.classList.add('fa-xmark');

                        menuButton.setAttribute(
                            'aria-expanded',
                            'true'
                        );

                    }
                );

                document
                    .querySelectorAll('.mobile-menu-link')
                    .forEach(link => {

                        link.addEventListener(
                            'click',
                            closeMenu
                        );

                    });

            }

            /* =====================================================
               NAVBAR AL HACER SCROLL
            ====================================================== */

            const mainNavbar =
                document.getElementById('main-navbar');

            let lastScrollY = window.scrollY;
            let scrollTicking = false;

            const updateNavbarOnScroll = () => {

                const currentScrollY =
                    window.scrollY;

                const scrollDifference =
                    currentScrollY - lastScrollY;

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

            window.addEventListener(
                'scroll',
                function () {

                    if (scrollTicking) return;

                    scrollTicking = true;

                    window.requestAnimationFrame(
                        updateNavbarOnScroll
                    );

                },
                {
                    passive: true
                }
            );

            /* =====================================================
               CARRUSEL
               Una sola lógica (showHeroSlide) alimenta puntos,
               flechas, autoplay y arrastre/swipe.
            ====================================================== */

            const heroShell =
                document.getElementById('hero-carousel');

            const heroTrack =
                document.getElementById('hero-track');

            const heroSlides =
                Array.from(
                    document.querySelectorAll('.hero-slide-item')
                );

            const heroDots =
                Array.from(
                    document.querySelectorAll('.hero-dot')
                );

            const heroArrows =
                Array.from(
                    document.querySelectorAll('[data-hero-arrow]')
                );

            let heroCurrent = 0;
            let heroTimer = null;

            function showHeroSlide(index) {

                if (!heroSlides.length || !heroTrack) return;

                index =
                    (index + heroSlides.length) %
                    heroSlides.length;

                heroTrack.style.transform =
                    'translateX(-' + (index * 100) + '%)';

                heroSlides.forEach(
                    (slide, i) => {

                        // Los slides fuera de vista no reciben foco
                        // ni los leen los lectores de pantalla.
                        slide.inert = i !== index;

                        slide.setAttribute(
                            'aria-hidden',
                            i !== index ? 'true' : 'false'
                        );

                    }
                );

                heroDots.forEach(
                    (dot, i) => {

                        dot.classList.toggle(
                            'is-active',
                            i === index
                        );

                    }
                );

                heroCurrent = index;

            }

            function stopHeroAutoplay() {

                clearInterval(heroTimer);

            }

            function startHeroAutoplay() {

                if (heroSlides.length < 2) return;

                stopHeroAutoplay();

                heroTimer = setInterval(
                    () => {

                        showHeroSlide(heroCurrent + 1);

                    },
                    6000
                );

            }

            if (
                heroShell &&
                heroTrack &&
                heroSlides.length
            ) {

                heroDots.forEach(
                    dot => {

                        dot.addEventListener(
                            'click',
                            function () {

                                showHeroSlide(
                                    Number(
                                        dot.dataset.heroDot
                                    )
                                );

                                startHeroAutoplay();

                            }
                        );

                    }
                );

                heroArrows.forEach(
                    arrow => {

                        arrow.addEventListener(
                            'click',
                            function () {

                                showHeroSlide(
                                    heroCurrent +
                                    Number(
                                        arrow.dataset.heroArrow
                                    )
                                );

                                startHeroAutoplay();

                            }
                        );

                    }
                );

                /* ---- Arrastre (mouse) y swipe (touch) ---- */

                const DRAG_INTENT_PX = 6;

                let dragPointerId = null;
                let dragStartX = 0;
                let dragStartY = 0;
                let dragDeltaX = 0;
                let dragActive = false;
                let dragMoved = false;

                function finishHeroDrag(commit) {

                    if (dragPointerId === null) return;

                    const width =
                        heroShell.getBoundingClientRect().width;

                    const threshold =
                        Math.min(80, width * 0.15);

                    if (dragActive) {

                        try {
                            heroShell.releasePointerCapture(
                                dragPointerId
                            );
                        } catch (e) {}

                    }

                    heroShell.classList.remove('is-dragging');

                    if (commit && dragActive) {

                        if (dragDeltaX <= -threshold) {

                            showHeroSlide(heroCurrent + 1);

                        } else if (dragDeltaX >= threshold) {

                            showHeroSlide(heroCurrent - 1);

                        } else {

                            showHeroSlide(heroCurrent);

                        }

                    } else if (dragActive) {

                        showHeroSlide(heroCurrent);

                    }

                    dragPointerId = null;
                    dragActive = false;
                    dragDeltaX = 0;

                    // El click posterior al arrastre se traga; la marca se
                    // limpia sola para no afectar a flechas o puntos.
                    setTimeout(() => { dragMoved = false; }, 50);

                    startHeroAutoplay();

                }

                heroTrack.addEventListener(
                    'pointerdown',
                    function (e) {

                        if (
                            dragPointerId !== null ||
                            (e.pointerType === 'mouse' && e.button !== 0)
                        ) return;

                        dragPointerId = e.pointerId;
                        dragStartX = e.clientX;
                        dragStartY = e.clientY;
                        dragDeltaX = 0;
                        dragActive = false;
                        dragMoved = false;

                        stopHeroAutoplay();

                    }
                );

                heroShell.addEventListener(
                    'pointermove',
                    function (e) {

                        if (e.pointerId !== dragPointerId) return;

                        const dx = e.clientX - dragStartX;
                        const dy = e.clientY - dragStartY;

                        if (!dragActive) {

                            // Solo es arrastre si la intención es
                            // claramente horizontal.
                            if (
                                Math.abs(dx) < DRAG_INTENT_PX ||
                                Math.abs(dx) < Math.abs(dy)
                            ) return;

                            dragActive = true;
                            dragMoved = true;

                            // Se captura el puntero recién aquí, para
                            // que un simple tap siga llegando intacto
                            // a botones y enlaces del slide.
                            heroShell.setPointerCapture(e.pointerId);
                            heroShell.classList.add('is-dragging');

                        }

                        dragDeltaX = dx;

                        heroTrack.style.transform =
                            'translateX(calc(-' +
                            (heroCurrent * 100) +
                            '% + ' + dx + 'px))';

                    }
                );

                heroShell.addEventListener(
                    'pointerup',
                    function (e) {

                        if (e.pointerId === dragPointerId) {

                            finishHeroDrag(true);

                        }

                    }
                );

                heroShell.addEventListener(
                    'pointercancel',
                    function (e) {

                        if (e.pointerId === dragPointerId) {

                            finishHeroDrag(false);

                        }

                    }
                );

                // Si hubo arrastre, el click que dispara el navegador al
                // soltar no debe activar un enlace o botón del slide.
                heroShell.addEventListener(
                    'click',
                    function (e) {

                        if (dragMoved) {

                            e.preventDefault();
                            e.stopPropagation();
                            dragMoved = false;

                        }

                    },
                    true
                );

                // Evita el drag nativo de enlaces/imágenes.
                heroTrack.addEventListener(
                    'dragstart',
                    e => e.preventDefault()
                );

                showHeroSlide(0);

                startHeroAutoplay();

            }

            /* =====================================================
               FAQ
            ====================================================== */

            document
                .querySelectorAll('.faq-question')
                .forEach(button => {

                    button.addEventListener(
                        'click',
                        function () {

                            const item =
                                button.closest('.faq-item');

                            if (!item) return;

                            const isOpen =
                                item.classList.contains(
                                    'is-open'
                                );

                            document
                                .querySelectorAll(
                                    '.faq-item.is-open'
                                )
                                .forEach(openItem => {

                                    openItem.classList.remove(
                                        'is-open'
                                    );

                                    const openButton =
                                        openItem.querySelector(
                                            '.faq-question'
                                        );

                                    if (openButton) {

                                        openButton.setAttribute(
                                            'aria-expanded',
                                            'false'
                                        );

                                    }

                                });

                            if (!isOpen) {

                                item.classList.add(
                                    'is-open'
                                );

                                button.setAttribute(
                                    'aria-expanded',
                                    'true'
                                );

                            }

                        }
                    );

                });

        });

    </script>

</body>
</html>