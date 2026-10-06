<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DestinationReceptionService
{
    /**
     * Recepción física en la agencia o almacén destino.
     * EN_TRANSITO_NACIONAL -> LISTO_RETIRO (retiro en persona), o
     * -> PENDIENTE_ENTREGA si el envío es a domicilio: ahí espera a que
     * un repartidor lo tome o se lo asignen.
     */
    public function receive(
        Package $package,
        int $userId,
        string $destinationLocation,
        ?int $routeStopId = null,
    ): Package {
        $destinationLocation = trim($destinationLocation);

        if ($destinationLocation === '') {
            throw new RuntimeException('Debes indicar la agencia destino.');
        }

        return DB::transaction(function () use ($package, $userId, $destinationLocation, $routeStopId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_EN_TRANSITO_NACIONAL) {
                throw new RuntimeException(
                    'Solo se puede recibir en agencia destino un paquete en tránsito nacional. Estado actual: '
                    . $locked->statusLabel() . '.'
                );
            }

            $newStatus = $locked->requires_delivery
                ? Package::STATUS_PENDIENTE_ENTREGA
                : Package::STATUS_LISTO_RETIRO;

            $locked->current_status = $newStatus;
            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'route_stop_id' => $routeStopId,
                'status' => $newStatus,
                'event_type' => PackageHistory::EVENT_RECEPCION,
                'origin_location' => 'Tránsito nacional',
                'destination_location' => $destinationLocation,
                'location_description' => $locked->requires_delivery
                    ? 'Recepción física en destino: pendiente de entrega a domicilio'
                    : 'Recepción física en agencia destino',
                'scanned_by_user_id' => $userId,
            ]);

            // El destinatario ya puede pasar a retirarlo (o sabe que
            // pronto sale a reparto): mismo aviso por correo que el
            // resto de cambios de estado.
            app(PackageService::class)->notifyStatusChangeAfterCommit(
                $locked,
                $newStatus
            );

            return $locked->fresh(['ally', 'driver', 'histories']);
        });
    }
}
