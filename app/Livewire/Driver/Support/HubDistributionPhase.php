<?php

namespace App\Livewire\Driver\Support;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;

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
     * Paquetes en HUB con destino a alguno de los almacenes (paradas)
     * de esta ruta de distribución. Conteo informativo para la
     * interfaz: compara por ciudad/estado de texto, la misma
     * limitación conocida que ya documenta
     * LogisticsScanService::resolveDestinationStop(); el escaneo real
     * sigue validando cada paquete ahí, esto no participa en esa
     * decisión.
     */
    public static function pendingDepartureCount(Route $route): int
    {
        $destinations = $route->stops
            ->map(function (RouteStop $stop) {
                if (! $stop->warehouse) {
                    return null;
                }

                return [
                    'city' => mb_strtolower(trim((string) $stop->warehouse->city)),
                    'state' => mb_strtolower(trim((string) $stop->warehouse->state)),
                ];
            })
            ->filter()
            ->unique(fn (array $destination) => $destination['city'].'|'.$destination['state'])
            ->values();

        if ($destinations->isEmpty()) {
            return 0;
        }

        return Package::query()
            ->where('current_status', Package::STATUS_EN_HUB)
            ->where(function ($query) use ($destinations) {
                foreach ($destinations as $destination) {
                    $query->orWhere(function ($q) use ($destination) {
                        $q->whereRaw('LOWER(TRIM(destination_city)) = ?', [$destination['city']])
                            ->whereRaw('LOWER(TRIM(destination_state)) = ?', [$destination['state']]);
                    });
                }
            })
            ->count();
    }
}
