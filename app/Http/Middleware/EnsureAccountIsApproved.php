<?php

namespace App\Http\Middleware;

use App\Models\Ally;
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

        $status = match (true) {
            $user->isAliado() => $user->ally?->status,
            $user->isAliadoTaquilla() => $user->alliedAgency?->status,
            $user->isRepartidor() => $user->driver?->status,
            default => null,
        };

        if ($status !== null && $status !== Ally::STATUS_ACTIVE) {
            return redirect()->route('account.pending');
        }

        return $next($request);
    }
}
