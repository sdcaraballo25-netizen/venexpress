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

    protected $fillable = [
        'user_id',
        'pickup_ally_id',
        'business_name',
        'document_id',
        'status',
    ];

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
}
