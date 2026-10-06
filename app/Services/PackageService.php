<?php

namespace App\Services;

use App\Models\Ally;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Package;
use App\Models\PackageHistory;
use App\Models\Pedido;
use App\Models\Route;
use App\Notifications\DeliveryPinIssued;
use App\Notifications\PackageCreated;
use App\Notifications\PackageStatusUpdated;
use App\Support\Money;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Throwable;

class PackageService
{
    public function __construct(
        protected TariffService $tariffService,
    ) {
    }

    /**
     * Avisa por correo al destinatario que el estado de su guía
     * cambió. El destinatario se resuelve por recipient_id_doc contra
     * la tabla customers (el mismo vínculo que usa el panel de
     * Cliente), así que si nadie con ese documento se ha registrado
     * o registrado como destinatario con correo, simplemente no se
     * envía nada — no es un error, es un envío sin cliente asociado.
     *
     * Nunca se deja que un fallo de correo (SMTP caído, red, etc.)
     * interrumpa una operación de guía ya confirmada en base de
     * datos: por eso todo el envío queda protegido en un try/catch.
     */
    public function notifyStatusChange(Package $package, string $status): void
    {
        try {
            $customer = Customer::query()
                ->where('id_doc', $package->recipient_id_doc)
                ->whereNotNull('email')
                ->first();

            if (! $customer || ! $customer->email) {
                return;
            }

            Notification::route('mail', $customer->email)
                ->notify(new PackageStatusUpdated($package->id, $status));
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo enviar la notificación de cambio de estado.',
                [
                    'package_id' => $package->id,
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Igual que notifyStatusChange(), pero para los servicios que
     * cambian current_status dentro de su propia transacción en vez de
     * pasar por changeStatus() (DestinationReceptionService,
     * HubReleaseService): el aviso sale solo si esa transacción — y la
     * exterior, si la hay, como HubReceptionService::attemptAutoRelease()
     * — se confirma. Si algo revierte el cambio, el cliente nunca recibe
     * un aviso de un estado que no quedó guardado. Fuera de una
     * transacción se ejecuta de inmediato.
     */
    public function notifyStatusChangeAfterCommit(Package $package, string $status): void
    {
        DB::afterCommit(fn () => $this->notifyStatusChange($package, $status));
    }

    /**
     * Avisa por correo al remitente y al destinatario que la guía
     * quedó registrada, cada uno con un mensaje distinto (ver
     * PackageCreated). El correo de cada uno se busca en customers
     * por su id_doc, igual que notifyStatusChange(): si no tiene uno
     * registrado, simplemente no se le envía nada.
     */
    protected function notifyPackageCreated(Package $package): void
    {
        try {
            $senderEmail = Customer::query()
                ->where('id_doc', $package->sender_id_doc)
                ->value('email');

            if ($senderEmail) {
                Notification::route('mail', $senderEmail)
                    ->notify(new PackageCreated($package->id, PackageCreated::ROLE_SENDER));
            }

            $recipientEmail = Customer::query()
                ->where('id_doc', $package->recipient_id_doc)
                ->value('email');

            if ($recipientEmail) {
                Notification::route('mail', $recipientEmail)
                    ->notify(new PackageCreated($package->id, PackageCreated::ROLE_RECIPIENT));
            }
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo enviar la notificación de guía registrada.',
                [
                    'package_id' => $package->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    public function createPackage(
        array $data,
        ?int $registeredByUserId = null
    ): Package {
        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $this->attemptCreatePackage(
                    $data,
                    $registeredByUserId
                );
            } catch (QueryException $e) {
                $isUniqueViolation =
                    (int) $e->getCode() === 23000
                    || str_contains(
                        strtolower($e->getMessage()),
                        'tracking_number'
                    );

                if (! $isUniqueViolation || $attempt === $maxAttempts) {
                    throw $e;
                }

                // Colisión de índice único en tracking_number:
                // se reintenta con un número nuevo.
                continue;
            }
        }

        // Nunca debería llegar aquí, pero PHP exige un retorno.
        throw new RuntimeException(
            'No se pudo generar la guía tras varios intentos.'
        );
    }

    protected function attemptCreatePackage(
        array $data,
        ?int $registeredByUserId
    ): Package {
        $package = DB::transaction(function () use (
            $data,
            $registeredByUserId
        ) {
            $isFragile = $data['is_fragile'] ?? false;
            $hasInsurance = $data['has_insurance'] ?? false;
            $declaredValueUsd = $data['declared_value_usd'] ?? null;
            $isCod = $data['is_cod'] ?? false;

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
                discountPercentage:
                    $data['discount_percentage'] ?? 0.0,
            );

            // El monto COD siempre es el total calculado por
            // TariffService, nunca lo que venga en $data: ese valor
            // viene de un campo del formulario (Ally\PackageCreate)
            // que un request manipulado podría alterar para cobrar de
            // más o de menos en destino.
            $codAmountUsd = $isCod
                ? $pricing['total_price_usd']
                : null;

            $commission = $this->calculateCommission(
                $data['ally_id'],
                $pricing['total_price_usd']
            );

            $package = Package::create([
                ...$data,

                'tracking_number' =>
                    $this->generateTrackingNumber(),

                'registered_by_user_id' =>
                    $registeredByUserId,

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

                // Coordenadas exactas capturadas por el Google Places
                // Autocomplete del formulario (ver Ally\PackageCreate),
                // cuando está configurado. Si no vienen (autocompletado
                // no disponible, o el aliado escribió la dirección a
                // mano sin seleccionar una sugerencia), quedan null y
                // GeocodingService las completa después, de respaldo,
                // la primera vez que un repartidor pida su ruta.
                'delivery_latitude' =>
                    $requiresDelivery
                        ? ($data['delivery_latitude'] ?? null)
                        : null,

                'delivery_longitude' =>
                    $requiresDelivery
                        ? ($data['delivery_longitude'] ?? null)
                        : null,

                'delivery_geocoded_at' =>
                    $requiresDelivery && ! empty($data['delivery_latitude'])
                        ? now()
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

        $this->notifyPackageCreated($package);

        return $package;
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
        bool $notifyCustomer = true,
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

        $updatedPackage = DB::transaction(
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

        if ($notifyCustomer) {
            $this->notifyStatusChange($updatedPackage, $newStatus);
        }

        return $updatedPackage;
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
                // Un repartidor de tipo Delivery recolecta directo en
                // la agencia y sale a reparto a domicilio sin pasar
                // por el HUB (PackageDetail::startDelivery()).
                Package::STATUS_EN_RUTA,
            ],

            Package::STATUS_EN_HUB => [
                Package::STATUS_EN_TRANSITO_NACIONAL,
            ],

            Package::STATUS_EN_TRANSITO_NACIONAL => [
                Package::STATUS_LISTO_RETIRO,
            ],

            Package::STATUS_LISTO_RETIRO => [
                Package::STATUS_ENTREGADO,
            ],

            // Entrega a domicilio: solo sale a reparto desde el almacén
            // destino (sendOutForDelivery()) y solo se entrega desde
            // EN_RUTA (completeDelivery(), que no pasa por aquí).
            Package::STATUS_PENDIENTE_ENTREGA => [
                Package::STATUS_EN_RUTA,
            ],

            Package::STATUS_EN_RUTA => [
                Package::STATUS_ENTREGADO,
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

    /**
     * Un repartidor de entrega (driver_type = delivery) toma, escaneando
     * la guía, un pedido a domicilio para SU ruta de reparto en curso.
     *
     * Conocer el número de guía no basta: el paquete debe estar
     * disponible para reparto en la zona de esa ruta. Validaciones
     * (todas del lado del servidor, con el paquete bloqueado):
     * - Estado PENDIENTE_ENTREGA (Package::CLAIMABLE_FOR_DELIVERY_STATUSES):
     *   ya llegó a destino. Un paquete EN_TRANSITO_NACIONAL (viajando
     *   entre HUBs) nunca se puede tomar.
     * - El repartidor tiene una ruta TYPE_DELIVERY en curso.
     * - El paquete está físicamente en su HUB destino
     *   (LogisticsResolutionService::isAtDestinationWarehouse()).
     *
     * La asignación en sí la hace DeliveryAssignmentService::assign() —
     * la misma que usa Admin/Almacén —, así el paquete queda ligado a la
     * ruta (AuditLog con route_id, ver RouteService::packageIdsForRoute()),
     * pasa por la misma regla de zona (almacén de la ruta + ciudad
     * destino) y sale a reparto (EN_RUTA) con su PIN de entrega.
     *
     * "Primero en escanear, primero en repartir": el lockForUpdate()
     * garantiza que si dos repartidores escanean la misma guía casi
     * al mismo tiempo, solo el primero la reclama y el segundo recibe
     * un error claro en vez de una asignación duplicada.
     */
    public function claimForDelivery(Package $package, Driver $driver, int $userId): Package
    {
        if ($driver->status !== Driver::STATUS_ACTIVE) {
            throw new RuntimeException('Solo un repartidor activo puede reclamar pedidos.');
        }

        if ($driver->driver_type !== Driver::TYPE_DELIVERY) {
            throw new RuntimeException(
                'Solo los repartidores de entrega final pueden reclamar pedidos aquí. '
                . 'Los choferes de Hub siguen usando el sistema de rutas.'
            );
        }

        return DB::transaction(function () use ($package, $driver, $userId) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            // isClaimedForDelivery() no basta aquí: solo es cierto
            // cuando delivery_status === DELIVERY_ACCEPTED, pero un
            // paquete también puede tener driver_id asignado por la
            // ruta de un chofer de HUB/reparto (registerCollection(),
            // DeliveryAssignmentService::assign()) sin tocar
            // delivery_status. Si solo miráramos isClaimedForDelivery()
            // aquí, ese paquete se vería como "libre" y otro repartidor
            // distinto podría reclamarlo mientras el primero todavía lo
            // tiene físicamente.
            if ($locked->driver_id !== null) {
                if ((int) $locked->driver_id === (int) $driver->id) {
                    // Ya lo tenía él mismo: no es un error, solo lo devolvemos.
                    return $locked->fresh();
                }

                throw new RuntimeException('Este pedido ya fue reclamado por otro repartidor.');
            }

            if (! $locked->requires_delivery) {
                throw new RuntimeException('Este paquete no requiere entrega a domicilio.');
            }

            if (! in_array($locked->current_status, Package::CLAIMABLE_FOR_DELIVERY_STATUSES, true)) {
                throw new RuntimeException(
                    'Este paquete todavía no está listo para reparto. Estado actual: '
                    . $locked->statusLabel() . '.'
                );
            }

            $route = Route::query()
                ->where('driver_id', $driver->id)
                ->where('status', Route::STATUS_IN_PROGRESS)
                ->where('route_type', Route::TYPE_DELIVERY)
                ->latest('started_at')
                ->first();

            if (! $route) {
                throw new RuntimeException(
                    'Necesitas una ruta de reparto en curso para tomar entregas. '
                    . 'Toma e inicia una ruta antes de escanear.'
                );
            }

            $resolution = app(LogisticsResolutionService::class);

            if (! $resolution->isAtDestinationWarehouse($locked)) {
                throw new RuntimeException(
                    'Este paquete todavía no fue recibido en su HUB destino. No puede salir a reparto.'
                );
            }

            $assignmentService = app(DeliveryAssignmentService::class);

            if ($assignmentService->routeWarehouseId($route) !== (int) $locked->current_warehouse_id) {
                throw new RuntimeException('Este paquete no pertenece a la zona de tu ruta de reparto.');
            }

            $assignmentService->assign($locked, $route, $userId);

            return $locked->fresh();
        });
    }

    /**
     * Sale a reparto: el paquete pasa a EN_RUTA en manos de su
     * repartidor y se genera el PIN de entrega (6 dígitos). Solo se
     * guarda su hash; el PIN en claro se le envía por correo al
     * destinatario una vez confirmada la transacción. Si el
     * destinatario no tiene correo registrado no se genera PIN: el
     * repartidor entregará verificando su cédula y con una foto.
     *
     * Usado por DeliveryAssignmentService::assign() (Admin, Almacén y la
     * toma por escaneo) y por Driver\PackageDetail::startDelivery().
     */
    public function sendOutForDelivery(
        Package $package,
        int $userId,
        string $locationDescription,
        string $originLocation = 'Almacén destino',
    ): Package {
        $recipientEmail = $this->recipientEmail($package);
        $pin = $recipientEmail
            ? str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT)
            : null;

        $updatedPackage = DB::transaction(function () use ($package, $userId, $locationDescription, $originLocation, $pin) {
            $updated = $this->changeStatus(
                package: $package,
                newStatus: Package::STATUS_EN_RUTA,
                userId: $userId,
                locationDescription: $locationDescription,
                eventType: PackageHistory::EVENT_REPARTO,
                originLocation: $originLocation,
                destinationLocation: 'Repartidor',
                // El aviso al cliente es el correo con el PIN.
                notifyCustomer: false,
            );

            $updated->forceFill([
                'delivery_pin_hash' => $pin !== null ? Hash::make($pin) : null,
                'delivery_pin_generated_at' => $pin !== null ? now() : null,
                'delivery_pin_failed_attempts' => 0,
            ])->save();

            return $updated;
        });

        if ($pin !== null) {
            DB::afterCommit(fn () => $this->notifyDeliveryPin($updatedPackage, $recipientEmail, $pin));
        }

        return $updatedPackage;
    }

    /**
     * Correo del destinatario: el de su cuenta de cliente (customers,
     * por recipient_id_doc, igual que notifyStatusChange()) o, si la
     * guía viene de un pedido del marketplace, el que dio al comprar.
     */
    protected function recipientEmail(Package $package): ?string
    {
        $email = Customer::query()
            ->where('id_doc', $package->recipient_id_doc)
            ->whereNotNull('email')
            ->value('email');

        if (! $email) {
            $email = Pedido::query()
                ->where('package_id', $package->id)
                ->whereNotNull('cliente_email')
                ->value('cliente_email');
        }

        return $email ?: null;
    }

    protected function notifyDeliveryPin(Package $package, string $email, string $pin): void
    {
        try {
            Notification::route('mail', $email)
                ->notify(new DeliveryPinIssued($package->id, $pin));
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo enviar el PIN de entrega al destinatario.',
                [
                    'package_id' => $package->id,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ENTREGA
    |--------------------------------------------------------------------------
    */

    /**
     * El repartidor confirma la entrega a domicilio. Solo desde EN_RUTA
     * (el paquete salió a reparto con él) y verificando a quien recibe:
     *
     * - Con el PIN que se le envió al destinatario al salir a reparto
     *   (DELIVERY_CONFIRMATION_PIN). Cada PIN incorrecto cuenta; tras
     *   Package::DELIVERY_PIN_MAX_ATTEMPTS deja de aceptarse.
     * - Sin PIN (no le llegó, no lo tiene a mano o se agotaron los
     *   intentos): la cédula de quien recibe debe coincidir con la del
     *   destinatario y se exige una foto de la entrega
     *   (DELIVERY_CONFIRMATION_ID_DOC).
     *
     * No se entrega con un pago pendiente: un COD sin cobrar exige la
     * forma de pago y, si es electrónica (pago móvil, transferencia,
     * Zelle), el número de referencia; el comprobante es opcional.
     */
    public function completeDelivery(
        Package $package,
        Driver $driver,
        ?string $locationDescription = null,
        ?string $receiverName = null,
        ?string $receiverIdDoc = null,
        ?string $receiverPhone = null,
        ?string $deliveryPin = null,
        ?string $deliveryPhotoPath = null,
        ?string $codPaymentMethod = null,
        ?string $codPaymentReference = null,
        ?string $codPaymentProofPath = null,
    ): Package {
        $deliveryPin = trim((string) $deliveryPin);
        $receiverName = trim((string) $receiverName);
        $receiverIdDoc = trim((string) $receiverIdDoc);
        $codPaymentReference = trim((string) $codPaymentReference);

        // Un PIN incorrecto se cuenta aunque la entrega no se confirme:
        // por eso la transacción no lanza la excepción (revertiría el
        // contador) sino que devuelve el error, y se lanza después.
        $result = DB::transaction(function () use (
            $package,
            $driver,
            $locationDescription,
            $receiverName,
            $receiverIdDoc,
            $receiverPhone,
            $deliveryPin,
            $deliveryPhotoPath,
            $codPaymentMethod,
            $codPaymentReference,
            $codPaymentProofPath
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

            if ($lockedPackage->current_status !== Package::STATUS_EN_RUTA) {
                throw new RuntimeException(
                    'El paquete no está en ruta de entrega. Estado actual: '
                    .$lockedPackage->statusLabel().'.'
                );
            }

            if ($receiverName === '') {
                throw new RuntimeException('Indica el nombre de quien recibe.');
            }

            if ($deliveryPin !== '') {
                if (! $lockedPackage->acceptsDeliveryPin()) {
                    throw new RuntimeException(
                        $lockedPackage->delivery_pin_hash === null
                            ? 'Esta guía no tiene PIN de entrega: confirma con la cédula del destinatario y una foto.'
                            : 'Se agotaron los intentos de PIN para esta guía: confirma con la cédula del destinatario y una foto.'
                    );
                }

                if (! Hash::check($deliveryPin, $lockedPackage->delivery_pin_hash)) {
                    $lockedPackage->increment('delivery_pin_failed_attempts');

                    $remaining = Package::DELIVERY_PIN_MAX_ATTEMPTS
                        - (int) $lockedPackage->delivery_pin_failed_attempts;

                    return $remaining > 0
                        ? "PIN incorrecto. Te quedan {$remaining} intento(s)."
                        : 'PIN incorrecto. Se agotaron los intentos: confirma con la cédula del destinatario y una foto.';
                }

                $confirmationMethod = Package::DELIVERY_CONFIRMATION_PIN;
            } else {
                if (! Package::idDocsMatch($receiverIdDoc, $lockedPackage->recipient_id_doc)) {
                    throw new RuntimeException(
                        $receiverIdDoc === ''
                            ? 'Sin PIN, indica la cédula del destinatario para confirmar la entrega.'
                            : 'La cédula no coincide con la del destinatario. Sin PIN, solo el destinatario puede recibir el paquete.'
                    );
                }

                if (! $deliveryPhotoPath) {
                    throw new RuntimeException(
                        'Sin PIN, adjunta una foto de la entrega para confirmarla.'
                    );
                }

                $confirmationMethod = Package::DELIVERY_CONFIRMATION_ID_DOC;
            }

            if ($lockedPackage->is_cod && ! $lockedPackage->cod_collected_at) {
                if (! $codPaymentMethod || ! in_array($codPaymentMethod, Package::PAYMENT_METHODS, true)) {
                    throw new RuntimeException(
                        'Este pedido es contra entrega (COD): indica la forma de pago con la que te cancelaron antes de confirmar la entrega.'
                    );
                }

                if (
                    in_array($codPaymentMethod, Package::PAYMENT_METHODS_REQUIRING_REFERENCE, true)
                    && $codPaymentReference === ''
                ) {
                    throw new RuntimeException(
                        'Indica el número de referencia del pago ('
                        .(Package::PAYMENT_METHOD_LABELS[$codPaymentMethod] ?? $codPaymentMethod)
                        .') antes de confirmar la entrega.'
                    );
                }

                $lockedPackage->cod_collected_at = now();
                $lockedPackage->cod_collected_by_user_id = $driver->user_id;
                $lockedPackage->cod_payment_method = $codPaymentMethod;
                $lockedPackage->cod_payment_reference = $codPaymentReference !== '' ? $codPaymentReference : null;
                $lockedPackage->cod_payment_proof_path = $codPaymentProofPath;
            }

            $lockedPackage->fill([
                'current_status' => Package::STATUS_ENTREGADO,
                'delivery_status' => Package::DELIVERY_COMPLETED,
                'delivery_completed_at' => now(),
                'driver_remuneration_status' => Package::REMUNERATION_PENDING,
                'receiver_name' => $receiverName,
                'receiver_id_doc' => $receiverIdDoc !== '' ? $receiverIdDoc : null,
                'receiver_phone' => $receiverPhone,
                'delivery_confirmation_method' => $confirmationMethod,
                'delivery_photo_path' => $deliveryPhotoPath,
                // Ya entregado, el PIN no sirve para nada más.
                'delivery_pin_hash' => null,
            ])->save();

            app(DriverPaymentService::class)->createForDeliveredPackage(
                $lockedPackage,
                $driver
            );

            $this->recordHistory(
                package: $lockedPackage,
                status: Package::STATUS_ENTREGADO,
                userId: $driver->user_id,
                locationDescription:
                    ($locationDescription ?? 'Entrega completada por el repartidor')
                    .($confirmationMethod === Package::DELIVERY_CONFIRMATION_PIN
                        ? ' (verificada con PIN)'
                        : ' (sin PIN: verificada con cédula y foto)'),
                eventType: PackageHistory::EVENT_ENTREGA,
                originLocation: 'Dirección de entrega',
                destinationLocation: 'Destinatario',
            );

            return $lockedPackage->fresh();
        });

        if (is_string($result)) {
            throw new RuntimeException($result);
        }

        $this->notifyStatusChange($result, Package::STATUS_ENTREGADO);

        return $result;
    }

    /**
     * Completa un retiro presencial en la agencia destino.
     * No genera remuneración de repartidor porque no hubo entrega a domicilio.
     */
    public function completeAgencyPickup(
        Package $package,
        int $userId,
        string $recipientIdDoc,
        ?string $locationDescription = null,
        ?string $originLocation = null,
    ): Package {
        $updatedPackage = DB::transaction(function () use (
            $package,
            $userId,
            $recipientIdDoc,
            $locationDescription,
            $originLocation
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
                originLocation: $originLocation ?? 'Agencia destino',
                destinationLocation: 'Destinatario',
            );

            return $locked->fresh();
        });

        $this->notifyStatusChange($updatedPackage, Package::STATUS_ENTREGADO);

        return $updatedPackage;
    }

    /*
    |--------------------------------------------------------------------------
    | DEVOLUCIÓN AL REMITENTE
    |--------------------------------------------------------------------------
    |
    | Un admin la inicia sobre un paquete que no se pudo entregar
    | (startReturn) y la agencia de ORIGEN la cierra al entregárselo
    | al remitente verificando su cédula (completeReturn). El traslado
    | físico de regreso lo coordina operaciones: no pasa por escaneos
    | de ruta. Sin reembolso ni cargo extra: el envío y la comisión
    | del aliado se mantienen; un COD pendiente se cancela porque el
    | paquete nunca llegó al destinatario.
    |
    */

    public function startReturn(Package $package, int $userId, string $reason): Package
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new RuntimeException('Indica el motivo de la devolución.');
        }

        $updatedPackage = DB::transaction(function () use ($package, $userId, $reason) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->isReturnable()) {
                throw new RuntimeException(match ($locked->current_status) {
                    Package::STATUS_RECIBIDO_AGENCIA => 'Esta guía todavía no ha salido de la agencia de origen: no hay nada que devolver por la red.',
                    Package::STATUS_ENTREGADO => 'Esta guía ya fue entregada al destinatario.',
                    Package::STATUS_EN_DEVOLUCION => 'Esta guía ya está en devolución.',
                    Package::STATUS_DEVUELTO => 'Esta guía ya fue devuelta al remitente.',
                    default => 'Esta guía no se puede devolver en su estado actual.',
                });
            }

            $previousStatusLabel = $locked->statusLabel();

            $updates = [
                'current_status' => Package::STATUS_EN_DEVOLUCION,
                'return_reason' => $reason,
                'return_requested_at' => now(),
                // Nadie la tiene en custodia dentro del sistema hasta
                // que la agencia de origen la entregue: así deja de
                // aparecer como pendiente en el panel/app del repartidor.
                'driver_id' => null,
                // Si había salido a reparto, el PIN ya no sirve.
                'delivery_pin_hash' => null,
            ];

            if ($locked->is_cod && $locked->cod_status === Package::COD_PENDIENTE) {
                $updates['cod_status'] = Package::COD_CANCELADO;
            }

            $locked->update($updates);

            $this->recordHistory(
                package: $locked,
                status: Package::STATUS_EN_DEVOLUCION,
                userId: $userId,
                locationDescription: 'Devolución al remitente iniciada. Motivo: '.$reason,
                eventType: PackageHistory::EVENT_DEVOLUCION,
                originLocation: $previousStatusLabel,
                destinationLocation: 'Agencia de origen',
            );

            return $locked->fresh();
        });

        $this->notifyReturnStatus($updatedPackage, Package::STATUS_EN_DEVOLUCION);

        return $updatedPackage;
    }

    public function completeReturn(Package $package, int $userId, string $senderIdDoc, Ally $originAlly): Package
    {
        $updatedPackage = DB::transaction(function () use ($package, $userId, $senderIdDoc, $originAlly) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $locked->ally_id !== (int) $originAlly->id) {
                throw new RuntimeException('Esta guía no se registró en tu agencia.');
            }

            if (! $locked->isInReturn()) {
                throw new RuntimeException(
                    'Esta guía no está en devolución. Estado actual: '.$locked->statusLabel().'.'
                );
            }

            if (trim((string) $locked->sender_id_doc) !== trim($senderIdDoc)) {
                throw new RuntimeException('El documento no coincide con el del remitente.');
            }

            $locked->update([
                'current_status' => Package::STATUS_DEVUELTO,
                'returned_at' => now(),
            ]);

            $this->recordHistory(
                package: $locked,
                status: Package::STATUS_DEVUELTO,
                userId: $userId,
                locationDescription: 'Devuelto al remitente en la agencia de origen',
                eventType: PackageHistory::EVENT_DEVOLUCION,
                originLocation: 'Agencia de origen',
                destinationLocation: 'Remitente',
            );

            return $locked->fresh();
        });

        $this->notifyReturnStatus($updatedPackage, Package::STATUS_DEVUELTO);

        return $updatedPackage;
    }

    /**
     * En una devolución el interesado principal es el remitente (es
     * quien recupera el paquete), así que además del destinatario
     * (notifyStatusChange) se le avisa a él.
     */
    protected function notifyReturnStatus(Package $package, string $status): void
    {
        $this->notifyStatusChange($package, $status);

        try {
            $senderEmail = Customer::query()
                ->where('id_doc', $package->sender_id_doc)
                ->whereNotNull('email')
                ->value('email');

            $recipientEmail = Customer::query()
                ->where('id_doc', $package->recipient_id_doc)
                ->value('email');

            if (! $senderEmail || $senderEmail === $recipientEmail) {
                return;
            }

            Notification::route('mail', $senderEmail)
                ->notify(new PackageStatusUpdated($package->id, $status));
        } catch (Throwable $e) {
            Log::warning(
                'No se pudo avisar al remitente de la devolución.',
                [
                    'package_id' => $package->id,
                    'status' => $status,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | COD
    |--------------------------------------------------------------------------
    */

    public function collectCod(Package $package, int $userId, ?Driver $driver = null): Package
    {
        return DB::transaction(function () use ($package, $userId, $driver) {
            $locked = Package::query()
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($driver !== null && (int) $locked->driver_id !== (int) $driver->id) {
                throw new RuntimeException(
                    'Este paquete no está asignado a este repartidor.'
                );
            }

            // Igual que completeDelivery(): un repartidor suspendido o
            // rechazado no puede seguir registrando cobros aunque su
            // token todavía no se haya revocado.
            if ($driver !== null && $driver->status !== Driver::STATUS_ACTIVE) {
                throw new RuntimeException('El repartidor no está activo.');
            }

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

            // Igual que completeDelivery()/scanCollection(): toda
            // acción que cambia algo relevante del paquete deja un
            // renglón en el historial, para que el cierre de caja y
            // la conciliación tengan de dónde reconstruir cuándo se
            // cobró el COD.
            $this->recordHistory(
                package: $locked,
                status: $locked->current_status,
                userId: $userId,
                locationDescription: 'Cobro COD registrado',
                eventType: PackageHistory::EVENT_MOVIMIENTO,
            );

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

        $amount = Money::round(
            Money::mul($totalPriceUsd, Money::div($percentage, 100)),
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
