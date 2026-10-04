<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A diferencia del middleware nativo `verified` de Laravel (que en este
 * proyecto no tiene efecto, porque User no implementa MustVerifyEmail),
 * este middleware exige el sistema propio de verificación por código de
 * VenExpress (users.account_verified_at) a los roles que se
 * autorregistran: Cliente, Aliado, Repartidor y Emprendedor (ver
 * User::requiresAccountVerification()). Admin, Taquilla y Almacén los
 * crea alguien de confianza y no pasan por aquí.
 *
 * Si alguien queda autenticado sin haber completado la verificación
 * (por ejemplo, cerró la pestaña justo después de registrarse), se le
 * manda de vuelta a la pantalla de verificación en vez de dejarlo
 * entrar a su panel. En la API del repartidor no hay pantalla a la que
 * redirigir: se responde 403 en JSON.
 */
class EnsureAccountIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->requiresAccountVerification() || $user->isAccountVerified()) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            abort(403, 'Debes verificar tu correo antes de operar. Ingresa al sitio web para completar la verificación.');
        }

        session(['pending_verification_user_id' => $user->id]);

        Auth::guard('web')->logout();

        return redirect()->route('verify-account');
    }
}
