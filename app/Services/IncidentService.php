<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\Incident;

class IncidentService
{
    /**
     * Registra una nueva incidencia/reclamo para una agencia aliada
     * (RF-ALI-06). La reporta un User (Aliado Administrador o Aliado
     * Taquilla, según su role).
     *
     * @param array{
     *   package_id?:int|null, type:string, description:string,
     *   reported_by_user_id?:int|null,
     * } $data
     */
    public function create(Ally $ally, array $data): Incident
    {
        return $ally->incidents()->create([
            'package_id' => $data['package_id'] ?? null,
            'reported_by_user_id' => $data['reported_by_user_id'] ?? null,
            'type' => $data['type'],
            'description' => $data['description'],
            'status' => Incident::STATUS_OPEN,
        ]);
    }

    /**
     * Cambia el estado de una incidencia. Marca resolved_at
     * automáticamente al pasar a 'resuelta' o 'cerrada'.
     */
    public function updateStatus(Incident $incident, string $status, ?string $resolutionNotes = null): Incident
    {
        $payload = ['status' => $status];

        if (in_array($status, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)) {
            $payload['resolved_at'] = now();
        }

        if ($resolutionNotes !== null) {
            $payload['resolution_notes'] = $resolutionNotes;
        }

        $incident->update($payload);

        return $incident->fresh();
    }
}
