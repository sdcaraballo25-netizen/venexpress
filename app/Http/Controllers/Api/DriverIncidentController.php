<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncidentResource;
use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverIncidentController extends Controller
{
    /**
     * Motivos disponibles para el repartidor al reportar un problema
     * de entrega. 'type' en el modelo Incident es un string libre
     * (no hay una lista fija en base de datos), así que este set es
     * una propuesta razonable pensada para el flujo de reparto. Si
     * tu panel de admin (IncidentsManager) espera valores distintos
     * para que los reportes/filtros cuadren, avísame para ajustarlo.
     */
    public const TYPES = [
        'CLIENTE_AUSENTE',
        'DIRECCION_INCORRECTA',
        'PAQUETE_DANADO',
        'RECHAZADO_POR_CLIENTE',
        'OTRO',
    ];

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

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:' . implode(',', self::TYPES)],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        $package = Package::query()
            ->where('driver_id', $driver->id)
            ->findOrFail($packageId);

        $incident = Incident::create([
            'ally_id' => $package->ally_id,
            'package_id' => $package->id,
            'reported_by_user_id' => Auth::id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'status' => Incident::STATUS_OPEN,
        ]);

        AuditLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'incident.reported_by_driver',
            'target_type' => Incident::class,
            'target_id' => $incident->id,
            'description' => "El repartidor reportó una incidencia en la guía {$package->tracking_number}: {$validated['type']}.",
            'metadata' => [
                'package_id' => $package->id,
                'type' => $validated['type'],
            ],
            'ip_address' => $request->ip(),
        ]);

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
