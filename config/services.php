<?php

return [

    'bcv_api' => [
        'url' => env('BCV_API_URL', 'https://ve.dolarapi.com/v1/dolares/oficial'),

        // Antigüedad máxima aceptada para la tasa BCV vigente antes de
        // bloquear cotizaciones (BcvRateService::getCurrentRate()), en
        // HORAS HÁBILES (BcvRateService::businessHoursAge() no cuenta
        // sábados/domingos, porque el BCV no publica esos días — un
        // fin de semana entero nunca cuenta como "atraso"). 48 horas
        // hábiles son 2 días hábiles completos sin ninguna
        // sincronización exitosa (bcv:sync corre cada 15 min entre
        // 1:30pm-6:30pm VET en días de semana, ver routes/console.php)
        // — bastante margen sobre una falla puntual, pero sin dejar
        // que el sistema siga cotizando con una tasa vieja por días.
        'max_age_hours' => env('BCV_MAX_RATE_AGE_HOURS', 48),
    ],

    'google_maps' => [
        // Places Autocomplete en el formulario de "Registrar pedido"
        // (dirección exacta de entrega). Null/vacío = el campo sigue
        // siendo texto libre, sin autocompletado ni coordenadas exactas.
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
