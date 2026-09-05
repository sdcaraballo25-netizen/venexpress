<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        Guía {{ $package->tracking_number }}
    </title>

    <style>
        @page {
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            background: #fff;
        }

        .label {
            width: 100%;
            padding: 18px;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-right {
            text-align: right;
        }

        .brand {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .tracking {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }

        .section {
            border-bottom: 1px solid #000;
            padding-bottom: 9px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 11px;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 3px;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .small {
            font-size: 11px;
            margin: 2px 0 0;
        }

        .destination {
            text-align: center;
            border: 2px solid #000;
            padding: 10px;
            margin: 12px 0;
        }

        .destination .city {
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }

        .destination .state {
            font-size: 13px;
            margin: 3px 0 0;
        }

        .service {
            text-align: center;
            border: 1px solid #000;
            padding: 7px;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: bold;
        }

        .qr {
            text-align: center;
            margin: 14px 0;
        }

        .qr svg {
            display: block;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }

        .qr-tracking {
            margin: 5px 0 0;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-top: 10px;
        }

        .info-cell {
            display: table-cell;
            width: 33.33%;
            padding: 7px 5px;
            vertical-align: top;
            text-align: center;
        }

        .info-cell + .info-cell {
            border-left: 1px solid #000;
        }

        .info-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
        }

        .info-value {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        .cod {
            background: #000;
            color: #fff;
            text-align: center;
            padding: 8px;
            margin-top: 12px;
        }

        .cod-title {
            font-size: 11px;
            font-weight: bold;
            margin: 0;
        }

        .cod-amount {
            font-size: 18px;
            font-weight: bold;
            margin: 3px 0 0;
        }

        .fragile {
            text-align: center;
            border: 2px solid #000;
            padding: 7px;
            margin-top: 12px;
            font-size: 13px;
            font-weight: bold;
        }

        .security {
            text-align: center;
            margin-top: 12px;
            font-size: 10px;
        }

        .footer {
            text-align: center;
            border-top: 1px dashed #000;
            margin-top: 14px;
            padding-top: 8px;
            font-size: 10px;
        }
    </style>
</head>

<body>

<div class="label">

    {{-- ========================================================= --}}
    {{-- ENCABEZADO --}}
    {{-- ========================================================= --}}
    <div class="header">

        <div class="header-left">

            <p class="brand">
                VENEXPRESS
            </p>

            @if ($package->ally)
                <p class="small">
                    {{ $package->ally->business_name ?? ($package->ally->name ?? '') }}
                </p>
            @endif

        </div>

        <div class="header-right">

            <p class="tracking">
                {{ $package->tracking_number }}
            </p>

            <p class="small">
                {{ $package->created_at?->format('d/m/Y H:i') }}
            </p>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- QR --}}
    {{-- ========================================================= --}}
    <div class="qr">

        {!! $qrSvg !!}

        <p class="qr-tracking">
            {{ $package->tracking_number }}
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- REMITENTE --}}
    {{-- ========================================================= --}}
    <div class="section">

        <div class="title">
            Remitente
        </div>

        <p class="value">
            {{ $package->sender_name }}
        </p>

        <p class="small">
            Documento: {{ $package->sender_id_doc }}
        </p>

        <p class="small">
            Teléfono: {{ $package->sender_phone }}
        </p>

        <p class="small">
            Origen:
            {{ $package->origin_city }}

            @if ($package->origin_state)
                , {{ $package->origin_state }}
            @endif
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- DESTINATARIO --}}
    {{-- ========================================================= --}}
    <div class="section">

        <div class="title">
            Destinatario
        </div>

        <p class="value">
            {{ $package->recipient_name }}
        </p>

        <p class="small">
            Documento: {{ $package->recipient_id_doc }}
        </p>

        <p class="small">
            Teléfono: {{ $package->recipient_phone }}
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- DESTINO --}}
    {{-- ========================================================= --}}
    <div class="destination">

        <p class="city">
            {{ $package->destination_city }}
        </p>

        <p class="state">
            {{ $package->destination_state }}
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- SERVICIO --}}
    {{-- ========================================================= --}}
    <div class="service">

        @if ($package->requires_delivery)
            ENTREGA A DOMICILIO
        @else
            RETIRO EN AGENCIA
        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- DIRECCIÓN DE ENTREGA --}}
    {{-- ========================================================= --}}
    @if ($package->requires_delivery)

        <div class="section">

            <div class="title">
                Dirección de entrega
            </div>

            <p class="value">
                {{ $package->delivery_address }}
            </p>

            @if ($package->delivery_sector)
                <p class="small">
                    Sector:
                    {{ $package->delivery_sector }}
                </p>
            @endif

            @if ($package->delivery_reference)
                <p class="small">
                    Referencia:
                    {{ $package->delivery_reference }}
                </p>
            @endif

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- DATOS DEL PAQUETE --}}
    {{-- ========================================================= --}}
    <div class="info-grid">

        <div class="info-cell">

            <div class="info-label">
                Tipo
            </div>

            <div class="info-value">
                {{ strtoupper($package->package_type) }}
            </div>

        </div>

        <div class="info-cell">

            <div class="info-label">
                Peso
            </div>

            <div class="info-value">
                {{ number_format((float) $package->physical_weight_kg, 3) }} kg
            </div>

        </div>

        <div class="info-cell">

            <div class="info-label">
                Estado
            </div>

            <div class="info-value">
                {{ str_replace('_', ' ', $package->current_status) }}
            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FRÁGIL --}}
    {{-- ========================================================= --}}
    @if ($package->is_fragile)

        <div class="fragile">
            ⚠ FRÁGIL — MANEJAR CON CUIDADO
        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- COD --}}
    {{-- ========================================================= --}}
    @if ($package->is_cod)

        <div class="cod">

            <p class="cod-title">
                COBRO CONTRA ENTREGA
            </p>

            <p class="cod-amount">
                ${{ number_format((float) $package->cod_amount_usd, 2) }}
            </p>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- SEGURIDAD --}}
    {{-- ========================================================= --}}
    @if ($package->security_hash)

        <div class="security">

            Código de seguridad:
            <strong>
                {{ $package->security_hash }}
            </strong>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- PIE --}}
    {{-- ========================================================= --}}
    <div class="footer">

        <p style="margin:0;">
            VENEXPRESS
        </p>

        <p style="margin:3px 0 0;">
            Conserva esta guía para rastrear tu envío.
        </p>

    </div>

</div>

</body>
</html>

