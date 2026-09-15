<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouteResource;
use App\Livewire\Driver\Support\HubDistributionPhase;
use App\Models\Driver;
use App\Models\Route;
use App\Models\RouteStop;
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
                'hub_scan' => null,
                'message' => 'No tienes una ruta asignada por el momento.',
            ]);
        }

        return response()->json([
            'route' => new RouteResource($route),
            'hub_scan' => $this->hubScanInfo($driver, $route),
        ]);
    }

    /**
     * Qué debe escanear ahora mismo un repartidor de HUB, para que el
     * dashboard de la app sea explícito ("Escanear recolección" /
     * "Escanear salida" / "Escanear recepción") en vez de un botón
     * genérico. Replica tal cual el bloque "ACCIÓN DE ESCANEO
     * PRINCIPAL (HUB)" de Livewire\Driver\Dashboard::render(), para
     * que el portal web y la app nunca se contradigan.
     */
    protected function hubScanInfo(Driver $driver, Route $route): ?array
    {
        if ($driver->driver_type !== Driver::TYPE_HUB || ! $route->isInProgress()) {
            return null;
        }

        $nextPendingStop = $route->stops->firstWhere('status', RouteStop::STATUS_PENDING);

        if ($route->route_type === Route::TYPE_HUB_TRANSFER) {
            return [
                'operation' => 'collection',
                'title' => 'RECOLECCIÓN EN ALIADO',
                'subtitle' => 'Aliado → HUB',
                'cta' => 'Escanear recolección',
                'pending_count' => null,
                'next_stop_name' => $nextPendingStop?->ally?->business_name,
                'warehouse_name' => null,
            ];
        }

        if ($route->route_type === Route::TYPE_HUB_DISTRIBUTION) {
            $operation = HubDistributionPhase::resolve($driver);

            if ($operation === HubDistributionPhase::ARRIVAL) {
                return [
                    'operation' => $operation,
                    'title' => 'RECEPCIÓN EN ALMACÉN',
                    'subtitle' => 'Llegada al almacén destino',
                    'cta' => 'Escanear recepción',
                    'pending_count' => HubDistributionPhase::pendingArrivalsCount($driver),
                    'next_stop_name' => null,
                    'warehouse_name' => $nextPendingStop?->warehouse?->name,
                ];
            }

            return [
                'operation' => $operation,
                'title' => 'SALIDA DESDE HUB',
                'subtitle' => 'HUB → Almacén destino',
                'cta' => 'Escanear salida',
                'pending_count' => HubDistributionPhase::pendingDepartureCount($route),
                'next_stop_name' => null,
                'warehouse_name' => null,
            ];
        }

        return null;
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
