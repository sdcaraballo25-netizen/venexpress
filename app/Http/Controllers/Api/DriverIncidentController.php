<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncidentResource;
use App\Models\Driver;
use App\Models\Package;
use App\Services\IncidentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverIncidentController extends Controller
{
    /**
     * Motivos disponibles para el repartidor al reportar un problema
     * de entrega. La lista vive en IncidentService::DRIVER_TYPES para
     * que la app y el panel web del repartidor usen exactamente la
     * misma; se conserva esta constante por compatibilidad.
     */
    public const TYPES = IncidentService::DRIVER_TYPES;

    protected function driver()
    {
        $driver = Auth::user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        return $driver;
    }

    /**
     * Registra una incidencia sobre un pedido asignado a este
     * repartidor. No cambia el estado del paquete — solo deja
     * constancia para que el admin la gestione desde IncidentsManager,
     * igual que las que reportan los Aliados.
     */
    public function store(Request $request, int $packageId): JsonResponse
    {
        $driver = $this->driver();

        if ($driver->status !== Driver::STATUS_ACTIVE) {
            abort(403, 'Solo un repartidor activo puede reportar incidencias.');
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', self::TYPES)],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        $incident = app(IncidentService::class)->reportByDriver(
            package: $package,
            driver: $driver,
            type: $validated['type'],
            description: $validated['description'],
            userId: (int) Auth::id(),
        );

        return response()->json([
            'message' => 'Incidencia reportada correctamente. El equipo administrativo la revisará.',
            'incident' => new IncidentResource($incident),
        ], 201);
    }

    /**
     * Lista las incidencias reportadas sobre un pedido asignado a
     * este repartidor (para ver si ya se resolvió, por ejemplo).
     */
    public function index(int $packageId): JsonResponse
    {
        $driver = $this->driver();

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        $incidents = $package->incidents()->latest()->get();

        return response()->json([
            'data' => IncidentResource::collection($incidents),
        ]);
    }
}
