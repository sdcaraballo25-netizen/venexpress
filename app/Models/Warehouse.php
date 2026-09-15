<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Almacén propio de Venexpress. Es infraestructura interna, no un
 * socio comercial — a diferencia de Ally, no tiene comisión, RIF ni
 * usuario dueño. Es el destino de las rutas de tipo
 * Route::TYPE_HUB_DISTRIBUTION.
 */
class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city',
        'state',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    /**
     * Zonas geográficas (estado, o estado + ciudad) que este HUB
     * atiende. Usada por LogisticsResolutionService (Fase 4) para
     * resolver el destino logístico de un paquete.
     */
    public function coverages(): HasMany
    {
        return $this->hasMany(WarehouseCoverage::class);
    }
}
