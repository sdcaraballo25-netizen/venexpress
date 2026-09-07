<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\PackageHistory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IncidentService
{
    public function __construct(
        protected PackageService $packageService,
    ) {
    }

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
     * Cambia el estado de una incidencia (RF panel Admin).
     *
     * Reglas aplicadas aquí, no solo en la interfaz:
     * - Solo un usuario administrativo (User::isAdmin()) puede
     *   ejecutar este cambio. No se confía en que la pantalla ya
     *   esté detrás de una ruta protegida.
     * - Una incidencia ya 'cerrada' solo puede volver a tocarla un
     *   Administrador Principal (User::isAdminPrincipal()).
     * - Se exige resolution_notes (nueva o ya existente) cuando el
     *   estado destino es 'resuelta' o 'cerrada'.
     * - Al volver a 'abierta' o 'en_proceso' se limpia resolved_at.
     * - Al pasar a 'cerrada' se conserva resolved_at si ya existía
     *   (por ejemplo, si la incidencia ya estaba 'resuelta').
     * - Todo el cambio ocurre dentro de una transacción con
     *   lockForUpdate() sobre la incidencia, y es idempotente: si la
     *   incidencia ya está en el estado solicitado (por ejemplo por
     *   una doble solicitud), no se generan auditorías ni eventos de
     *   historial duplicados.
     * - Se registra en AuditLog con la misma estructura que usa el
     *   resto del proyecto.
     * - Si la incidencia tiene guía asociada, se refleja el cambio
     *   en su historial mediante PackageService::registerScan() con
     *   el evento PackageHistory::EVENT_INCIDENCIA, sin inventar
     *   ubicación (ese evento no la requiere).
     *
     * @throws AuthorizationException si el actor no tiene permiso.
     * @throws RuntimeException si el estado es inválido o la
     *         transición no cumple las reglas de negocio.
     */
    public function updateStatus(
        Incident $incident,
        string $status,
        User $actor,
        ?string $resolutionNotes = null,
    ): Incident {
        if (! $actor->isAdmin()) {
            throw new AuthorizationException(
                'No tienes permiso para modificar incidencias.'
            );
        }

        if (! in_array($status, Incident::STATUSES, true)) {
            throw new RuntimeException('Estado de incidencia inválido.');
        }

        return DB::transaction(function () use ($incident, $status, $actor, $resolutionNotes) {
            /** @var Incident $locked */
            $locked = Incident::query()
                ->whereKey($incident->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotencia: si una doble solicitud (doble clic,
            // reintento de red) llega a procesarse dos veces, la
            // segunda no debe generar auditoría ni historial extra.
            if ($locked->status === $status) {
                return $locked;
            }

            // Una incidencia cerrada no se modifica de forma
            // arbitraria: solo un Administrador Principal puede
            // reabrirla o volver a tocarla.
            if ($locked->status === Incident::STATUS_CLOSED && ! $actor->isAdminPrincipal()) {
                throw new RuntimeException(
                    'Solo un Administrador Principal puede modificar una incidencia cerrada.'
                );
            }

            $previousStatus = $locked->status;

            $trimmedNotes = $resolutionNotes !== null ? trim($resolutionNotes) : null;

            // Notas finales que tendría la incidencia tras este
            // cambio: las nuevas si vienen, o las que ya tenía.
            $finalNotes = ($trimmedNotes !== null && $trimmedNotes !== '')
                ? $trimmedNotes
                : $locked->resolution_notes;

            if (
                in_array($status, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)
                && trim((string) $finalNotes) === ''
            ) {
                throw new RuntimeException(
                    'Debes indicar las notas de resolución.'
                );
            }

            $payload = ['status' => $status];

            if (in_array($status, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)) {
                // Conserva la fecha de resolución original si la
                // incidencia ya había sido resuelta antes de cerrarse.
                $payload['resolved_at'] = $locked->resolved_at ?? now();
            } else {
                // abierta / en_proceso: una incidencia no resuelta no
                // debe conservar una fecha de resolución.
                $payload['resolved_at'] = null;
            }

            if ($trimmedNotes !== null && $trimmedNotes !== '') {
                $payload['resolution_notes'] = $trimmedNotes;
            }

            $locked->update($payload);

            AuditLog::create([
                'actor_user_id' => $actor->id,
                'action' => 'incident.status_updated',
                'target_type' => Incident::class,
                'target_id' => $locked->id,
                'description' => sprintf(
                    'Cambió el estado de la incidencia #%d de "%s" a "%s".',
                    $locked->id,
                    $previousStatus,
                    $status
                ),
                'metadata' => [
                    'previous_status' => $previousStatus,
                    'status' => $status,
                    'package_id' => $locked->package_id,
                ],
                'ip_address' => request()?->ip(),
            ]);

            if ($locked->package_id && $locked->package) {
                // No se inventa ubicación: EVENT_INCIDENCIA no la
                // requiere, así que se dejan esos parámetros en su
                // valor por defecto (null).
                $this->packageService->registerScan(
                    package: $locked->package,
                    eventType: PackageHistory::EVENT_INCIDENCIA,
                    userId: $actor->id,
                );
            }

            return $locked->fresh();
        });
    }
}
