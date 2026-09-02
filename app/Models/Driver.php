<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */

    public const STATUS_PENDING = 'PENDIENTE';
    public const STATUS_ACTIVE = 'ACTIVO';
    public const STATUS_REJECTED = 'RECHAZADO';
    public const STATUS_SUSPENDED = 'SUSPENDIDO';

    protected $fillable = [
    'user_id',
    'vehicle_plate',
    'vehicle_type',
    'phone',
    'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Usuario asociado a este chofer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paquetes actualmente asignados a este chofer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
