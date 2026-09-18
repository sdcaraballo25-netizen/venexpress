<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sincroniza la tasa BCV automáticamente. El comando ya existía
// (app/Console/Commands/SyncBcvRate.php) pero nunca estaba programado,
// así que la tasa solo se actualizaba si un admin la cargaba a mano.
//
// Cada 15 minutos (en vez de cada hora) para que una falla puntual de
// la API del BCV (red, timeout, la propia API caída un rato) se
// autocorrija en minutos en el próximo intento, no en una hora. El
// propio comando ya evita crear una fila duplicada si la tasa no
// cambió (BcvRateService::syncFromApi()), así que correrlo más
// seguido no genera ruido en la tabla bcv_rates.
Schedule::command('bcv:sync')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
