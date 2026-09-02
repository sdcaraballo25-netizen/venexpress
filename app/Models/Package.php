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

    /**
     * Etiquetas legibles en español para cada estado.
     */
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

    /**
     * Estados de liquidación del cobro en destino (COD).
     */
    public const COD_PENDIENTE = 'pendiente';
    public const COD_LIQUIDADO = 'liquidado';

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
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
        'is_cod',
        'payment_method',
        'cod_amount_usd',
        'cod_status',
        'cod_liquidated_at',
        'commission_percentage_used',
        'commission_amount_usd',
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
            'distance_km' => 'integer',
            'requires_delivery' => 'boolean',
            'delivery_fee_usd' => 'decimal:2',
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
            'commission_percentage_used' => 'decimal:2',
            'commission_amount_usd' => 'decimal:2',
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

    /**
     * Incidencias reportadas sobre este paquete (RF-ALI-06).
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
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

    /**
     * Etiqueta legible en español del estado actual del paquete.
     */
    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->current_status] ?? $this->current_status;
    }

    public function isSobre(): bool
    {
        return $this->package_type === self::TYPE_SOBRE;
    }

    public function isCodPending(): bool
    {
        return $this->is_cod && $this->cod_status === self::COD_PENDIENTE;
    }

    /*
    |--------------------------------------------------------------------------
    | CÓDIGO DE SEGURIDAD (ANTI-ALTERACIÓN)
    |--------------------------------------------------------------------------
    */

    /**
     * Calcula el código de seguridad de una guía a partir de los
     * datos que quedan fijos en el momento de su creación (snapshot):
     * número de guía, agencia, peso físico y fecha de creación.
     *
     * Usa HMAC-SHA256 con la app key de Laravel como llave secreta,
     * para que nadie pueda recalcular un código válido sin acceso al
     * servidor. Se trunca a 10 caracteres solo para que quepa cómodo
     * en la etiqueta impresa; la verificación vuelve a calcular el
     * mismo valor truncado y lo compara.
     */
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

        $fullHash = hash_hmac('sha256', $payload, config('app.key'));

        return strtoupper(substr($fullHash, -10));
    }

    /**
     * Verifica que el código de seguridad guardado siga siendo
     * consistente con los datos actuales del paquete. Si alguien
     * modificó el peso, la agencia, o el número de guía después de
     * haberse generado la etiqueta, esta verificación falla.
     *
     * IMPORTANTE: esto detecta alteraciones en el registro dentro
     * del sistema (base de datos), no alteraciones físicas hechas
     * directamente sobre el papel de la etiqueta.
     */
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

        return hash_equals($expected, $this->security_hash);
    }
}
