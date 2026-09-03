<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guía {{ $package->tracking_number }}</title>
    <style>
        /*
         * dompdf no soporta flexbox ni grid: todo el layout de esta
         * plantilla usa tablas e inline-block a propósito.
         */
        @page {
            margin: 10px;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #111;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #111;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .brand {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .tracking-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 6px 0 2px 0;
        }

        .barcode {
            text-align: center;
            margin: 6px 0;
        }

        .barcode img, .barcode svg {
            width: 240px;
            height: 55px;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.info td {
            padding: 3px 0;
            vertical-align: top;
        }

        .box {
            border: 1px solid #999;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 8px;
        }

        .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
        }

        .value {
            font-size: 12px;
            font-weight: bold;
        }

        .section-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            margin-bottom: 4px;
            padding-bottom: 2px;
        }

        .badge {
            display: inline-block;
            border: 1px solid #111;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 10px;
            font-size: 8px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">VENEXPRESS</div>
        <div class="tracking-number">{{ $package->tracking_number }}</div>

        <div class="barcode">
            {!! $barcodeSvg !!}
        </div>

        <span class="badge">
            {{ $package->isSobre() ? 'SOBRE' : 'PAQUETE' }}
        </span>

        @if ($package->is_fragile)
            <span class="badge">FRÁGIL</span>
        @endif

        @if ($package->is_cod)
            <span class="badge">COBRO CONTRA ENTREGA</span>
        @endif
    </div>

    <table class="info">
        <tr>
            <td style="width: 50%;">
                <div class="box">
                    <div class="section-title">Origen</div>
                    <div class="value">{{ $package->origin_city }}</div>
                    <div>{{ $package->origin_state }}</div>

                    @if ($package->ally)
                        <div style="margin-top: 4px;">
                            <span class="label">Agencia</span><br>
                            {{ $package->ally->business_name }}
                        </div>
                    @endif
                </div>
            </td>
            <td style="width: 50%; padding-left: 6px;">
                <div class="box">
                    <div class="section-title">Destino</div>
                    <div class="value">{{ $package->destination_city }}</div>
                    <div>{{ $package->destination_state }}</div>

                    @if ($package->requires_delivery)
                        <div style="margin-top: 4px;">
                            <span class="label">Entrega a domicilio</span><br>
                            {{ $package->delivery_address }}
                            @if ($package->delivery_sector)
                                — {{ $package->delivery_sector }}
                            @endif
                        </div>
                    @else
                        <div style="margin-top: 4px;">
                            <span class="label">Modalidad</span><br>
                            Retiro en agencia destino
                        </div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="box">
        <div class="section-title">Remitente</div>
        <div class="value">{{ $package->sender_name }}</div>
        <div>C.I./RIF: {{ $package->sender_id_doc }} · Tel: {{ $package->sender_phone }}</div>
    </div>

    <div class="box">
        <div class="section-title">Destinatario</div>
        <div class="value">{{ $package->recipient_name }}</div>
        <div>C.I./RIF: {{ $package->recipient_id_doc }} · Tel: {{ $package->recipient_phone }}</div>
    </div>

    <table class="info">
        <tr>
            <td style="width: 33%;">
                <span class="label">Peso facturable</span><br>
                <span class="value">{{ number_format((float) $package->billable_weight_kg, 2) }} kg</span>
            </td>
            <td style="width: 33%;">
                <span class="label">Total</span><br>
                <span class="value">${{ number_format((float) $package->total_price_usd, 2) }}</span>
            </td>
            <td style="width: 34%;">
                <span class="label">Estado actual</span><br>
                <span class="value">{{ $package->statusLabel() }}</span>
            </td>
        </tr>
    </table>

    @if ($package->is_cod)
        <div class="box" style="margin-top: 6px;">
            <span class="label">Cobrar en destino (COD)</span><br>
            <span class="value">${{ number_format((float) $package->cod_amount_usd, 2) }}</span>
        </div>
    @endif

    <div class="footer">
        Verificación: {{ $package->security_hash }} ·
        Generado {{ now()->format('d/m/Y H:i') }} ·
        Rastrea tu envío en venexpress.com/rastreo
    </div>

</body>
</html>
