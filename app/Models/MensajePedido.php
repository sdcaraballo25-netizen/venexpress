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

    /**
     * URL autenticada del adjunto. Los adjuntos (comprobantes de pago,
     * guías) ya no viven en el disco "public" — se sirven a través de
     * DocumentPhotoController::pedidoAttachment(), que exige el
     * chat_token del pedido, igual que la página del chat.
     */
    public function archivoUrl(string $chatToken): ?string
    {
        if (! $this->archivo_path) {
            return null;
        }

        return route('public.marketplace.pedido.attachment', [
            'token' => $chatToken,
            'mensaje' => $this->id,
        ]);
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
