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
    public const STATUS_ENTREGADO = 'ENTREGADO';

    public const STATUSES = [
        self::STATUS_RECIBIDO_AGENCIA,
        self::STATUS_RECOLECTADO_VENEXPRESS,
        self::STATUS_EN_HUB,
        self::STATUS_EN_TRANSITO_NACIONAL,
        self::STATUS_LISTO_RETIRO,
        self::STATUS_ENTREGADO,
    ];

    public const STATUS_LABELS = [
        self::STATUS_RECIBIDO_AGENCIA => 'Recibido en Agencia',
        self::STATUS_RECOLECTADO_VENEXPRESS => 'Recolectado',
        self::STATUS_EN_HUB => 'En Hub',
        self::STATUS_EN_TRANSITO_NACIONAL => 'En Tránsito',
        self::STATUS_LISTO_RETIRO => 'Listo para Retiro',
        self::STATUS_ENTREGADO => 'Entregado',
    ];

    public const TYPE_SOBRE = 'sobre';
    public const TYPE_PAQUETE = 'paquete';

    public const TYPES = [
        self::TYPE_SOBRE,
        self::TYPE_PAQUETE,
    ];

    public const COD_PENDIENTE = 'pendiente';
    public const COD_LIQUIDADO = 'liquidado';

    public const DELIVERY_PENDING = 'pendiente';
    public const DELIVERY_ACCEPTED = 'aceptada';
    public const DELIVERY_REJECTED = 'rechazada';
    public const DELIVERY_COMPLETED = 'completada';

    public const REMUNERATION_PENDING = 'pendiente';
    public const REMUNERATION_PAID = 'pagada';
    public const REMUNERATION_CANCELLED = 'cancelada';

    protected $fillable = [
        'tracking_number',
        'security_hash',
        'ally_id',
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

        'commission_percentage_used',
        'commission_amount_usd',
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

            'delivery_accepted_at' => 'datetime',
            'delivery_rejected_at' => 'datetime',
            'delivery_completed_at' => 'datetime',

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
            'cod_collected_at' => 'datetime',

            'commission_percentage_used' => 'decimal:2',
            'commission_amount_usd' => 'decimal:2',
        ];
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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
