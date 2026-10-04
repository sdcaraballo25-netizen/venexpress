<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPackageResource;
use App\Models\Driver;
use App\Models\Package;
use App\Services\GeocodingService;
use App\Services\LogisticsScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DriverDeliveryController extends Controller
{
    /**
     * Ordena los pedidos YA reclamados por este repartidor (pendientes
     * de entregar) de más lejos a más cerca de su ubicación GPS
     * actual. Geocodifica de una vez (síncrono, dentro de esta misma
     * petición) las direcciones que todavía no tengan coordenadas
     * guardadas, para que el repartidor no dependa de que un worker
     * de colas esté corriendo en ese momento.
     */
    public function routeOrder(Request $request, GeocodingService $geocoding): JsonResponse
    {
        $driver = $this->driver();

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $packages = Package::query()
            ->where('driver_id', $driver->id)
            ->where('requires_delivery', true)
            ->where('current_status', Package::STATUS_EN_TRANSITO_NACIONAL)
            ->get();

        $notGeocodedYet = [];
        $liveGeocodeCalls = 0;

        $stops = $packages->map(function (Package $package) use (&$notGeocodedYet, &$liveGeocodeCalls, $geocoding, $validated) {
            if ($package->delivery_latitude === null || $package->delivery_longitude === null) {
                // Respetamos la política de uso justo de Nominatim
                // (~1 petición/segundo) también aquí: si este mismo
                // repartidor tiene varios paquetes sin geocodificar,
                // espaciamos las consultas en vivo entre sí.
                if ($liveGeocodeCalls > 0) {
                    sleep(1);
                }
                $liveGeocodeCalls++;

                if (! $geocoding->geocodePackageDeliveryAddress($package)) {
                    // Nominatim no encontró la dirección o la consulta
                    // falló transitoriamente: encolamos un reintento en
                    // segundo plano y lo excluimos de esta respuesta.
                    \App\Jobs\GeocodePackageDeliveryAddress::dispatch($package->id);
                    $notGeocodedYet[] = $package->tracking_number;
                    return null;
                }
            }

            return [
                'package' => $package,
                'distance_km' => GeocodingService::haversineKm(
                    (float) $validated['latitude'],
                    (float) $validated['longitude'],
                    (float) $package->delivery_latitude,
                    (float) $package->delivery_longitude,
                ),
            ];
        })->filter()->sortByDesc('distance_km')->values();

        return response()->json([
            'stops' => $stops->map(fn ($stop, $index) => [
                'order' => $index + 1,
                'distance_km' => $stop['distance_km'],
                'package' => new DriverPackageResource($stop['package']),
            ]),
            // Direcciones que Nominatim no pudo ubicar en el momento
            // (quedaron encoladas para reintentar en segundo plano).
            // El repartidor las sigue viendo en su lista normal, solo
            // que sin orden por distancia todavía.
            'pending_location' => $notGeocodedYet,
        ]);
    }

    protected function driver(): Driver
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        if ($driver->driver_type !== Driver::TYPE_DELIVERY) {
            abort(403, 'Esta función es solo para repartidores de entrega final.');
        }

        return $driver;
    }

    /**
     * Antes devolvía a cualquier repartidor de entrega TODOS los
     * pedidos reclamables del país, con nombre, cédula, teléfono y
     * dirección de remitente y destinatario. Ya no existe una lista
     * global de paquetes para elegir: un repartidor solo opera lo que
     * le asignaron (su ruta) o la guía que escanea físicamente
     * (claimByScan), y es el backend quien decide si puede tomarla.
     *
     * Se conserva el endpoint con la misma forma de respuesta (lista
     * vacía) para no romper versiones de la app que todavía lo llamen.
     */
    public function available(): JsonResponse
    {
        $this->driver();

        return response()->json([
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
            ],
            'message' => 'Escanea la guía del paquete para tomar una entrega.',
        ]);
    }

    /**
     * Reclama un pedido escaneando su QR directamente (en vez de
     * tocar "Reclamar" desde la lista de disponibles). Es el flujo
     * principal esperado: el repartidor llega al almacén y escanea.
     */
    public function claimByScan(\Illuminate\Http\Request $request): JsonResponse
    {
        $driver = $this->driver();

        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
        ]);

        $package = Package::query()
            ->where('tracking_number', trim($validated['tracking_number']))
            ->first();

        if (! $package) {
            return response()->json([
                'message' => "No existe una guía con número: {$validated['tracking_number']}",
            ], 404);
        }

        try {
            // Misma decisión que el escáner del panel web
            // (Livewire\Driver\Scanner): ruta propia, paquete ya suyo,
            // o entrega individual sin ruta vía claimForDelivery().
            [$result, $package] = app(LogisticsScanService::class)->scanForDelivery(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => match ($result) {
                    LogisticsScanService::DELIVERY_SCAN_COLLECTION => 'Recolección registrada en tu ruta.',
                    LogisticsScanService::DELIVERY_SCAN_ASSIGNED => 'Este pedido ya está asignado a ti.',
                    default => '¡Pedido reclamado! Ya es tuyo para entregar.',
                },
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            // Sin datos del paquete: aún no se validó que le corresponda.
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reclamar por ID ya no está permitido: los IDs son secuenciales,
     * así que cualquiera podía recorrerlos y quedarse con pedidos (y
     * sus datos personales) sin tener el paquete en la mano. Tomar una
     * entrega individual se hace escaneando la guía (claimByScan), que
     * aplica las mismas validaciones de PackageService::claimForDelivery().
     *
     * Se conserva la ruta para que una versión anterior de la app
     * reciba un mensaje claro en vez de un 404.
     */
    public function claim(int $packageId): JsonResponse
    {
        $this->driver();

        return response()->json([
            'message' => 'Para tomar una entrega, escanea la guía del paquete.',
        ], 422);
    }
}
