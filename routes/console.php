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
Schedule::command('bcv:sync')
    ->hourlyAt(5)
    ->withoutOverlapping();
