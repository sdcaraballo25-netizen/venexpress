<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HubReceptionService
{
    public function receive(
        Package $package,
        int $userId,
        string $hubLocation,
    ): Package {
        $hubLocation = trim($hubLocation);

        if ($hubLocation === '') {
            throw new RuntimeException('Indica el Hub donde fue recibido el paquete.');
        }

        return DB::transaction(function () use ($package, $userId, $hubLocation) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_EN_TRANSITO_NACIONAL) {
                throw new RuntimeException(
                    'Solo se puede registrar recepción de Hub para un paquete en tránsito nacional.'
                );
            }

            $locked->current_status = Package::STATUS_EN_HUB;
            $locked->save();

            PackageHistory::create([
                'package_id' => $locked->id,
                'status' => Package::STATUS_EN_HUB,
                'event_type' => PackageHistory::EVENT_RECEPCION,
                'origin_location' => 'Tránsito nacional',
                'destination_location' => $hubLocation,
                'location_description' => 'Recepción física en Hub Venexpress',
                'scanned_by_user_id' => $userId,
            ]);

            return $locked->fresh(['ally', 'driver', 'histories']);
        });
    }
}
