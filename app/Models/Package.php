<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    use HasFactory;

    /**
     * Máquina de estados de Venexpress, en orden de flujo normal.
     */
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

    public const TYPE_SOBRE = 'sobre';
    public const TYPE_PAQUETE = 'paquete';

    public const TYPES = [
        self::TYPE_SOBRE,
        self::TYPE_PAQUETE,
    ];

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tracking_number',
        'ally_id',
        'driver_id',
        'sender_name',
        'sender_id_doc',
        'sender_phone',
        'recipient_name',
        'recipient_id_doc',
        'recipient_phone',
        'origin_city',
        'destination_city',
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
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_fragile' => 'boolean',
            'has_insurance' => 'boolean',
            'declared_value_usd' => 'decimal:2',
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
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Taquilla aliada que registró este paquete.
     */
    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Chofer actualmente asignado a este paquete (puede ser null).
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Historial completo de estados de este paquete.
     */
    public function histories(): HasMany
    {
        return $this->hasMany(PackageHistory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isDelivered(): bool
    {
        return $this->current_status === self::STATUS_ENTREGADO;
    }

    public function isSobre(): bool
    {
        return $this->package_type === self::TYPE_SOBRE;
    }
}
