<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PackageService
{
    public function __construct(
        protected TariffService $tariffService,
    ) {
    }

    /**
     * Crea un nuevo paquete: calcula tarifa, pesos y número de guía,
     * y registra el primer evento en el historial.
     *
     * @param array{
     *   ally_id:int, sender_name:string, sender_id_doc:string, sender_phone:string,
     *   recipient_name:string, recipient_id_doc:string, recipient_phone:string,
     *   origin_city:string, destination_city:string, package_type:string,
     *   physical_weight_kg:float, length_cm?:float|null, width_cm?:float|null, height_cm?:float|null,
     * } $data
     */
    public function createPackage(array $data, ?int $registeredByUserId = null): Package
    {
        return DB::transaction(function () use ($data, $registeredByUserId) {
            $pricing = $this->tariffService->calculate(
                originCity: $data['origin_city'],
                destinationCity: $data['destination_city'],
                physicalWeightKg: $data['physical_weight_kg'],
                lengthCm: $data['length_cm'] ?? null,
                widthCm: $data['width_cm'] ?? null,
                heightCm: $data['height_cm'] ?? null,
            );

            $package = Package::create([
                ...$data,
                'tracking_number' => $this->generateTrackingNumber(),
                'volumetric_weight_kg' => $pricing['volumetric_weight_kg'],
                'billable_weight_kg' => $pricing['billable_weight_kg'],
                'total_price_usd' => $pricing['total_price_usd'],
                'total_price_ves' => $pricing['total_price_ves'],
                'bcv_rate_used' => $pricing['bcv_rate_used'],
                'current_status' => Package::STATUS_RECIBIDO_AGENCIA,
            ]);

            $this->recordHistory(
                $package,
                Package::STATUS_RECIBIDO_AGENCIA,
                $registeredByUserId,
                'Guía registrada en taquilla aliada',
            );

            return $package;
        });
    }

    /**
     * Cambia el estado de un paquete y deja constancia en su historial.
     *
     * @throws RuntimeException si el estado no es válido.
     */
    public function changeStatus(
        Package $package,
        string $newStatus,
        ?int $userId = null,
        ?string $locationDescription = null,
    ): Package {
        if (! in_array($newStatus, Package::STATUSES, true)) {
            throw new RuntimeException("Estado inválido: {$newStatus}");
        }

        return DB::transaction(function () use ($package, $newStatus, $userId, $locationDescription) {
            $package->update(['current_status' => $newStatus]);

            $this->recordHistory($package, $newStatus, $userId, $locationDescription);

            return $package->fresh();
        });
    }

    /**
     * Asigna (o reasigna) un chofer a un paquete.
     */
    public function assignDriver(Package $package, Driver $driver): Package
    {
        $package->update(['driver_id' => $driver->id]);

        return $package->fresh();
    }

    /**
     * Genera un número de guía único con formato VEN-YYYYMMDD-NNNNNN.
     */
    protected function generateTrackingNumber(): string
    {
        $prefix = 'VEN-' . now()->format('Ymd') . '-';

        do {
            $candidate = $prefix . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Package::where('tracking_number', $candidate)->exists());

        return $candidate;
    }

    protected function recordHistory(
        Package $package,
        string $status,
        ?int $userId,
        ?string $locationDescription,
    ): PackageHistory {
        return $package->histories()->create([
            'status' => $status,
            'location_description' => $locationDescription,
            'scanned_by_user_id' => $userId,
        ]);
    }
}
