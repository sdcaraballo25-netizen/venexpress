<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AuditLog;
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

            $requiresDelivery =
                $data['requires_delivery'] ?? false;

            $pricing = $this->tariffService->calculate(
                originCity: $data['origin_city'],
                destinationCity: $data['destination_city'],
                packageType: $data['package_type'],
                physicalWeightKg:
                    $data['physical_weight_kg'] ?? 0.0,
                lengthCm:
                    $data['length_cm'] ?? null,
                widthCm:
                    $data['width_cm'] ?? null,
                heightCm:
                    $data['height_cm'] ?? null,
                isFragile: $isFragile,
                hasInsurance: $hasInsurance,
                declaredValueUsd: $declaredValueUsd,
                originState:
                    $data['origin_state'] ?? null,
                destinationState:
                    $data['destination_state'] ?? null,
                requiresDelivery: $requiresDelivery,
            );

            $commission = $this->calculateCommission(
                $data['ally_id'],
                $pricing['total_price_usd']
            );

            $package = Package::create([
                ...$data,

                'tracking_number' =>
                    $this->generateTrackingNumber(),

                'is_fragile' =>
                    $isFragile,

                'has_insurance' =>
                    $hasInsurance,

                'declared_value_usd' =>
                    $declaredValueUsd,

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

                'current_status' =>
                    Package::STATUS_RECIBIDO_AGENCIA,

                'driver_id' =>
                    null,

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

                'is_cod' =>
                    $isCod,

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
                package: $package,
                status: Package::STATUS_RECIBIDO_AGENCIA,
                userId: $registeredByUserId,
                locationDescription:
                    'Guía registrada en taquilla aliada',
                eventType:
                    PackageHistory::EVENT_RECEPCION,
                destinationLocation:
                    'Agencia Aliada',
            );

            $securityHash =
                Package::computeSecurityHash(
                    $package->tracking_number,
                    (int) $package->ally_id,
                    (float) $package->physical_weight_kg,
                    $package->created_at,
                );

            $package->forceFill([
                'security_hash' => $securityHash,
            ])->save();

            return $package;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CAMBIO DE ESTADO
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Package $package,
        string $newStatus,
        ?int $userId = null,
        ?string $locationDescription = null,
        ?int $routeStopId = null,
        string $eventType = PackageHistory::EVENT_MOVIMIENTO,
        ?string $originLocation = null,
        ?string $destinationLocation = null,
    ): Package {
        if (! in_array(
            $newStatus,
            Package::STATUSES,
            true
        )) {
            throw new RuntimeException(
                "Estado inválido: {$newStatus}"
            );
        }

        $this->validateStatusTransition(
            $package->current_status,
            $newStatus
        );

        return DB::transaction(
            function () use (
                $package,
                $newStatus,
                $userId,
                $locationDescription,
                $routeStopId,
                $eventType,
                $originLocation,
                $destinationLocation
            ) {
                $lockedPackage =
                    Package::query()
                        ->whereKey($package->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $lockedPackage->current_status
                    !== $package->current_status
                ) {
                    throw new RuntimeException(
                        'El estado del paquete cambió mientras '
                        . 'se procesaba la operación.'
                    );
                }

                $this->validateStatusTransition(
                    $lockedPackage->current_status,
                    $newStatus
                );

                $lockedPackage->update([
                    'current_status' => $newStatus,
                ]);

                $this->recordHistory(
                    package: $lockedPackage,
                    status: $newStatus,
                    userId: $userId,
                    locationDescription:
                        $locationDescription,
                    routeStopId: $routeStopId,
                    eventType: $eventType,
                    originLocation:
                        $originLocation,
                    destinationLocation:
                        $destinationLocation,
                );

                return $lockedPackage->fresh();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PISTOLEO
    |--------------------------------------------------------------------------
    */

    /**
     * Registra un movimiento físico de la guía.
     *
     * Este método NO cambia automáticamente el estado del paquete.
     * El estado y el evento se controlan por separado para conservar
     * una trazabilidad correcta.
     */
    public function registerScan(
        Package $package,
        string $eventType,
        int $userId,
        ?string $originLocation = null,
        ?string $destinationLocation = null,
        ?string $locationDescription = null,
        ?int $routeStopId = null,
    ): PackageHistory {
        if (! in_array(
            $eventType,
            PackageHistory::EVENTOS,
            true
        )) {
            throw new RuntimeException(
                'Tipo de movimiento inválido.'
            );
        }

        if ($eventType === PackageHistory::EVENT_MOVIMIENTO) {
            throw new RuntimeException(
                'Debes indicar un tipo específico de pistoleo.'
            );
        }

        if (
            $eventType !== PackageHistory::EVENT_INCIDENCIA
            && $eventType !== PackageHistory::EVENT_CORRECCION
            && $originLocation === null
            && $destinationLocation === null
            && $locationDescription === null
        ) {
            throw new RuntimeException(
                'El pistoleo debe indicar ubicación.'
            );
        }

        return DB::transaction(function () use (
            $package,
            $eventType,
            $userId,
            $originLocation,
            $destinationLocation,
            $locationDescription,
            $routeStopId
        ) {
            $lockedPackage =
                Package::query()
                    ->whereKey($package->id)
                    ->lockForUpdate()
                    ->firstOrFail();

            return $this->recordHistory(
                package: $lockedPackage,
                status:
                    $lockedPackage->current_status,
                userId: $userId,
                locationDescription:
                    $locationDescription,
                routeStopId: $routeStopId,
                eventType: $eventType,
                originLocation: $originLocation,
                destinationLocation:
                    $destinationLocation,
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ESTADOS PERMITIDOS
    |--------------------------------------------------------------------------
    */

    protected function validateStatusTransition(
        string $currentStatus,
        string $newStatus
    ): void {
        if ($currentStatus === $newStatus) {
            throw new RuntimeException(
                'El paquete ya se encuentra en ese estado.'
            );
        }

        $allowedTransitions = [

            Package::STATUS_RECIBIDO_AGENCIA => [
                Package::STATUS_RECOLECTADO_VENEXPRESS,
            ],

            Package::STATUS_RECOLECTADO_VENEXPRESS => [
                Package::STATUS_EN_HUB,
            ],

            Package::STATUS_EN_HUB => [
                Package::STATUS_EN_TRANSITO_NACIONAL,
            ],

            Package::STATUS_EN_TRANSITO_NACIONAL => [
                Package::STATUS_LISTO_RETIRO,
            ],

            Package::STATUS_LISTO_RETIRO => [
                Package::STATUS_ENTREGADO,
                Package::STATUS_EN_TRANSITO_NACIONAL,
            ],

            Package::STATUS_ENTREGADO => [],
        ];

        if (
            ! in_array(
                $newStatus,
                $allowedTransitions[$currentStatus] ?? [],
                true
            )
        ) {
            throw new RuntimeException(
                "No se puede cambiar el paquete de "
                . "{$currentStatus} a {$newStatus}."
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REPARTIDOR
    |--------------------------------------------------------------------------
    */

    public function assignDriver(
        Package $package,
        Driver $driver
    ): Package {
        if (
            $driver->status
            !== Driver::STATUS_ACTIVE
        ) {
            throw new RuntimeException(
                'Solo se pueden asignar paquetes '
                . 'a repartidores activos.'
            );
        }

        $package->update([
            'driver_id' => $driver->id,
        ]);

        return $package->fresh();
    }

    public function assignDriverOnScan(
        Package $package,
        Driver $driver
    ): Package {
        if (
            $driver->status
            !== Driver::STATUS_ACTIVE
        ) {
            throw new RuntimeException(
                'Solo un repartidor activo puede '
                . 'escanear paquetes.'
            );
        }

        if (
            $package->current_status
            !== Package::STATUS_RECIBIDO_AGENCIA
        ) {
            throw new RuntimeException(
                'Este paquete no está disponible '
                . 'para ser asignado.'
            );
        }

        if ($package->driver_id !== null) {

            if (
                (int) $package->driver_id
                === (int) $driver->id
            ) {
                return $package->fresh();
            }

            throw new RuntimeException(
                'Este paquete ya está asignado '
                . 'a otro repartidor.'
            );
        }

        return DB::transaction(
            function () use (
                $package,
                $driver
            ) {
                $lockedPackage =
                    Package::query()
                        ->whereKey($package->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                if (
                    $lockedPackage->current_status
                    !== Package::STATUS_RECIBIDO_AGENCIA
                ) {
                    throw new RuntimeException(
                        'Este paquete ya no está disponible '
                        . 'para ser asignado.'
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
                        'Este paquete ya está asignado '
                        . 'a otro repartidor.'
                    );
                }

                $lockedPackage->update([
                    'driver_id' => $driver->id,
                ]);

                return $lockedPackage->fresh();
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ENTREGA
    |--------------------------------------------------------------------------
    */

    public function completeDelivery(
        Package $package,
        Driver $driver,
        ?string $locationDescription = null
    ): Package {
        return DB::transaction(function () use (
            $package,
            $driver,
            $locationDescription
        ) {
            $lockedPackage = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($driver->status !== Driver::STATUS_ACTIVE) {
                throw new RuntimeException('El repartidor no está activo.');
            }

            if ((int) $lockedPackage->driver_id !== (int) $driver->id) {
                throw new RuntimeException(
                    'Este paquete no está asignado a este repartidor.'
                );
            }

            if (! $lockedPackage->requires_delivery) {
                throw new RuntimeException(
                    'Este paquete no requiere entrega a domicilio.'
                );
            }

            if ($lockedPackage->current_status !== Package::STATUS_EN_TRANSITO_NACIONAL) {
                throw new RuntimeException(
                    'El paquete no está en estado de reparto.'
                );
            }

            if ($lockedPackage->delivery_status !== Package::DELIVERY_ACCEPTED) {
                throw new RuntimeException(
                    'El cliente todavía no ha aceptado la entrega.'
                );
            }

            if ($lockedPackage->is_cod && ! $lockedPackage->cod_collected_at) {
                $lockedPackage->cod_collected_at = now();
                $lockedPackage->cod_collected_by_user_id = $driver->user_id;
            }

            $lockedPackage->update([
                'current_status' => Package::STATUS_ENTREGADO,
                'delivery_status' => Package::DELIVERY_COMPLETED,
                'delivery_completed_at' => now(),
                'driver_remuneration_status' => Package::REMUNERATION_PENDING,
            ]);

            if ($lockedPackage->is_cod && $lockedPackage->cod_collected_at) {
                $lockedPackage->save();
            }

            app(DriverPaymentService::class)->createForDeliveredPackage(
                $lockedPackage,
                $driver
            );

            $this->recordHistory(
                package: $lockedPackage,
                status: Package::STATUS_ENTREGADO,
                userId: $driver->user_id,
                locationDescription:
                    $locationDescription
                    ?? 'Entrega completada por el repartidor',
                eventType: PackageHistory::EVENT_ENTREGA,
                originLocation: 'Dirección de entrega',
                destinationLocation: 'Destinatario',
            );

            return $lockedPackage->fresh();
        });
    }

    /**
     * Completa un retiro presencial en la agencia destino.
     * No genera remuneración de repartidor porque no hubo entrega a domicilio.
     */
    public function completeAgencyPickup(
        Package $package,
        int $userId,
        string $recipientIdDoc,
        ?string $locationDescription = null
    ): Package {
        return DB::transaction(function () use (
            $package,
            $userId,
            $recipientIdDoc,
            $locationDescription
        ) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->current_status !== Package::STATUS_LISTO_RETIRO) {
                throw new RuntimeException('La guía no está lista para retiro.');
            }

            if ($locked->requires_delivery) {
                throw new RuntimeException(
                    'Este envío requiere entrega a domicilio y no puede retirarse en agencia.'
                );
            }

            if (trim((string) $locked->recipient_id_doc) !== trim($recipientIdDoc)) {
                throw new RuntimeException('El documento del receptor no coincide.');
            }

            if ($locked->is_cod && ! $locked->cod_collected_at) {
                $locked->cod_collected_at = now();
                $locked->cod_collected_by_user_id = $userId;
            }

            $locked->current_status = Package::STATUS_ENTREGADO;
            $locked->delivery_completed_at = now();
            $locked->save();

            $this->recordHistory(
                package: $locked,
                status: Package::STATUS_ENTREGADO,
                userId: $userId,
                locationDescription:
                    $locationDescription ?? 'Retiro confirmado en agencia destino',
                eventType: PackageHistory::EVENT_ENTREGA,
                originLocation: 'Agencia destino',
                destinationLocation: 'Destinatario',
            );

            return $locked->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | COD
    |--------------------------------------------------------------------------
    */

    public function collectCod(Package $package, int $userId): Package
    {
        return DB::transaction(function () use ($package, $userId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->is_cod) {
                throw new RuntimeException('Este paquete no tiene COD.');
            }

            if ($locked->current_status !== Package::STATUS_ENTREGADO) {
                throw new RuntimeException(
                    'El COD solo puede registrarse después de confirmar la entrega.'
                );
            }

            if ($locked->cod_status === Package::COD_LIQUIDADO) {
                throw new RuntimeException('El COD ya fue liquidado.');
            }

            if ($locked->cod_collected_at) {
                return $locked->fresh();
            }

            $locked->update([
                'cod_collected_at' => now(),
                'cod_collected_by_user_id' => $userId,
            ]);

            return $locked->fresh();
        });
    }

    public function liquidateCod(Package $package, int $userId): Package
    {
        return DB::transaction(function () use ($package, $userId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->is_cod) {
                throw new RuntimeException(
                    'Este paquete no tiene cobro en destino (COD) activo.'
                );
            }

            if ($locked->current_status !== Package::STATUS_ENTREGADO) {
                throw new RuntimeException(
                    'El paquete debe estar entregado antes de liquidar el COD.'
                );
            }

            if (! $locked->cod_collected_at) {
                throw new RuntimeException(
                    'Primero debes registrar el cobro del COD.'
                );
            }

            if ($locked->cod_status === Package::COD_LIQUIDADO) {
                throw new RuntimeException('El COD ya está liquidado.');
            }

            $locked->update([
                'cod_status' => Package::COD_LIQUIDADO,
                'cod_liquidated_at' => now(),
            ]);

            AuditLog::create([
                'actor_user_id' => $userId,
                'action' => 'package.cod_liquidated',
                'target_type' => Package::class,
                'target_id' => $locked->id,
                'description' => "Liquidó COD de la guía {$locked->tracking_number}.",
                'metadata' => [
                    'tracking_number' => $locked->tracking_number,
                    'amount_usd' => (float) $locked->cod_amount_usd,
                ],
                'ip_address' => request()?->ip(),
            ]);

            return $locked->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | COMISIONES
    |--------------------------------------------------------------------------
    */

    protected function calculateCommission(
        int $allyId,
        float $totalPriceUsd
    ): array {
        $ally = Ally::findOrFail($allyId);

        $percentage =
            (float) $ally->commission_percentage;

        $amount = round(
            $totalPriceUsd
            * ($percentage / 100),
            2
        );

        return [
            'percentage' => $percentage,
            'amount' => $amount,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GUÍA
    |--------------------------------------------------------------------------
    */

    protected function generateTrackingNumber(): string
    {
        $prefix =
            'VEN-' . now()->format('Ymd') . '-';

        do {

            $candidate =
                $prefix
                . str_pad(
                    (string) random_int(
                        1,
                        999999
                    ),
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

    /*
    |--------------------------------------------------------------------------
    | HISTORIAL
    |--------------------------------------------------------------------------
    */

    protected function recordHistory(
        Package $package,
        string $status,
        ?int $userId,
        ?string $locationDescription,
        ?int $routeStopId = null,
        string $eventType =
            PackageHistory::EVENT_MOVIMIENTO,
        ?string $originLocation = null,
        ?string $destinationLocation = null,
    ): PackageHistory {
        return $package->histories()->create([

            'status' =>
                $status,

            'event_type' =>
                $eventType,

            'origin_location' =>
                $originLocation,

            'destination_location' =>
                $destinationLocation,

            'location_description' =>
                $locationDescription,

            'scanned_by_user_id' =>
                $userId,

            'route_stop_id' =>
                $routeStopId,
        ]);
    }
}
