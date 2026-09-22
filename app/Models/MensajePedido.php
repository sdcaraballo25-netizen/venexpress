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
        'archivo_path',
        'archivo_nombre',
    ];

    protected const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }

    public function esImagen(): bool
    {
        if (! $this->archivo_path) {
            return false;
        }

        $extension = strtolower(pathinfo($this->archivo_path, PATHINFO_EXTENSION));

        return in_array($extension, self::IMAGE_EXTENSIONS, true);
    }
}
