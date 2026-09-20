<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sugerencia/recomendación enviada desde el formulario público
 * (sin cuenta) o desde el panel de un usuario autenticado. user_id es
 * nullable a propósito: una recomendación del formulario público
 * nunca tiene usuario asociado.
 */
class Recommendation extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'nueva';
    public const STATUS_READ = 'leida';
    public const STATUS_ARCHIVED = 'archivada';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'message',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
