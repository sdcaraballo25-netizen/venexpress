<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Hallazgo de auditoría #4: el .env por defecto trae
 * MAIL_MAILER=log, lo que significa que PackageStatusUpdated (la
 * notificación de cambio de estado) no envía ningún correo real,
 * solo lo escribe en el log. Es correcto para desarrollo local, pero
 * si se despliega así a producción el sistema "vende" notificaciones
 * de envío que en realidad nunca llegan al cliente.
 *
 * Este comando centraliza ese y otros chequeos de salida a
 * producción para que sea un paso explícito del checklist de
 * despliegue, en vez de un detalle que se descubre después de que
 * un cliente reclama que nunca recibió su correo.
 */
class CheckProductionReadiness extends Command
{
    protected $signature = 'venexpress:check-production';

    protected $description = 'Valida que la configuración actual sea segura para un despliegue en producción';

    public function handle(): int
    {
        $problems = [];
        $warnings = [];

        if (! app()->environment('production')) {
            $this->warn(
                'APP_ENV no es "production" (actual: ' . app()->environment() . '). '
                . 'Este chequeo es más útil ejecutado con la configuración real de producción.'
            );
        }

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $problems[] = sprintf(
                'MAIL_MAILER="%s": las notificaciones de cambio de estado (PackageStatusUpdated) '
                . 'NO se envían por correo real, solo quedan en el log. Los clientes no recibirán '
                . 'notificaciones de sus envíos.',
                config('mail.default')
            );
        }

        if (config('app.debug') === true) {
            $problems[] = 'APP_DEBUG=true: expone trazas de error detalladas a cualquier visitante.';
        }

        if (config('app.env') === 'local' || config('app.env') === 'testing') {
            $warnings[] = 'APP_ENV="' . config('app.env') . '": revisa que esto sea intencional antes de desplegar.';
        }

        if (config('session.driver') === 'array') {
            $warnings[] = 'SESSION_DRIVER="array": las sesiones no persisten entre peticiones/procesos.';
        }

        if (config('queue.default') === 'sync') {
            $warnings[] = 'QUEUE_CONNECTION="sync": los correos y trabajos en cola se ejecutan de forma síncrona, sin reintentos ante fallos.';
        }

        if (in_array(config('queue.default'), ['database', 'redis'], true)) {
            $warnings[] = sprintf(
                'QUEUE_CONNECTION="%s": los jobs (ej. notificaciones de PackageStatusUpdated) '
                . 'solo se procesan si hay un worker corriendo (`php artisan queue:work`, '
                . 'normalmente bajo Supervisor). Este comando no puede verificar si ese '
                . 'proceso está activo — confírmalo manualmente en el servidor.',
                config('queue.default')
            );
        }

        $lastBcvRate = \App\Models\BcvRate::query()->latest('created_at')->first();

        if ($lastBcvRate === null) {
            $warnings[] = 'Nunca se ha registrado una tasa BCV: revisa si `bcv:sync` corrió alguna vez.';
        } elseif ($lastBcvRate->created_at->lt(now()->subHours(6))) {
            $warnings[] = sprintf(
                'La última tasa BCV registrada es de %s (hace más de 6 horas). '
                . 'bcv:sync está programado cada hora (routes/console.php) pero eso solo '
                . 'funciona si el cron del scheduler está configurado en el servidor '
                . '(`* * * * * php artisan schedule:run`). Verifica el crontab.',
                $lastBcvRate->created_at->diffForHumans()
            );
        }

        foreach ($warnings as $warning) {
            $this->warn('⚠ ' . $warning);
        }

        if (empty($problems)) {
            $this->info('✓ No se detectaron bloqueantes de salida a producción en la configuración actual.');

            return self::SUCCESS;
        }

        $this->error('Se encontraron problemas que deben resolverse antes de salir a producción:');

        foreach ($problems as $problem) {
            $this->line(' - ' . $problem);
        }

        return self::FAILURE;
    }
}
