<?php

return [
    'driver_remuneration_usd' => (float) env('VENEXPRESS_DRIVER_REMUNERATION_USD', 1.00),

    /*
    |--------------------------------------------------------------------------
    | Canales de verificación de cuenta
    |--------------------------------------------------------------------------
    |
    | Canales por los que se envía el código de bienvenida al
    | registrarse. Hoy solo "mail" está implementado. "sms" y
    | "whatsapp" quedan preparados: una vez se contrate un proveedor
    | (Twilio, Meta Cloud API, etc.) y se implementen sus canales,
    | basta con agregarlos aquí vía .env, sin tocar código.
    |
    | Ejemplo futuro: VENEXPRESS_VERIFICATION_CHANNELS=mail,sms,whatsapp
    |
    */
    'account_verification_channels' => array_filter(array_map(
        'trim',
        explode(',', env('VENEXPRESS_VERIFICATION_CHANNELS', 'mail'))
    )),
];
