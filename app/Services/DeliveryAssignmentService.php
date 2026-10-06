<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Route;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Asigna un paquete a domicilio a la ruta de reparto en curso de un
 * repartidor. Punto único para las tres vías: Admin
 * (Admin\DriverAssignment), Almacén (Almacen\Dashboard) y el propio
 * repartidor escaneando la guía (PackageService::claimForDelivery()).
 *
 * Regla de zona (la misma para las tres): el paquete debe estar
 * PENDIENTE_ENTREGA en el almacén que atiende la zona de la ruta
 * (routeWarehouseId()) y su ciudad destino debe ser la ciudad de la
 * ruta. Así un almacén que cubre varias ciudades no termina mandando
 * a un repartidor de una ciudad a entregar en otra.
 */
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

            if (! $lockedRoute->isDelivery()) {
                throw new RuntimeException('La ruta seleccionada no es una ruta de reparto.');
            }

            if ($lockedRoute->status !== Route::STATUS_IN_PROGRESS) {
                throw new RuntimeException('La ruta debe estar en curso.');
            }

            if (! $lockedRoute->driver_id || ! $lockedRoute->driver) {
                throw new RuntimeException('La ruta no tiene repartidor asignado.');
            }

            if ($lockedRoute->driver->status !== Driver::STATUS_ACTIVE) {
                throw new RuntimeException('El repartidor de la ruta no está activo.');
            }

            if (! $lockedPackage->requires_delivery) {
                throw new RuntimeException('El paquete no requiere entrega a domicilio.');
            }

            if (! in_array($lockedPackage->current_status, Package::CLAIMABLE_FOR_DELIVERY_STATUSES, true)) {
                throw new RuntimeException(
                    'El paquete debe estar pendiente de entrega en su almacén destino. Estado actual: '
                    .$lockedPackage->statusLabel().'.'
                );
            }

            // Ya no se exige delivery_status === DELIVERY_ACCEPTED: la
            // modalidad a domicilio se decide al crear el envío y la
            // aceptación posterior del cliente quedó fuera de las reglas
            // del MVP. Exigirla dejaba sin camino la asignación por
            // Admin/Almacén y la toma por escaneo de un repartidor.

            if ($lockedPackage->driver_id !== null && (int) $lockedPackage->driver_id !== (int) $lockedRoute->driver_id) {
                throw new RuntimeException('El paquete ya está asignado a otro repartidor.');
            }

            $routeWarehouseId = $this->routeWarehouseId($lockedRoute);

            if ($routeWarehouseId === null) {
                throw new RuntimeException(
                    'La ruta no tiene un almacén de salida ni hay uno que cubra su ciudad: no puede tomar entregas.'
                );
            }

            if ((int) $lockedPackage->current_warehouse_id !== $routeWarehouseId) {
                throw new RuntimeException('Este paquete no está en el almacén de la zona de la ruta.');
            }

            if (mb_strtolower(trim((string) $lockedRoute->city)) !== mb_strtolower(trim((string) $lockedPackage->destination_city))) {
                throw new RuntimeException('La ciudad de la ruta no coincide con la ciudad destino del paquete.');
            }

            $lockedPackage->driver_id = $lockedRoute->driver_id;
            $lockedPackage->delivery_status = Package::DELIVERY_ACCEPTED;
            $lockedPackage->save();

            // PENDIENTE_ENTREGA -> EN_RUTA: el paquete sale a reparto con
            // el repartidor de la ruta, y se genera el PIN de entrega que
            // se le envía al destinatario.
            $lockedPackage = app(PackageService::class)->sendOutForDelivery(
                package: $lockedPackage,
                userId: $userId,
                locationDescription: 'Salió a reparto en la ruta '.$lockedRoute->name,
                originLocation: $lockedPackage->currentWarehouse?->name ?? 'Almacén destino',
            );

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

    public function unassign(Package $package, int $userId, ?string $reason = null): Package
    {
        return DB::transaction(function () use ($package, $userId, $reason) {
            $lockedPackage = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPackage->driver_id === null) {
                throw new RuntimeException('El paquete no tiene un repartidor asignado.');
            }

            if ($lockedPackage->isDelivered()) {
                throw new RuntimeException('No se puede retirar la asignación de un paquete entregado.');
            }

            $previousDriverId = $lockedPackage->driver_id;
            $lockedPackage->driver_id = null;

            // Si ya había salido a reparto, vuelve a quedar pendiente de
            // entrega en el almacén (para poder asignarlo a otro) y el
            // PIN emitido deja de servir.
            if ($lockedPackage->current_status === Package::STATUS_EN_RUTA) {
                $lockedPackage->current_status = Package::STATUS_PENDIENTE_ENTREGA;
                $lockedPackage->delivery_status = Package::DELIVERY_PENDING;
                $lockedPackage->delivery_pin_hash = null;
                $lockedPackage->delivery_pin_failed_attempts = 0;
            }

            $lockedPackage->save();

            $lockedPackage->histories()->create([
                'status' => $lockedPackage->current_status,
                'event_type' => PackageHistory::EVENT_CORRECCION,
                'origin_location' => 'Reparto',
                'destination_location' => 'Agencia destino',
                'location_description' => 'Se retiró la asignación de reparto.',
                'scanned_by_user_id' => $userId,
            ]);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'package.delivery_unassigned',
                'target_type' => Package::class,
                'target_id' => $lockedPackage->id,
                'description' => "Retiró la asignación de la guía {$lockedPackage->tracking_number}.",
                'metadata' => [
                    'previous_driver_id' => $previousDriverId,
                    'reason' => $reason,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $lockedPackage->fresh(['driver.user', 'histories']);
        });
    }

    /**
     * Almacén (HUB) que atiende la zona de una ruta de reparto:
     * origin_warehouse_id si Admin lo fijó; si no, el que
     * WarehouseCoverage resuelve para el estado/ciudad de la ruta
     * (misma fuente de verdad que el destino de los paquetes). Null si
     * no se puede resolver, y entonces la ruta no puede tomar entregas.
     */
    public function routeWarehouseId(Route $route): ?int
    {
        if ($route->origin_warehouse_id !== null) {
            return (int) $route->origin_warehouse_id;
        }

        $result = app(LogisticsResolutionService::class)
            ->resolveDestinationWarehouse($route->state, $route->city);

        return $result->isResolved() ? (int) $result->warehouseId : null;
    }
}
