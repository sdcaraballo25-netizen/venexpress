<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    public const STATUS_RECIBIDO_AGENCIA = 'RECIBIDO_AGENCIA';
    public const STATUS_RECOLECTADO_VENEXPRESS = 'RECOLECTADO_VENEXPRESS';
    public const STATUS_EN_HUB = 'EN_HUB';
    public const STATUS_EN_TRANSITO_NACIONAL = 'EN_TRANSITO_NACIONAL';
    public const STATUS_LISTO_RETIRO = 'LISTO_RETIRO';

    /**
     * Entrega a domicilio (requires_delivery): el paquete espera
     * repartidor en su almacén destino (PENDIENTE_ENTREGA), un
     * repartidor lo lleva (EN_RUTA) o no lo pudo entregar
     * (ENTREGA_FALLIDA). LISTO_RETIRO queda solo para el retiro en
     * persona en agencia o almacén.
     */
    public const STATUS_PENDIENTE_ENTREGA = 'PENDIENTE_ENTREGA';
    public const STATUS_EN_RUTA = 'EN_RUTA';
    public const STATUS_ENTREGA_FALLIDA = 'ENTREGA_FALLIDA';

    public const STATUS_ENTREGADO = 'ENTREGADO';

    /**
     * Devolución al remitente (ver PackageService::startReturn() y
     * completeReturn()). Fuera del flujo normal: solo esos dos métodos
     * mueven un paquete a estos estados — no figuran en las
     * transiciones genéricas de PackageService::changeStatus().
     */
    public const STATUS_EN_DEVOLUCION = 'EN_DEVOLUCION';
    public const STATUS_DEVUELTO = 'DEVUELTO';

    public const STATUSES = [
        self::STATUS_RECIBIDO_AGENCIA,
        self::STATUS_RECOLECTADO_VENEXPRESS,
        self::STATUS_EN_HUB,
        self::STATUS_EN_TRANSITO_NACIONAL,
        self::STATUS_LISTO_RETIRO,
        self::STATUS_PENDIENTE_ENTREGA,
        self::STATUS_EN_RUTA,
        self::STATUS_ENTREGA_FALLIDA,
        self::STATUS_ENTREGADO,
        self::STATUS_EN_DEVOLUCION,
        self::STATUS_DEVUELTO,
    ];

    public const STATUS_LABELS = [
        self::STATUS_RECIBIDO_AGENCIA => 'Recibido en Agencia',
        self::STATUS_RECOLECTADO_VENEXPRESS => 'Recolectado',
        self::STATUS_EN_HUB => 'En Hub',
        self::STATUS_EN_TRANSITO_NACIONAL => 'En Tránsito',
        self::STATUS_LISTO_RETIRO => 'Listo para Retiro',
        self::STATUS_PENDIENTE_ENTREGA => 'Pendiente de entrega',
        self::STATUS_EN_RUTA => 'En ruta de entrega',
        self::STATUS_ENTREGA_FALLIDA => 'Entrega fallida',
        self::STATUS_ENTREGADO => 'Entregado',
        self::STATUS_EN_DEVOLUCION => 'En devolución',
        self::STATUS_DEVUELTO => 'Devuelto al remitente',
    ];

    /**
     * Estados desde los que un admin puede iniciar una devolución: el
     * paquete ya salió de la agencia de origen y todavía no se
     * entregó. En RECIBIDO_AGENCIA sigue en el origen, así que no hay
     * nada que devolver por la red.
     */
    public const RETURNABLE_STATUSES = [
        self::STATUS_RECOLECTADO_VENEXPRESS,
        self::STATUS_EN_HUB,
        self::STATUS_EN_TRANSITO_NACIONAL,
        self::STATUS_LISTO_RETIRO,
        self::STATUS_PENDIENTE_ENTREGA,
        self::STATUS_EN_RUTA,
        self::STATUS_ENTREGA_FALLIDA,
    ];

    /**
     * Estados desde los que un repartidor de entrega puede tomar un
     * paquete (o Admin/Almacén asignárselo): solo PENDIENTE_ENTREGA, es
     * decir, ya recibido en su almacén destino y sin repartidor. Un
     * paquete EN_TRANSITO_NACIONAL (todavía viajando entre HUBs) nunca
     * es tomable.
     *
     * Ver DeliveryAssignmentService::assign(), que además exige que el
     * paquete esté en el almacén de la zona de la ruta y lo pasa a
     * EN_RUTA (generando el PIN de entrega).
     */
    public const CLAIMABLE_FOR_DELIVERY_STATUSES = [
        self::STATUS_PENDIENTE_ENTREGA,
    ];

    /**
     * Cómo se verificó a quien recibió una entrega a domicilio: con el
     * PIN que se le envió al destinatario al salir a reparto, o, si no
     * lo tiene, con su cédula (que debe coincidir con la del
     * destinatario) y una foto de la entrega.
     */
    public const DELIVERY_CONFIRMATION_PIN = 'pin';

    public const DELIVERY_CONFIRMATION_ID_DOC = 'cedula';

    /**
     * Sin PIN, lo recibió un tercero autorizado por el destinatario:
     * se registra su nombre y cédula, una foto de su cédula y una de la
     * copia de la cédula del destinatario.
     */
    public const DELIVERY_CONFIRMATION_THIRD_PARTY = 'tercero_autorizado';

    public const DELIVERY_CONFIRMATION_METHODS = [
        self::DELIVERY_CONFIRMATION_PIN,
        self::DELIVERY_CONFIRMATION_ID_DOC,
        self::DELIVERY_CONFIRMATION_THIRD_PARTY,
    ];

    /**
     * Motivos por los que un repartidor no pudo entregar (EN_RUTA ->
     * ENTREGA_FALLIDA, ver PackageService::markDeliveryFailed()).
     */
    public const FAILED_DELIVERY_REASON_LABELS = [
        'CLIENTE_AUSENTE' => 'Destinatario ausente',
        'DIRECCION_INCORRECTA' => 'Dirección incorrecta o no encontrada',
        'RECHAZADO_POR_CLIENTE' => 'El destinatario lo rechazó',
        'SIN_PAGO' => 'No pagó el cobro contra entrega',
        'ZONA_INACCESIBLE' => 'Zona inaccesible o insegura',
        'OTRO' => 'Otro',
    ];

    /**
     * Intentos fallidos de PIN tras los cuales el PIN deja de
     * aceptarse para esa guía (evita adivinarlo probando): la entrega
     * solo puede confirmarse con cédula + foto.
     */
    public const DELIVERY_PIN_MAX_ATTEMPTS = 5;

    public const TYPE_SOBRE = 'sobre';
    public const TYPE_PAQUETE = 'paquete';

    public const TYPES = [
        self::TYPE_SOBRE,
        self::TYPE_PAQUETE,
    ];

    public const COD_PENDIENTE = 'pendiente';
    public const COD_LIQUIDADO = 'liquidado';

    /**
     * COD de un paquete devuelto al remitente: nunca se entregó, así
     * que no hay nada que cobrar en destino.
     */
    public const COD_CANCELADO = 'cancelado';

    /**
     * Formas de pago aceptadas, usadas tanto por el aliado al
     * registrar el pedido (payment_method) como por el repartidor al
     * confirmar el cobro contra entrega (cod_payment_method).
     */
    public const PAYMENT_METHODS = [
        'efectivo_usd',
        'efectivo_ves',
        'pago_movil',
        'transferencia',
        'punto_venta',
        'zelle',
    ];

    /**
     * Formas de pago de un cobro contra entrega que dejan un número de
     * referencia: el repartidor debe registrarlo (y puede adjuntar el
     * comprobante) antes de entregar.
     */
    public const PAYMENT_METHODS_REQUIRING_REFERENCE = [
        'pago_movil',
        'transferencia',
        'zelle',
    ];

    public const PAYMENT_METHOD_LABELS = [
        'efectivo_usd' => 'Efectivo (USD)',
        'efectivo_ves' => 'Efectivo (VES)',
        'pago_movil' => 'Pago móvil',
        'transferencia' => 'Transferencia',
        'punto_venta' => 'Punto de venta',
        'zelle' => 'Zelle',
    ];

    public const DELIVERY_PENDING = 'pendiente';
    public const DELIVERY_ACCEPTED = 'aceptada';
    public const DELIVERY_REJECTED = 'rechazada';
    public const DELIVERY_COMPLETED = 'completada';

    public const REMUNERATION_PENDING = 'pendiente';
    public const REMUNERATION_PAID = 'pagada';
    public const REMUNERATION_CANCELLED = 'cancelada';

    /**
     * Modalidad de destino final cuando requires_delivery = false.
     * NULL cuando el pedido requiere delivery — el flujo de Delivery
     * no usa este campo.
     */
    public const PICKUP_MODE_HUB = 'hub';

    public const PICKUP_MODE_ALLY = 'ally';

    public const PICKUP_MODES = [
        self::PICKUP_MODE_HUB,
        self::PICKUP_MODE_ALLY,
    ];

    protected $fillable = [
        'tracking_number',
        'security_hash',
        'ally_id',
        'pickup_ally_id',
        'pickup_mode',
        'destination_warehouse_id',
        'current_warehouse_id',
        'destination_resolution_status',
        'registered_by_user_id',
        'driver_id',

        'sender_name',
        'sender_id_doc',
        'sender_phone',

        'recipient_name',
        'recipient_id_doc',
        'recipient_phone',

        'origin_city',
        'origin_state',
        'destination_city',
        'destination_state',
        'distance_km',

        'requires_delivery',
        'delivery_address',
        'delivery_sector',
        'delivery_reference',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_geocoded_at',
        'delivery_fee_usd',

        'package_type',
        'is_fragile',
        'has_insurance',
        'declared_value_usd',

        'physical_weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'volumetric_weight_kg',
        'billable_weight_kg',

        'fragile_surcharge_usd',
        'insurance_price_usd',
        'total_price_usd',
        'total_price_ves',
        'bcv_rate_used',

        'current_status',

        'delivery_status',
        'delivery_accepted_at',
        'delivery_rejected_at',
        'delivery_completed_at',
        'delivery_confirmation_method',
        'receiver_name',
        'receiver_id_doc',
        'receiver_phone',
        'delivery_photo_path',
        'customer_confirmed_at',
        'customer_confirmed_by',
        'delivery_rejection_reason',

        'driver_remuneration_usd',
        'driver_remuneration_status',
        'driver_remuneration_paid_at',

        'is_cod',
        'payment_method',
        'cod_amount_usd',
        'cod_status',
        'cod_liquidated_at',
        'cod_collected_at',
        'cod_collected_by_user_id',
        'cod_payment_method',
        'cod_payment_reference',
        'cod_payment_proof_path',

        'delivery_pin_hash',
        'delivery_pin_generated_at',
        'delivery_pin_failed_attempts',

        'delivery_attempts',
        'failed_delivery_reason',
        'failed_delivery_notes',
        'failed_delivery_at',

        'received_by_third_party',
        'third_party_id_photo_path',
        'recipient_id_copy_path',

        'commission_percentage_used',
        'commission_amount_usd',

        'return_reason',
        'return_requested_at',
        'returned_at',
    ];

    /**
     * El hash del PIN de entrega nunca sale del servidor (API, JSON).
     */
    protected $hidden = [
        'delivery_pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_fragile' => 'boolean',
            'has_insurance' => 'boolean',

            'declared_value_usd' => 'decimal:2',
            'distance_km' => 'integer',

            'requires_delivery' => 'boolean',
            'delivery_fee_usd' => 'decimal:2',
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'delivery_geocoded_at' => 'datetime',

            'delivery_accepted_at' => 'datetime',
            'delivery_rejected_at' => 'datetime',
            'delivery_completed_at' => 'datetime',
            'customer_confirmed_at' => 'datetime',

            'driver_remuneration_usd' => 'decimal:2',
            'driver_remuneration_paid_at' => 'datetime',

            'physical_weight_kg' => 'decimal:3',
            'length_cm' => 'decimal:2',
            'width_cm' => 'decimal:2',
            'height_cm' => 'decimal:2',

            'volumetric_weight_kg' => 'decimal:3',
            'billable_weight_kg' => 'decimal:3',

            'fragile_surcharge_usd' => 'decimal:2',
            'insurance_price_usd' => 'decimal:2',

            'total_price_usd' => 'decimal:2',
            'total_price_ves' => 'decimal:2',
            'bcv_rate_used' => 'decimal:6',

            'is_cod' => 'boolean',
            'cod_amount_usd' => 'decimal:2',
            'cod_liquidated_at' => 'datetime',

            'return_requested_at' => 'datetime',
            'returned_at' => 'datetime',
            'cod_collected_at' => 'datetime',

            'delivery_pin_generated_at' => 'datetime',
            'delivery_pin_failed_attempts' => 'integer',

            'delivery_attempts' => 'integer',
            'failed_delivery_at' => 'datetime',
            'received_by_third_party' => 'boolean',

            'commission_percentage_used' => 'decimal:2',
            'commission_amount_usd' => 'decimal:2',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Aliado verificado elegido por el cliente como punto de retiro,
     * cuando el pedido no requiere delivery (requires_delivery =
     * false). Distinto de ally(), que es siempre el Aliado de origen
     * donde se registró el pedido.
     */
    public function pickupAlly(): BelongsTo
    {
        return $this->belongsTo(Ally::class, 'pickup_ally_id');
    }

    /**
     * HUB propio que LogisticsResolutionService resuelve como destino
     * logístico de este paquete (Fase 4). No se persiste
     * automáticamente al registrar el pedido: la resolución es en
     * vivo (ver LogisticsResolutionService::resolveForPackage), esta
     * columna queda disponible para cuando una fase posterior decida
     * fijarla de forma explícita.
     */
    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    /**
     * HUB propio donde el paquete se encuentra físicamente en este
     * momento. Todavía ningún flujo de escaneo/recepción lo
     * actualiza (Fase 5+); por ahora solo existe para que
     * LogisticsResolutionService::isAtDestinationWarehouse() lo pueda
     * comparar contra el destino resuelto.
     */
    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Usuario (Aliado Administrador o Taquilla) que registró esta
     * guía — usado para agrupar ventas por taquilla en el cierre del
     * día. Null en guías creadas antes de esta columna.
     */
    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PackageHistory::class);
    }

    public function driverPayments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function isDelivered(): bool
    {
        return $this->current_status === self::STATUS_ENTREGADO;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->current_status]
            ?? $this->current_status;
    }

    /**
     * Permite usar:
     *
     * {{ $package->status_label }}
     *
     * además de:
     *
     * $package->statusLabel()
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->statusLabel();
    }

    public function isReturnable(): bool
    {
        return in_array($this->current_status, self::RETURNABLE_STATUSES, true);
    }

    public function isInReturn(): bool
    {
        return $this->current_status === self::STATUS_EN_DEVOLUCION;
    }

    public function isDeliveryFailed(): bool
    {
        return $this->current_status === self::STATUS_ENTREGA_FALLIDA;
    }

    public function failedDeliveryReasonLabel(): ?string
    {
        return $this->failed_delivery_reason
            ? (self::FAILED_DELIVERY_REASON_LABELS[$this->failed_delivery_reason] ?? $this->failed_delivery_reason)
            : null;
    }

    public function isOutForDelivery(): bool
    {
        return $this->current_status === self::STATUS_EN_RUTA;
    }

    /**
     * Si el repartidor todavía puede confirmar la entrega con el PIN:
     * se generó uno al salir a reparto y no se agotaron los intentos.
     */
    public function acceptsDeliveryPin(): bool
    {
        return $this->delivery_pin_hash !== null
            && (int) $this->delivery_pin_failed_attempts < self::DELIVERY_PIN_MAX_ATTEMPTS;
    }

    /**
     * Compara dos documentos de identidad ignorando mayúsculas,
     * espacios, puntos y guiones ("V-12.345.678" = "v12345678"). Si
     * uno trae letra de nacionalidad y el otro no, compara solo los
     * números.
     */
    public static function idDocsMatch(?string $a, ?string $b): bool
    {
        $normalize = fn (?string $doc) => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $doc));

        $a = $normalize($a);
        $b = $normalize($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b) {
            return true;
        }

        $digitsA = preg_replace('/\D/', '', $a);
        $digitsB = preg_replace('/\D/', '', $b);
        $lettersA = preg_replace('/\d/', '', $a);
        $lettersB = preg_replace('/\d/', '', $b);

        return $digitsA !== ''
            && $digitsA === $digitsB
            && ($lettersA === '' || $lettersB === '');
    }

    public function isSobre(): bool
    {
        return $this->package_type === self::TYPE_SOBRE;
    }

    public function isCodPending(): bool
    {
        return $this->is_cod
            && $this->cod_status === self::COD_PENDIENTE;
    }

    public function deliveryAccepted(): bool
    {
        return $this->delivery_status === self::DELIVERY_ACCEPTED;
    }

    /**
     * A partir de este cambio de arquitectura, 'delivery_status' deja
     * de representar "el cliente aceptó la entrega" (ese flujo nunca
     * se construyó) y pasa a representar el estado de reclamo del
     * repartidor: pendiente = nadie lo ha tomado, aceptada = un
     * repartidor lo reclamó y lo está entregando.
     */
    public function isClaimedForDelivery(): bool
    {
        return $this->driver_id !== null
            && $this->delivery_status === self::DELIVERY_ACCEPTED;
    }

    public function isAvailableForDeliveryClaim(): bool
    {
        return $this->driver_id === null
            && $this->requires_delivery
            && in_array($this->current_status, self::CLAIMABLE_FOR_DELIVERY_STATUSES, true)
            && ($this->delivery_status === null || $this->delivery_status === self::DELIVERY_PENDING);
    }

    /**
     * Paquetes listos para que cualquier repartidor de entrega los
     * reclame: requieren entrega a domicilio, están en un estado
     * reclamable (ver CLAIMABLE_FOR_DELIVERY_STATUSES), nadie los ha
     * tomado por delivery_status (isClaimedForDelivery() usa esa
     * marca) y tampoco tienen ya un driver_id asignado — por ejemplo,
     * uno que RouteService::cancel() acaba de liberar pero que otro
     * repartidor podría estar sosteniendo físicamente todavía.
     */
    public function scopeAvailableForDeliveryClaim($query)
    {
        return $query
            ->whereNull('driver_id')
            ->where('requires_delivery', true)
            ->whereIn('current_status', self::CLAIMABLE_FOR_DELIVERY_STATUSES)
            ->where(function ($q) {
                $q->whereNull('delivery_status')
                    ->orWhere('delivery_status', self::DELIVERY_PENDING);
            });
    }

    public function deliveryRejected(): bool
    {
        return $this->delivery_status === self::DELIVERY_REJECTED;
    }

    public function deliveryCompleted(): bool
    {
        return $this->delivery_status === self::DELIVERY_COMPLETED;
    }

    public function driverRemunerationPaid(): bool
    {
        return $this->driver_remuneration_status
            === self::REMUNERATION_PAID;
    }

    public static function computeSecurityHash(
        string $trackingNumber,
        int $allyId,
        float $physicalWeightKg,
        \DateTimeInterface $createdAt
    ): string {
        $payload = implode('|', [
            $trackingNumber,
            $allyId,
            number_format($physicalWeightKg, 3, '.', ''),
            $createdAt->format('Y-m-d H:i:s'),
        ]);

        $fullHash = hash_hmac(
            'sha256',
            $payload,
            config('app.key')
        );

        return strtoupper(substr($fullHash, -10));
    }

    public function verifySecurityHash(): bool
    {
        if (! $this->security_hash || ! $this->created_at) {
            return false;
        }

        $expected = self::computeSecurityHash(
            $this->tracking_number,
            (int) $this->ally_id,
            (float) $this->physical_weight_kg,
            $this->created_at,
        );

        return hash_equals(
            $expected,
            $this->security_hash
        );
    }
}
