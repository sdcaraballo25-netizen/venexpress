<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Si un invitado intenta entrar a /admin/* sin sesión, lo mandamos
        // al login privado de admin en vez del login público general.
        $middleware->redirectGuestsTo(fn ($request) => $request->is('admin*')
            ? route('admin.login')
            : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();