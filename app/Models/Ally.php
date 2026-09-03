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

    protected $fillable = [
        'user_id',
        'business_name',
        'rif',
        'city',
        'state',
        'address',
        'latitude',
        'longitude',
        'commission_percentage',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'commission_percentage' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
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
