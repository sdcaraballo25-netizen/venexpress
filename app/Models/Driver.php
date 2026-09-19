<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory, HasPublicId;

    /**
     * Tipos de conductor.
     */
    public const TYPE_HUB = 'hub';

    public const TYPE_DELIVERY = 'delivery';

    /**
     * Valor fijo de vehicle_type para drivers HUB: no manejan un
     * vehículo particular (moto/carro), sino un camión de la flota
     * de Venexpress.
     */
    public const HUB_VEHICLE_TYPE = 'Camión Venexpress';

    /**
     * Estados del conductor.
     */
    public const STATUS_PENDING = 'PENDIENTE';

    public const STATUS_ACTIVE = 'ACTIVO';

    public const STATUS_REJECTED = 'RECHAZADO';

    public const STATUS_SUSPENDED = 'SUSPENDIDO';

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'vehicle_plate',
        'vehicle_type',
        'phone',
        'cedula',
        'bank_account_number',
        'bank_account_holder_name',
        'bank_account_holder_id',
        'status',
        'driver_type',
        'license_photo_path',
        'id_photo_path',
        'vehicle_registration_photo_path',
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
     * Pagos asociados a este chofer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DriverPayment::class);
    }

    /**
     * Paquetes actualmente asignados a este chofer.
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Rutas asignadas a este chofer.
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }
}
