<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Incident;
use App\Models\Package;
use App\Models\User;
use App\Notifications\MisroutedPackageAlert;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Alerta cuando un paquete se escanea en un destino equivocado (un
 * almacén o una agencia que no le corresponde): deja una incidencia
 * abierta para que Admin corrija el envío desde Incidencias, una
 * entrada en la bitácora y un correo a los administradores.
 *
 * No cambia el paquete: la recepción ya fue rechazada por quien llama.
 * Si ya hay una alerta abierta para la misma guía no se duplica (el
 * mismo paquete se puede escanear varias veces seguidas).
 */
class MisroutedPackageAlertService
{
    public const INCIDENT_TYPE = 'DESTINO_EQUIVOCADO';

    public function report(Package $package, string $scannedAt, ?int $userId, ?string $expectedDestination = null): ?Incident
    {
        $alreadyOpen = Incident::query()
            ->where('package_id', $package->id)
            ->where('type', self::INCIDENT_TYPE)
            ->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_IN_PROGRESS])
            ->exists();

        if ($alreadyOpen) {
            return null;
        }

        $description = "La guía {$package->tracking_number} se escaneó en {$scannedAt}, que no es su destino"
            .($expectedDestination ? " (debía llegar a {$expectedDestination})" : '')
            .'. Revisa a dónde se envió y corrige el traslado.';

        $incident = Incident::create([
            'ally_id' => $package->ally_id,
            'package_id' => $package->id,
            'reported_by_user_id' => $userId,
            'type' => self::INCIDENT_TYPE,
            'description' => $description,
            'status' => Incident::STATUS_OPEN,
        ]);

        AuditLog::create([
            'actor_user_id' => $userId,
            'action' => 'package.misrouted_scan',
            'target_type' => Package::class,
            'target_id' => $package->id,
            'description' => $description,
            'metadata' => [
                'tracking_number' => $package->tracking_number,
                'scanned_at' => $scannedAt,
                'expected_destination' => $expectedDestination,
                'incident_id' => $incident->id,
            ],
            'ip_address' => request()?->ip(),
        ]);

        $this->notifyAdmins($package, $scannedAt, $expectedDestination);

        return $incident;
    }

    protected function notifyAdmins(Package $package, string $scannedAt, ?string $expectedDestination): void
    {
        try {
            User::query()
                ->whereIn('role', [User::ROLE_ADMIN_PRINCIPAL, User::ROLE_ADMIN_OPERATIVO])
                ->where('status', User::STATUS_ACTIVE)
                ->get()
                ->each(fn (User $admin) => $admin->notify(
                    new MisroutedPackageAlert($package->id, $scannedAt, $expectedDestination)
                ));
        } catch (Throwable $e) {
            // Un fallo del correo nunca debe ocultar la incidencia ya creada.
            Log::warning('No se pudo avisar a los administradores de un paquete en destino equivocado.', [
                'package_id' => $package->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
