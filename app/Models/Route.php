<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | TIPOS DE RUTA
    |--------------------------------------------------------------------------
    */

    public const TYPE_HUB_TRANSFER = 'hub_transfer';
    public const TYPE_DELIVERY = 'delivery';

    public const TYPES = [
        self::TYPE_HUB_TRANSFER,
        self::TYPE_DELIVERY,
    ];

    /*
    |--------------------------------------------------------------------------
    | ESTADOS
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ASSIGNED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'city',
        'state',
        'name',
        'driver_id',
        'created_by',
        'status',
        'started_at',
        'completed_at',
        'route_type',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Administrador que creó la ruta.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Paradas de la ruta, ordenadas por secuencia de visita.
     */
    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isHubTransfer(): bool
    {
        return $this->route_type === self::TYPE_HUB_TRANSFER;
    }

    public function isDelivery(): bool
    {
        return $this->route_type === self::TYPE_DELIVERY;
    }

    public function isEditable(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_ASSIGNED,
            ],
            true
        );
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function nextPendingStop(): ?RouteStop
    {
        return $this->stops()
            ->where(
                'status',
                RouteStop::STATUS_PENDING
            )
            ->orderBy('sequence')
            ->first();
    }

    public function pendingStopsCount(): int
    {
        return $this->stops()
            ->where(
                'status',
                RouteStop::STATUS_PENDING
            )
            ->count();
    }

    public function visitedStopsCount(): int
    {
        return $this->stops()
            ->where(
                'status',
                RouteStop::STATUS_VISITED
            )
            ->count();
    }
}

