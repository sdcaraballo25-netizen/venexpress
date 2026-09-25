<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una línea del carrito dentro de un Pedido. Ver
 * database/migrations/2026_09_25_000004_create_pedido_items_table.php
 * para por qué existe (antes, un Pedido era un solo producto).
 */
class PedidoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'cantidad',
        'precio_unitario_usd',
        'subtotal_usd',
    ];

    protected function casts(): array
    {
        return [
            'precio_unitario_usd' => 'decimal:2',
            'subtotal_usd' => 'decimal:2',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
