<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ally extends Model
{
    use HasFactory;

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
    'business_name',
    'rif',
    'city',
    'address',
    'commission_percentage',
    'status',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    /**
     * Usuario dueño de esta taquilla aliada (Aliado Administrador).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Paquetes registrados por esta taquilla aliada.
     */
    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Usuarios de Taquilla de esta agencia (RF-ALI-02).
     * Son Users normales con role 'aliado_taquilla' y ally_id = este id.
     */
    public function staffUsers(): HasMany
    {
        return $this->hasMany(User::class, 'ally_id')
            ->where('role', User::ROLE_ALIADO_TAQUILLA);
    }

    /**
     * Incidencias/reclamos de esta agencia (RF-ALI-06).
     */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }
}
