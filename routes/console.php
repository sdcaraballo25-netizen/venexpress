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
// El BCV publica la tasa oficial una vez al día, solo en días
// hábiles bancarios (lunes a viernes; nunca fines de semana ni
// feriados, cuando simplemente sigue vigente la última publicada) —
// según fuentes públicas, en algún momento entre la 1:30pm y las
// 6:30pm hora de Venezuela, sin un minuto exacto fijo (varía día a
// día). En vez de adivinar una sola hora y arriesgarnos a que ese día
// el BCV publique más tarde, consultamos cada 15 minutos pero SOLO
// dentro de esa ventana y SOLO en días de semana: así se detecta la
// publicación real del día a los pocos minutos de ocurrir, sin
// martillar la API el resto del día ni los fines de semana, cuando no
// hay nada nuevo que buscar.
Schedule::command('bcv:sync')
    ->weekdays()
    ->everyFifteenMinutes()
    ->between('13:30', '18:30')
    ->timezone('America/Caracas')
    ->withoutOverlapping();
