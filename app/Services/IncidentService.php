<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AuditLog;
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
    public function updateStatus(Incident $incident, string $status, ?string $resolutionNotes = null, ?int $actorUserId = null): Incident
    {
        if (! in_array($status, Incident::STATUSES, true)) {
            throw new \RuntimeException('Estado de incidencia inválido.');
        }

        $payload = ['status' => $status];

        if (in_array($status, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)) {
            $payload['resolved_at'] = now();
        }

        if ($resolutionNotes !== null) {
            $payload['resolution_notes'] = $resolutionNotes;
        }

        $incident->update($payload);

        if ($actorUserId) {
            AuditLog::create([
                'actor_user_id' => $actorUserId,
                'action' => 'incident.status_updated',
                'target_type' => Incident::class,
                'target_id' => $incident->id,
                'description' => 'Actualizó el estado de una incidencia.',
                'metadata' => [
                    'status' => $status,
                    'package_id' => $incident->package_id,
                ],
                'ip_address' => request()?->ip(),
            ]);
        }

        return $incident->fresh();
    }
}
