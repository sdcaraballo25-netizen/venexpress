<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RouteStop extends Model
{
    use HasFactory;

    /**
     * AZUL — todavía no visitada durante este ciclo.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * GRIS — recolección completada durante este ciclo.
     */
    public const STATUS_VISITED = 'visited';

    /**
     * La ruta se cerró y esta parada nunca se visitó.
     */
    public const STATUS_SKIPPED = 'skipped';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_VISITED,
        self::STATUS_SKIPPED,
    ];

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'route_id',
        'ally_id',
        'sequence',
        'status',
        'visited_at',
        'packages_collected_count',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'sequence' => 'integer',
            'packages_collected_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function ally(): BelongsTo
    {
        return $this->belongsTo(Ally::class);
    }

    /**
     * Eventos del historial de paquetes registrados durante esta parada.
     */
    public function packageHistories(): HasMany
    {
        return $this->hasMany(PackageHistory::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Color a pintar en el mapa: 'blue' (pending) o 'gray' (visited/skipped).
     */
    public function mapColor(): string
    {
        return $this->status === self::STATUS_PENDING ? 'blue' : 'gray';
    }
}
