<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageHistory extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'package_id',
        'status',
        'location_description',
        'scanned_by_user_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Paquete al que pertenece este evento del historial.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Usuario que registró/escaneó este evento (puede ser null).
     */
    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }
}
