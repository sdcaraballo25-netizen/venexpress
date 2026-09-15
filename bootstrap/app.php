<?php

use App\Http\Middleware\EnsureAccountIsApproved;
use App\Http\Middleware\EnsureAccountIsVerified;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Consultamos periódicamente la API. Solo se guarda una nueva fila
        // cuando la tasa realmente cambia, por lo que se conservan las dos
        // tasas diarias del BCV sin sobrescribir la anterior.
        $schedule->command('bcv:sync')->everyThirtyMinutes();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'account.verified' => EnsureAccountIsVerified::class,
            'account.approved' => EnsureAccountIsApproved::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);

        // Si un invitado intenta entrar a /admin/* sin sesión, lo mandamos
        // al login privado de admin en vez del login público general.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*')
            ? route('admin.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 419 "Página caducada": el token CSRF expira junto con la
        // sesión (SESSION_LIFETIME, 120 min por defecto). Sin este
        // handler, un formulario enviado desde una pestaña dejada
        // abierta (ej. el botón "Salir") muestra la página de error
        // genérica de Laravel en vez de mandar al usuario a iniciar
        // sesión de nuevo con un mensaje claro.
        //
        // IMPORTANTE: Handler::prepareException() de Laravel convierte
        // TokenMismatchException en un HttpException(419) genérico
        // ANTES de despachar a los callbacks de render() registrados
        // aquí — un type-hint contra TokenMismatchException nunca
        // coincide. Por eso se captura HttpException y se filtra por
        // código de estado en vez de por la clase original.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return null;
            }

            return redirect()
                ->route($request->is('admin*') ? 'admin.login' : 'login')
                ->with('status', 'Tu sesión expiró por inactividad. Inicia sesión de nuevo.');
        });
    })->create();
