<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A diferencia de EnsureUserHasRole (que solo valida el rol y si el
 * User está activo/inactivo), este middleware valida que Aliado,
 * Repartidor o Emprendedor puedan operar realmente. Desde que existe
 * verification_status (verificación documental, separada de
 * status/estado operativo), "puede operar" exige ambas cosas:
 * verification_status === VERIFICADO Y status === ACTIVO (ver
 * Ally::canOperate() / Driver::canOperate() / Emprendedor::canOperate()).
 * Se aplica DESPUÉS de 'role:' en las rutas de aliado, repartidor y
 * emprendedor.
 */
class EnsureAccountIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        /*
         * Si el rol exige un Ally/Driver/Emprendedor asociado y no lo
         * tiene (ej. un registro que falló a mitad de camino antes de
         * que existiera la transacción en register.blade.php), el
         * operador de Elvis (?->canOperate()) devuelve null, que el
         * cast a bool vuelve false: sin esto, un User huérfano
         * quedaba con acceso libre a las rutas de aliado/repartidor
         * porque no había nada que comparar.
         */
        $canOperate = match (true) {
            $user->isAliado() => (bool) $user->ally?->canOperate(),
            $user->isAliadoTaquilla() => (bool) $user->alliedAgency?->canOperate(),
            $user->isRepartidor() => (bool) $user->driver?->canOperate(),
            $user->isEmprendedor() => (bool) $user->emprendedor?->canOperate(),
            default => true,
        };

        if (! $canOperate) {
            // API de la app del repartidor: un token emitido antes de
            // una suspensión/rechazo no debe seguir operando, y la app
            // necesita un error JSON, no una redirección HTML.
            if ($request->expectsJson() || $request->is('api/*')) {
                abort(403, 'Tu cuenta no está habilitada para operar. Contacta al administrador.');
            }

            return redirect()->route('account.pending');
        }

        return $next($request);
    }
}
