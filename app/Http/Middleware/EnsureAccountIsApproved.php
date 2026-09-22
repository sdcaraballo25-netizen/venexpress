<?php

namespace App\Http\Middleware;

use App\Models\Ally;
use App\Models\Emprendedor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A diferencia de EnsureUserHasRole (que solo valida el rol y si el
 * User está activo/inactivo), este middleware valida el estado de
 * aprobación específico de Aliado o Repartidor (Ally::status /
 * Driver::status: PENDIENTE/ACTIVO/RECHAZADO/SUSPENDIDO), controlado
 * por un admin desde el panel. Se aplica DESPUÉS de 'role:' en las
 * rutas de aliado y repartidor.
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
         * Si el rol exige un Ally/Driver asociado y no lo tiene (ej.
         * un registro que falló a mitad de camino antes de que
         * existiera la transacción en register.blade.php), tratamos
         * eso como PENDIENTE en vez de dejarlo pasar: sin esto, un
         * User huérfano quedaba con acceso libre a las rutas de
         * aliado/repartidor porque $status era null.
         */
        $status = match (true) {
            $user->isAliado() => $user->ally?->status ?? Ally::STATUS_PENDING,
            $user->isAliadoTaquilla() => $user->alliedAgency?->status ?? Ally::STATUS_PENDING,
            $user->isRepartidor() => $user->driver?->status ?? Ally::STATUS_PENDING,
            $user->isEmprendedor() => $user->emprendedor?->status ?? Emprendedor::STATUS_PENDING,
            default => null,
        };

        if ($status !== null && $status !== Ally::STATUS_ACTIVE) {
            return redirect()->route('account.pending');
        }

        return $next($request);
    }
}
