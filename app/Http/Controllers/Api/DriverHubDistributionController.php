<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPackageResource;
use App\Models\Driver;
use App\Models\Package;
use App\Services\LogisticsScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Escaneos del driver de HUB Distribución (HUB -> almacén propio de
 * Venexpress destino). No confundir con el driver de HUB Recolección
 * (DriverPackageController::scan) ni con la app de Delivery
 * (DriverDeliveryController) — son tres actores distintos.
 */
class DriverHubDistributionController extends Controller
{
    /**
     * Repartidor autenticado, exigiendo además que sea de tipo HUB.
     * La ruta ya está protegida por
     * ['auth:sanctum', 'ability:driver', 'role:repartidor'].
     */
    protected function driver(): Driver
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        if ($driver->driver_type !== Driver::TYPE_HUB) {
            abort(403, 'Esta función es solo para repartidores de HUB.');
        }

        return $driver;
    }

    protected function findPackageByTrackingNumber(string $trackingNumber): ?Package
    {
        return Package::query()
            ->where('tracking_number', trim($trackingNumber))
            ->with(['ally', 'driver', 'histories'])
            ->first();
    }

    /**
     * Escanea la salida de un paquete del HUB hacia el almacén
     * destino, dentro de una ruta de distribución en curso.
     */
    public function departFromHub(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $driver = $this->driver();

        $package = $this->findPackageByTrackingNumber($validated['tracking_number']);

        if (! $package) {
            return response()->json([
                'message' => "No existe una guía con número: {$validated['tracking_number']}",
            ], 404);
        }

        $scanService = app(LogisticsScanService::class);
        $canAccess = $scanService->canDriverAccessPackage($package, $driver);

        try {
            $package = $scanService->scanHubDeparture(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => 'Salida de HUB registrada correctamente. El paquete quedó en tránsito nacional.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            // No se filtra la PII del paquete (remitente/destinatario)
            // en una respuesta de error a menos que el paquete
            // realmente le toque a este repartidor — el mensaje de
            // error sigue siendo el mismo que antes.
            return response()->json(array_filter([
                'message' => $e->getMessage(),
                'package' => $canAccess ? new DriverPackageResource($package) : null,
            ], fn ($v) => $v !== null), 422);
        }
    }

    /**
     * Escanea la llegada de un paquete al almacén propio de
     * Venexpress destino. No cambia el estado del paquete (sigue
     * EN_TRANSITO_NACIONAL) para no interferir con la app de Delivery.
     */
    public function arriveAtDestination(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $driver = $this->driver();

        $package = $this->findPackageByTrackingNumber($validated['tracking_number']);

        if (! $package) {
            return response()->json([
                'message' => "No existe una guía con número: {$validated['tracking_number']}",
            ], 404);
        }

        $scanService = app(LogisticsScanService::class);
        $canAccess = $scanService->canDriverAccessPackage($package, $driver);

        try {
            $package = $scanService->scanHubArrival(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => 'Llegada al almacén destino registrada correctamente.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(array_filter([
                'message' => $e->getMessage(),
                'package' => $canAccess ? new DriverPackageResource($package) : null,
            ], fn ($v) => $v !== null), 422);
        }
    }
}
