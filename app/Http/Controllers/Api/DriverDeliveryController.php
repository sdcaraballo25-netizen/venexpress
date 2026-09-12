<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverPackageResource;
use App\Models\Driver;
use App\Models\Package;
use App\Services\GeocodingService;
use App\Services\PackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class DriverDeliveryController extends Controller
{
    /**
     * Ordena los pedidos YA reclamados por este repartidor (pendientes
     * de entregar) de más lejos a más cerca de su ubicación GPS
     * actual. Geocodifica bajo demanda (y cachea) las direcciones que
     * todavía no tengan coordenadas guardadas.
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

        $stops = $packages->map(function (Package $package) use (&$notGeocodedYet) {
            if ($package->delivery_latitude === null || $package->delivery_longitude === null) {
                // No bloqueamos la respuesta geocodificando en vivo —
                // eso sería un cuello de botella si muchos repartidores
                // piden su ruta al mismo tiempo a nivel nacional. En
                // vez de eso, nos aseguramos de que ya esté encolado
                // (por si el job original falló) y lo excluimos de
                // esta respuesta.
                \App\Jobs\GeocodePackageDeliveryAddress::dispatch($package->id);
                $notGeocodedYet[] = $package->tracking_number;
                return null;
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
            // Direcciones que aún se están geocodificando en segundo
            // plano (o que no se pudieron ubicar). El repartidor las
            // sigue viendo en su lista normal, solo que sin orden por
            // distancia todavía — reintentar en unos segundos suele
            // resolverlo.
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
     * Pedidos disponibles para reclamar: ya están en tránsito
     * nacional, requieren entrega a domicilio, y nadie los ha
     * tomado todavía. Visible para CUALQUIER repartidor de entrega
     * activo, sin importar su ubicación.
     */
    public function available(): JsonResponse
    {
        $this->driver();

        $packages = Package::query()
            ->availableForDeliveryClaim()
            ->with('ally')
            ->orderBy('created_at')
            ->paginate(20);

        return response()->json([
            'data' => DriverPackageResource::collection($packages->items()),
            'meta' => [
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'total' => $packages->total(),
            ],
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
            $package = app(PackageService::class)->claimForDelivery(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => '¡Pedido reclamado! Ya es tuyo para entregar.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Reclama un pedido disponible. "Primero en escanear, primero en
     * repartir" — si dos repartidores lo intentan casi al mismo
     * tiempo, solo el primero gana (ver PackageService::claimForDelivery).
     */
    public function claim(int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $package = Package::query()->findOrFail($packageId);

        try {
            $package = app(PackageService::class)->claimForDelivery(
                package: $package,
                driver: $driver,
                userId: (int) Auth::id(),
            );

            return response()->json([
                'message' => '¡Pedido reclamado! Ya es tuyo para entregar.',
                'package' => new DriverPackageResource($package),
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
