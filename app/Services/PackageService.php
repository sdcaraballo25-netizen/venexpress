<?php

namespace App\Services;

use App\Models\Ally;
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
     * Crea un nuevo paquete.
     */
    public function createPackage(
        array $data,
        ?int $registeredByUserId = null
    ): Package {
        return DB::transaction(function () use (
            $data,
            $registeredByUserId
        ) {
            $isFragile = $data['is_fragile'] ?? false;
            $hasInsurance = $data['has_insurance'] ?? false;
            $declaredValueUsd = $data['declared_value_usd'] ?? null;
            $isCod = $data['is_cod'] ?? false;
            $codAmountUsd = $isCod
                ? ($data['cod_amount_usd'] ?? null)
                : null;
            $requiresDelivery = $data['requires_delivery'] ?? false;

            $pricing = $this->tariffService->calculate(
                originCity: $data['origin_city'],
                destinationCity: $data['destination_city'],
                packageType: $data['package_type'],
                physicalWeightKg: $data['physical_weight_kg'] ?? 0.0,
                lengthCm: $data['length_cm'] ?? null,
                widthCm: $data['width_cm'] ?? null,
                heightCm: $data['height_cm'] ?? null,
                isFragile: $isFragile,
                hasInsurance: $hasInsurance,
                declaredValueUsd: $declaredValueUsd,
                originState: $data['origin_state'] ?? null,
                destinationState: $data['destination_state'] ?? null,
                requiresDelivery: $requiresDelivery,
            );

            $commission = $this->calculateCommission(
                $data['ally_id'],
                $pricing['total_price_usd']
            );

            $package = Package::create([
                ...$data,

                'tracking_number' => $this->generateTrackingNumber(),

                'is_fragile' => $isFragile,
                'has_insurance' => $hasInsurance,
                'declared_value_usd' => $declaredValueUsd,

                'volumetric_weight_kg' =>
                    $pricing['volumetric_weight_kg'],

                'billable_weight_kg' =>
                    $pricing['billable_weight_kg'],

                'fragile_surcharge_usd' =>
                    $pricing['fragile_surcharge_usd'],

                'insurance_price_usd' =>
                    $pricing['insurance_price_usd'],

                'total_price_usd' =>
                    $pricing['total_price_usd'],

                'total_price_ves' =>
                    $pricing['total_price_ves'],

                'bcv_rate_used' =>
                    $pricing['bcv_rate_used'],

                /*
                 * El paquete entra a la agencia.
                 */
                'current_status' =>
                    Package::STATUS_RECIBIDO_AGENCIA,

                /*
                 * IMPORTANTE:
                 * Al crear el paquete NO tiene repartidor.
                 *
                 * Aunque venga un driver_id en $data,
                 * lo dejamos en null porque el repartidor
                 * se asigna cuando lo escanea.
                 */
                'driver_id' => null,

                'distance_km' =>
                    $pricing['distance_km'],

                'requires_delivery' =>
                    $requiresDelivery,

                'delivery_fee_usd' =>
                    $pricing['delivery_fee_usd'],

                'delivery_address' =>
                    $requiresDelivery
                        ? ($data['delivery_address'] ?? null)
                        : null,

                'delivery_sector' =>
                    $requiresDelivery
                        ? ($data['delivery_sector'] ?? null)
                        : null,

                'delivery_reference' =>
                    $requiresDelivery
                        ? ($data['delivery_reference'] ?? null)
                        : null,

                'is_cod' => $isCod,

                'payment_method' =>
                    $data['payment_method'] ?? null,

                'cod_amount_usd' =>
                    $codAmountUsd,

                'cod_status' =>
                    $isCod
                        ? Package::COD_PENDIENTE
                        : null,

                'commission_percentage_used' =>
                    $commission['percentage'],

                'commission_amount_usd' =>
                    $commission['amount'],
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
     * Cambia el estado de un paquete y registra historial.
     */
    public function changeStatus(
        Package $package,
        string $newStatus,
        ?int $userId = null,
        ?string $locationDescription = null,
        ?int $routeStopId = null,
    ): Package {
        if (! in_array($newStatus, Package::STATUSES, true)) {
            throw new RuntimeException(
                "Estado inválido: {$newStatus}"
            );
        }

        return DB::transaction(function () use (
            $package,
            $newStatus,
            $userId,
            $locationDescription,
            $routeStopId
        ) {
            $package->update([
                'current_status' => $newStatus,
            ]);

            $this->recordHistory(
                $package,
                $newStatus,
                $userId,
                $locationDescription,
                $routeStopId
            );

            return $package->fresh();
        });
    }

    /**
     * Asignación manual de un repartidor a un paquete.
     *
     * Se conserva por compatibilidad con otras partes del sistema.
     */
    public function assignDriver(
        Package $package,
        Driver $driver
    ): Package {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo se pueden asignar paquetes a repartidores activos.'
            );
        }

        $package->update([
            'driver_id' => $driver->id,
        ]);

        return $package->fresh();
    }

    /**
     * Asigna un paquete al repartidor que acaba de escanearlo.
     *
     * ESTA ES LA FUNCIÓN PRINCIPAL DEL NUEVO FLUJO.
     *
     * El paquete:
     *
     * - debe estar en RECIBIDO_AGENCIA
     * - no debe tener otro repartidor
     *
     * La comprobación de que la agencia pertenece a la ruta
     * se realiza desde Scanner.php.
     */
    public function assignDriverOnScan(
        Package $package,
        Driver $driver
    ): Package {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException(
                'Solo un repartidor activo puede escanear paquetes.'
            );
        }

        if (
            $package->current_status
            !== Package::STATUS_RECIBIDO_AGENCIA
        ) {
            throw new RuntimeException(
                'Este paquete no está disponible para ser asignado.'
            );
        }

        /*
         * Si ya tiene repartidor, no lo sobrescribimos.
         */
        if ($package->driver_id !== null) {
            if ((int) $package->driver_id === (int) $driver->id) {
                return $package->fresh();
            }

            throw new RuntimeException(
                'Este paquete ya está asignado a otro repartidor.'
            );
        }

        return DB::transaction(function () use (
            $package,
            $driver
        ) {
            /*
             * Bloqueamos el registro para evitar que dos
             * repartidores puedan escanearlo exactamente
             * al mismo tiempo.
             */
            $lockedPackage = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedPackage->current_status
                !== Package::STATUS_RECIBIDO_AGENCIA
            ) {
                throw new RuntimeException(
                    'Este paquete ya no está disponible para ser asignado.'
                );
            }

            if ($lockedPackage->driver_id !== null) {
                if (
                    (int) $lockedPackage->driver_id
                    === (int) $driver->id
                ) {
                    return $lockedPackage->fresh();
                }

                throw new RuntimeException(
                    'Este paquete ya está asignado a otro repartidor.'
                );
            }

            $lockedPackage->update([
                'driver_id' => $driver->id,
            ]);

            return $lockedPackage->fresh();
        });
    }

    /**
     * Liquida un cobro contra entrega.
     */
    public function liquidateCod(
        Package $package
    ): Package {
        if (! $package->is_cod) {
            throw new RuntimeException(
                'Este paquete no tiene cobro en destino (COD) activo.'
            );
        }

        $package->update([
            'cod_status' => Package::COD_LIQUIDADO,
            'cod_liquidated_at' => now(),
        ]);

        return $package->fresh();
    }

    /**
     * Calcula la comisión del aliado.
     */
    protected function calculateCommission(
        int $allyId,
        float $totalPriceUsd
    ): array {
        $ally = Ally::findOrFail($allyId);

        $percentage = (float) $ally->commission_percentage;

        $amount = round(
            $totalPriceUsd * ($percentage / 100),
            2
        );

        return [
            'percentage' => $percentage,
            'amount' => $amount,
        ];
    }

    /**
     * Genera un número de guía único.
     */
    protected function generateTrackingNumber(): string
    {
        $prefix = 'VEN-' . now()->format('Ymd') . '-';

        do {
            $candidate = $prefix
                . str_pad(
                    (string) random_int(1, 999999),
                    6,
                    '0',
                    STR_PAD_LEFT
                );
        } while (
            Package::where(
                'tracking_number',
                $candidate
            )->exists()
        );

        return $candidate;
    }

    /**
     * Registra un evento en el historial.
     */
    protected function recordHistory(
        Package $package,
        string $status,
        ?int $userId,
        ?string $locationDescription,
        ?int $routeStopId = null,
    ): PackageHistory {
        return $package->histories()->create([
            'status' => $status,
            'location_description' => $locationDescription,
            'scanned_by_user_id' => $userId,
            'route_stop_id' => $routeStopId,
        ]);
    }
}