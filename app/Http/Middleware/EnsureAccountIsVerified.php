<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A diferencia del middleware nativo `verified` de Laravel (que este
 * proyecto no usa, porque User no implementa MustVerifyEmail), este
 * middleware protege el panel de Cliente usando el sistema propio de
 * verificación por código de VenExpress (users.account_verified_at).
 *
 * Si alguien queda autenticado sin haber completado la verificación
 * (por ejemplo, cerró la pestaña justo después de registrarse), se
 * le manda de vuelta a la pantalla de verificación en vez de dejarlo
 * entrar al dashboard.
 */
class EnsureAccountIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAccountVerified()) {
            session(['pending_verification_user_id' => $user->id]);

            \Illuminate\Support\Facades\Auth::logout();

            return redirect()->route('verify-account');
        }

        return $next($request);
    }
}
