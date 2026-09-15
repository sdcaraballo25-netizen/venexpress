<?php

namespace App\Livewire\Driver\Support;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Services\LogisticsResolutionResult;

/**
 * Determina en qué fase de una ruta hub_distribution está un driver de
 * HUB: 'hub_departure' (todavía no ha sacado paquetes del HUB) o
 * 'hub_arrival' (ya tiene paquetes en tránsito bajo su custodia, rumbo
 * al almacén destino).
 *
 * Única fuente de verdad para esta fase — antes vivía duplicada como
 * Scanner::distributionPhase(); ahora Scanner, Dashboard y RouteDetail
 * la comparten para mostrar siempre la misma acción. Es solo una guía
 * de presentación: la decisión real de qué escaneo aplica la sigue
 * tomando LogisticsScanService en cada scan.
 */
class HubDistributionPhase
{
    public const DEPARTURE = 'hub_departure';

    public const ARRIVAL = 'hub_arrival';

    public static function resolve(Driver $driver): string
    {
        return static::hasPendingArrivals($driver)
            ? self::ARRIVAL
            : self::DEPARTURE;
    }

    protected static function hasPendingArrivals(Driver $driver): bool
    {
        return Package::query()
            ->where('driver_id', $driver->id)
            ->where('current_status', Package::STATUS_EN_TRANSITO_NACIONAL)
            ->exists();
    }

    /**
     * Paquetes bajo custodia de este driver, en tránsito nacional,
     * todavía sin registrar su llegada al almacén destino.
     */
    public static function pendingArrivalsCount(Driver $driver): int
    {
        return Package::query()
            ->where('driver_id', $driver->id)
            ->where('current_status', Package::STATUS_EN_TRANSITO_NACIONAL)
            ->count();
    }

    /**
     * Paquetes EN_HUB cuyo HUB destino ya resuelto (Fase 4/5A,
     * destination_warehouse_id) es alguno de los almacenes (paradas)
     * de esta ruta de distribución.
     *
     * Fase 5B-1: deja de comparar texto de ciudad/estado — usa la
     * misma fuente de verdad que la operación real
     * (LogisticsScanService::scanHubDeparture() solo acepta paquetes
     * con destination_resolution_status = resolved y
     * destination_warehouse_id igual a una parada de la ruta), para
     * que este conteo nunca muestre algo distinto de lo que el
     * escaneo realmente va a aceptar.
     */
    public static function pendingDepartureCount(Route $route): int
    {
        $warehouseIds = $route->stops
            ->pluck('warehouse_id')
            ->filter()
            ->unique()
            ->values();

        if ($warehouseIds->isEmpty()) {
            return 0;
        }

        return Package::query()
            ->where('current_status', Package::STATUS_EN_HUB)
            ->where('destination_resolution_status', LogisticsResolutionResult::STATUS_RESOLVED)
            ->whereIn('destination_warehouse_id', $warehouseIds)
            ->count();
    }
}
