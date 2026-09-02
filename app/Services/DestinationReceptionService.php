<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DestinationReceptionService
{
    /**
     * Recepción física en la agencia destino.
     * EN_TRANSITO_NACIONAL -> LISTO_RETIRO.
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

            $locked->current_status = Package::STATUS_LISTO_RETIRO;
            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'route_stop_id' => $routeStopId,
                'status' => Package::STATUS_LISTO_RETIRO,
                'event_type' => PackageHistory::EVENT_RECEPCION,
                'origin_location' => 'Tránsito nacional',
                'destination_location' => $destinationLocation,
                'location_description' => 'Recepción física en agencia destino',
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh(['ally', 'driver', 'histories']);
        });
    }
}
