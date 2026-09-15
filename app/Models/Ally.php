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

    /*
    |--------------------------------------------------------------------------
    | VERIFICACIÓN DE DESTINO (punto final de entrega/retiro)
    |--------------------------------------------------------------------------
    |
    | Estructura preparatoria únicamente. El flujo completo de
    | verificación documental (solicitud -> revisión -> aprobado/
    | rechazado) todavía no existe; estas constantes solo describen
    | los valores que destination_verification_status podrá tomar el
    | día que ese flujo se construya. Hasta entonces,
    | is_verified_destination se activa manualmente por el Admin.
    */

    public const DESTINATION_VERIFICATION_PENDING = 'pendiente';
    public const DESTINATION_VERIFICATION_IN_REVIEW = 'en_revision';
    public const DESTINATION_VERIFICATION_APPROVED = 'aprobado';
    public const DESTINATION_VERIFICATION_REJECTED = 'rechazado';

    protected $fillable = [
        'user_id',
        'business_name',
        'rif',
        'city',
        'state',
        'address',
        'storefront_photo_path',
        'latitude',
        'longitude',
        'commission_percentage',
        'status',
        'is_verified_destination',
        'destination_verification_status',
        'destination_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_verified_destination' => 'boolean',
            'destination_verified_at' => 'datetime',
        ];
    }

    /**
     * Aliado habilitado como punto final de entrega/retiro (no
     * redistribuye). Mientras no exista el flujo de verificación
     * documental completo, este flag lo activa el Admin manualmente.
     */
    public function isVerifiedDestination(): bool
    {
        return (bool) $this->is_verified_destination;
    }

    /**
     * Aliados activos habilitados como punto final de entrega/retiro
     * (Regla 9-12). Usado por Ally\PackageCreate para ofrecer el
     * selector de punto de retiro cuando el pedido no requiere
     * delivery.
     */
    public function scopeVerifiedDestinations($query)
    {
        return $query
            ->where('is_verified_destination', true)
            ->where('status', self::STATUS_ACTIVE);
    }

    public function scopePubliclyVisible($query)
    {
        return $query
            ->where(
                'status',
                self::STATUS_ACTIVE
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function packages(): HasMany
    {
        return $this->hasMany(
            Package::class
        );
    }

    public function staffUsers(): HasMany
    {
        return $this->hasMany(
            User::class,
            'ally_id'
        )->where(
            'role',
            User::ROLE_ALIADO_TAQUILLA
        );
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(
            Incident::class
        );
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(
            AllyFinancialTransaction::class
        );
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(
            AllySettlement::class
        );
    }
}
