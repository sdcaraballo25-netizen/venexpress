<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DeliveryAssignmentService
{
    public function assign(Package $package, Route $route, int $userId): Package
    {
        return DB::transaction(function () use ($package, $route, $userId) {
            $lockedPackage = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedRoute = Route::query()
                ->with('driver.user')
                ->whereKey($route->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedRoute->status !== Route::STATUS_IN_PROGRESS) {
                throw new RuntimeException('La ruta debe estar en curso.');
            }

            if (! $lockedRoute->driver_id || ! $lockedRoute->driver) {
                throw new RuntimeException('La ruta no tiene repartidor asignado.');
            }

            if ($lockedRoute->driver->status !== Driver::STATUS_ACTIVE) {
                throw new RuntimeException('El repartidor de la ruta no está activo.');
            }

            if ($lockedPackage->current_status !== Package::STATUS_LISTO_RETIRO) {
                throw new RuntimeException('El paquete debe estar LISTO_RETIRO.');
            }

            if (! $lockedPackage->requires_delivery) {
                throw new RuntimeException('El paquete no requiere entrega a domicilio.');
            }

            if ($lockedPackage->delivery_status !== Package::DELIVERY_ACCEPTED) {
                throw new RuntimeException('El cliente debe aceptar la entrega antes de asignarla a reparto.');
            }

            if ($lockedPackage->driver_id !== null && (int) $lockedPackage->driver_id !== (int) $lockedRoute->driver_id) {
                throw new RuntimeException('El paquete ya está asignado a otro repartidor.');
            }

            if (mb_strtolower(trim((string) $lockedRoute->city)) !== mb_strtolower(trim((string) $lockedPackage->destination_city))) {
                throw new RuntimeException('La ciudad de la ruta no coincide con la ciudad destino del paquete.');
            }

            $lockedPackage->driver_id = $lockedRoute->driver_id;
            $lockedPackage->save();

            $lockedPackage->histories()->create([
                'status' => Package::STATUS_LISTO_RETIRO,
                'event_type' => PackageHistory::EVENT_REPARTO,
                'origin_location' => 'Agencia destino',
                'destination_location' => 'Repartidor',
                'location_description' => 'Paquete asignado a reparto en la ruta '.$lockedRoute->name,
                'scanned_by_user_id' => $userId,
            ]);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'package.delivery_assigned',
                'target_type' => Package::class,
                'target_id' => $lockedPackage->id,
                'description' => "Asignó la guía {$lockedPackage->tracking_number} al repartidor de la ruta {$lockedRoute->name}.",
                'metadata' => [
                    'route_id' => $lockedRoute->id,
                    'driver_id' => $lockedRoute->driver_id,
                    'destination_city' => $lockedPackage->destination_city,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $lockedPackage->fresh(['driver.user', 'histories']);
        });
    }
}
