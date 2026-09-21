<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MensajePedido extends Model
{
    use HasFactory;

    public const AUTOR_CLIENTE = 'cliente';

    public const AUTOR_EMPRENDEDOR = 'emprendedor';

    protected $table = 'mensajes_pedido';

    protected $fillable = [
        'pedido_id',
        'autor',
        'texto',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
}
