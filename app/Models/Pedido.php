<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pedido extends Model
{
    use HasFactory;

    public const STATUS_PENDIENTE = 'PENDIENTE';

    /**
     * El emprendedor confirmó que el cliente ya le pagó (fuera de la
     * plataforma), pero todavía no hay guía: falta que lleve el
     * paquete a su agencia aliada y que taquilla verifique el peso
     * real, tamaño, fragilidad y seguro (ver Ally\EmprendedorPedidos).
     * Antes "confirmar" generaba la guía de una vez usando el peso
     * autodeclarado del catálogo, sin frágil/seguro y sin verificación
     * física alguna.
     */
    public const STATUS_PAGADO = 'PAGADO';

    public const STATUS_CONFIRMADO = 'CONFIRMADO';

    public const STATUS_CANCELADO = 'CANCELADO';

    protected $fillable = [
        'producto_id',
        'emprendedor_id',
        'user_id',
        'package_id',
        'cantidad',
        'precio_unitario_usd',
        'precio_total_usd',
        'cliente_nombre',
        'cliente_id_doc',
        'cliente_telefono',
        'destino_ciudad',
        'destino_estado',
        'direccion_entrega',
        'referencia_entrega',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajePedido::class)->oldest();
    }

    public function resena(): HasOne
    {
        return $this->hasOne(Resena::class);
    }

    /**
     * Mismo criterio que Producto::precio_ves: todo se cotiza en USD y
     * se muestra convertido a Bs. con la tasa BCV vigente (no se
     * guarda un snapshot — se recalcula con la tasa actual cada vez
     * que se muestra, igual que en el resto de la plataforma).
     */
    public function getPrecioTotalVesAttribute(): ?float
    {
        $rate = BcvRate::current()?->rate;

        return $rate ? (float) $this->precio_total_usd * (float) $rate : null;
    }
}
