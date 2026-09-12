<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Models\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DriverRouteController extends Controller
{
    protected function driver()
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        return $driver;
    }

    /**
     * Ruta asignada actualmente al repartidor (asignada o en curso).
     * Mismo criterio que usa Dashboard.php del portal web.
     */
    public function active(): JsonResponse
    {
        $driver = $this->driver();

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', [Route::STATUS_ASSIGNED, Route::STATUS_IN_PROGRESS])
            ->with(['stops.ally.user'])
            ->latest('created_at')
            ->first();

        if (! $route) {
            return response()->json([
                'route' => null,
                'message' => 'No tienes una ruta asignada por el momento.',
            ]);
        }

        return response()->json([
            'route' => new RouteResource($route),
        ]);
    }

    /**
     * Inicia la ruta asignada. Lógica idéntica a
     * App\Livewire\Driver\Dashboard::startRoute(), solo que
     * devolviendo JSON en vez de un flash de sesión.
     */
    public function start(): JsonResponse
    {
        $driver = $this->driver();

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_ASSIGNED)
            ->latest('created_at')
            ->first();

        if (! $route) {
            return response()->json([
                'message' => 'No tienes una ruta asignada pendiente de iniciar.',
            ], 422);
        }

        $route->update([
            'status' => Route::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return response()->json([
            'message' => 'Ruta iniciada correctamente. Ya puedes comenzar a escanear paquetes.',
            'route' => new RouteResource($route->fresh(['stops.ally.user'])),
        ]);
    }
}
