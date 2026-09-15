<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Sugerencia/recomendación enviada por un visitante desde el
 * formulario público. Sin usuario asociado: cualquiera puede enviar
 * una, incluso sin cuenta.
 */
class Recommendation extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'nueva';
    public const STATUS_READ = 'leida';
    public const STATUS_ARCHIVED = 'archivada';

    protected $fillable = [
        'name',
        'email',
        'message',
        'status',
    ];
}
