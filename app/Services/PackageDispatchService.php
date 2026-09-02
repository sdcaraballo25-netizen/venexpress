<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PackageDispatchService
{
    /**
     * Despacha un paquete desde Hub hacia tránsito nacional.
     *
     * EN_HUB -> EN_TRANSITO_NACIONAL
     * Cada despacho genera un evento SALIDA inmutable.
     */
    public function dispatch(
        Package $package,
        int $userId,
        string $originLocation,
        string $destinationLocation,
    ): Package {
        $originLocation = trim($originLocation);
        $destinationLocation = trim($destinationLocation);

        if ($originLocation === '') {
            throw new RuntimeException('Indica el Hub de origen.');
        }

        if ($destinationLocation === '') {
            throw new RuntimeException('Indica la ciudad/agencia de destino.');
        }

        return DB::transaction(function () use (
            $package,
            $userId,
            $originLocation,
            $destinationLocation,
        ) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_EN_HUB) {
                throw new RuntimeException(
                    'Solo se puede despachar un paquete que esté en HUB. '
                    . 'Estado actual: ' . $locked->statusLabel() . '.'
                );
            }

            $locked->current_status = Package::STATUS_EN_TRANSITO_NACIONAL;
            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'route_stop_id' => null,
                'status' => Package::STATUS_EN_TRANSITO_NACIONAL,
                'event_type' => PackageHistory::EVENT_SALIDA,
                'origin_location' => $originLocation,
                'destination_location' => $destinationLocation,
                'location_description' => 'Salida de Hub hacia tránsito nacional',
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        });
    }
}
