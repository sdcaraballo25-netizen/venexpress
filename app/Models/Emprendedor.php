<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Emprendedor extends Model
{
    use HasFactory;

    /**
     * Eloquent pluraliza "Emprendedor" como "emprendedors" (solo
     * conoce reglas de inglés), así que hay que fijar la tabla real.
     */
    protected $table = 'emprendedores';

    public const STATUS_PENDING = 'PENDIENTE';

    public const STATUS_ACTIVE = 'ACTIVO';

    public const STATUS_REJECTED = 'RECHAZADO';

    public const STATUS_SUSPENDED = 'SUSPENDIDO';

    /**
     * Estados de VERIFICACIÓN DE IDENTIDAD, separados del estado
     * operativo (`status`, sin cambios). Un emprendedor solo puede
     * publicar/vender en el Marketplace si verification_status ===
     * VERIFICADO Y status === ACTIVO (ver canOperate()).
     */
    public const VERIFICATION_PENDING = 'PENDIENTE';

    public const VERIFICATION_IN_REVIEW = 'EN_REVISION';

    public const VERIFICATION_VERIFIED = 'VERIFICADO';

    public const VERIFICATION_REJECTED = 'RECHAZADO';

    protected $fillable = [
        'user_id',
        'pickup_ally_id',
        'business_name',
        'document_id',
        'cedula',
        'city',
        'state',
        'logo_path',
        'cover_photo_path',
        'descripcion',
        'address',
        'cedula_front_photo_path',
        'cedula_back_photo_path',
        'rif_document_path',
        'product_or_workspace_photo_path',
        'status',
        'verification_status',
        'verification_rejection_reason',
        'verification_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pickupAlly(): BelongsTo
    {
        return $this->belongsTo(Ally::class, 'pickup_ally_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * True si el emprendedor puede publicar/vender en el Marketplace:
     * verificado documentalmente Y con la cuenta operativa activa.
     * Única fuente de verdad que debe consultar el backend.
     */
    public function canOperate(): bool
    {
        return $this->verification_status === self::VERIFICATION_VERIFIED
            && $this->status === self::STATUS_ACTIVE;
    }
}
