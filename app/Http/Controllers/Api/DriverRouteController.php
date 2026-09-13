<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Models\Route;
use App\Services\RouteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

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
     * Rutas disponibles para que el Driver las tome. Fuente única de
     * verdad: RouteService::availableRoutesFor().
     */
    public function available(RouteService $routeService): JsonResponse
    {
        $driver = $this->driver();

        $routes = $routeService->availableRoutesFor($driver);

        return response()->json([
            'data' => RouteResource::collection($routes),
        ]);
    }

    /**
     * El Driver toma una ruta disponible. "Primero en tomar, primero
     * en repartir": si dos Drivers la intentan tomar casi al mismo
     * tiempo, solo el primero gana (ver RouteService::claimRoute()).
     */
    public function claim(int $routeId): JsonResponse
    {
        $driver = $this->driver();

        $route = Route::findOrFail($routeId);

        try {
            $route = app(RouteService::class)->claimRoute(
                route: $route,
                driver: $driver,
                actingUserId: (int) Auth::id(),
            );

            return response()->json([
                'message' => '¡Ruta tomada! Ya puedes iniciarla cuando estés listo.',
                'route' => new RouteResource($route),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
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
            ->with(['stops.ally.user', 'stops.warehouse'])
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

        $route = app(RouteService::class)->start(
            route: $route,
            actingUserId: (int) Auth::id(),
        );

        return response()->json([
            'message' => 'Ruta iniciada correctamente. Ya puedes comenzar a escanear paquetes.',
            'route' => new RouteResource($route->fresh(['stops.ally.user', 'stops.warehouse'])),
        ]);
    }

    /**
     * Finaliza la ruta en curso del repartidor. Reutiliza
     * RouteService::complete() sin modificarlo; solo valida que la
     * ruta pertenezca a este driver antes de llamarlo.
     */
    public function complete(): JsonResponse
    {
        $driver = $this->driver();

        $route = Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->latest('started_at')
            ->first();

        if (! $route) {
            return response()->json([
                'message' => 'No tienes una ruta en curso para finalizar.',
            ], 422);
        }

        try {
            $route = app(RouteService::class)->complete(
                route: $route,
                actingUserId: (int) Auth::id(),
            );

            return response()->json([
                'message' => 'Ruta finalizada correctamente.',
                'route' => new RouteResource($route->fresh(['stops.ally.user', 'stops.warehouse'])),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
