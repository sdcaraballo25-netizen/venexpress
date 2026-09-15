<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cobertura de un Warehouse (HUB propio) sobre una zona geográfica
 * del catálogo unificado (Fase 1). Usada exclusivamente por
 * LogisticsResolutionService para resolver a qué HUB pertenece un
 * destino — no representa ninguna acción logística por sí misma.
 */
class WarehouseCoverage extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'state',
        'city',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Cobertura de todo el estado (fallback), en vez de una ciudad
     * específica dentro de él.
     */
    public function isStateWide(): bool
    {
        return $this->city === null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
