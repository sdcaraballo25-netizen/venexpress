<?php

namespace App\Providers;

use App\Models\Package;
use App\Observers\PackageObserver;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Package::observe(
            PackageObserver::class
        );

        $this->warnIfNotificationsAreNotReallySent();
    }

    /**
     * Hallazgo de auditoría #4: con MAIL_MAILER=log en producción,
     * PackageStatusUpdated nunca llega realmente al cliente, solo se
     * escribe en el log. Esto no debe descubrirse por un reclamo de
     * un cliente; lo dejamos como una advertencia explícita en cada
     * arranque de la aplicación en producción.
     *
     * Ver también: comando `venexpress:check-production`, que hace
     * este mismo chequeo (y otros) bajo demanda como parte del
     * checklist de despliegue.
     */
    private function warnIfNotificationsAreNotReallySent(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            Log::warning(
                'Configuración de correo no apta para producción: '
                . 'MAIL_MAILER="' . config('mail.default') . '". '
                . 'Las notificaciones de cambio de estado de paquetes no se están enviando '
                . 'realmente a los clientes, solo se registran en el log.'
            );
        }
    }
}
