<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    public const STATUS_PENDIENTE = 'PENDIENTE';

    public const STATUS_CONFIRMADO = 'CONFIRMADO';

    public const STATUS_CANCELADO = 'CANCELADO';

    protected $fillable = [
        'producto_id',
        'emprendedor_id',
        'package_id',
        'cantidad',
        'precio_unitario_usd',
        'precio_total_usd',
        'cliente_nombre',
        'cliente_id_doc',
        'cliente_telefono',
        'destino_ciudad',
        'destino_estado',
        'status',
        'chat_token',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario_usd' => 'decimal:2',
            'precio_total_usd' => 'decimal:2',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajePedido::class)->oldest();
    }
}
