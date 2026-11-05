<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\Http\Middleware\EnsureAccountIsApproved;
use App\Http\Middleware\EnsureAccountIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use App\Mail\GmailApiTransport;
use App\Models\Package;
use App\Observers\PackageObserver;
use Illuminate\Database\Connection;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Connection::resolverFor('pgsql', fn ($pdo, $database, $prefix, $config) => new PostgresConnection($pdo, $database, $prefix, $config));
    }

    public function boot(): void
    {
        Package::observe(
            PackageObserver::class
        );

        Mail::extend('gmail', fn (array $config) => new GmailApiTransport(
            (string) $config['client_id'],
            (string) $config['client_secret'],
            (string) $config['refresh_token'],
        ));

        $this->warnIfNotificationsAreNotReallySent();

        $this->configureTrustedProxies();

        $this->configureUrlGeneration();

        // Todas las reglas de contraseña usan Password::defaults()
        // (registro, cambio, restablecimiento, cuentas creadas por un
        // Admin o un Aliado). En producción: mínimo 10 caracteres con
        // letras y números, y que no aparezca en filtraciones conocidas
        // (haveibeenpwned, por k-anonimato: no se envía la contraseña).
        Password::defaults(fn () => $this->app->environment('production')
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(8));

        // Livewire solo vuelve a aplicar en cada acción (wire:click,
        // etc.) los middleware marcados como persistentes. Sin esto,
        // un usuario desactivado, suspendido o al que se le cambió el
        // rol seguía ejecutando acciones desde una pestaña ya abierta
        // hasta que vencía su sesión (24 h).
        Livewire::addPersistentMiddleware([
            EnsureUserHasRole::class,
            EnsureAccountIsApproved::class,
            EnsureAccountIsVerified::class,
        ]);
    }

    /**
     * Ver config/app.php 'trusted_proxies'.
     */
    private function configureTrustedProxies(): void
    {
        $proxies = config('app.trusted_proxies');

        if (blank($proxies)) {
            return;
        }

        TrustProxies::at(
            $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies))
        );

        // SEGURIDAD: del proxy solo se aceptan IP, puerto y protocolo.
        // X-Forwarded-Host NO: con TRUSTED_PROXIES="*" cualquier visitante
        // podía mandarlo y Laravel armaba los enlaces (incluido el del
        // correo de "olvidé mi contraseña") con un dominio elegido por el
        // atacante, que así recibía el token de restablecimiento.
        TrustProxies::withHeaders(
            Request::HEADER_X_FORWARDED_FOR
            | Request::HEADER_X_FORWARDED_PORT
            | Request::HEADER_X_FORWARDED_PROTO
            | Request::HEADER_X_FORWARDED_AWS_ELB
        );
    }

    /**
     * En producción todos los enlaces (correos, redirecciones, assets)
     * se generan con APP_URL y nunca con el Host que mande el visitante:
     * segunda barrera contra el envenenamiento de enlaces de
     * restablecimiento de contraseña (ver configureTrustedProxies()).
     */
    public function configureUrlGeneration(): void
    {
        $appUrl = (string) config('app.url');

        if (! $this->app->environment('production') || $appUrl === '') {
            return;
        }

        URL::forceRootUrl(rtrim($appUrl, '/'));

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
        }
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
                .'MAIL_MAILER="'.config('mail.default').'". '
                .'Las notificaciones de cambio de estado de paquetes no se están enviando '
                .'realmente a los clientes, solo se registran en el log.'
            );
        }
    }
}
