<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DistanceApiService
{
    private const GEOCODER_URL = 'https://nominatim.openstreetmap.org/search';

    private const ROUTER_URL = 'https://router.project-osrm.org/route/v1/driving';

    private const CACHE_DAYS = 30;

    /**
     * Calcula la distancia real por carretera entre dos ubicaciones.
     *
     * Utiliza:
     * - Nominatim para geocodificar.
     * - OSRM para calcular la ruta.
     */
    public function drivingDistanceKm(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState,
    ): int {
        $originCity = trim($originCity);
        $destinationCity = trim($destinationCity);

        $originState = $this->normalizeState($originState);
        $destinationState = $this->normalizeState($destinationState);

        if ($originCity === '') {
            throw new RuntimeException(
                'La ciudad de origen es obligatoria.'
            );
        }

        if ($destinationCity === '') {
            throw new RuntimeException(
                'La ciudad de destino es obligatoria.'
            );
        }

        /*
         * Solo consideramos distancia 0 cuando ciudad Y estado
         * representan la misma ubicación.
         */
        if (
            $this->sameLocation(
                $originCity,
                $originState,
                $destinationCity,
                $destinationState
            )
        ) {
            return 0;
        }

        $cacheKey = $this->distanceCacheKey(
            $originCity,
            $originState,
            $destinationCity,
            $destinationState
        );

        return (int) Cache::remember(
            $cacheKey,
            now()->addDays(self::CACHE_DAYS),
            function () use (
                $originCity,
                $originState,
                $destinationCity,
                $destinationState
            ) {
                $origin = $this->geocode(
                    $originCity,
                    $originState
                );

                $destination = $this->geocode(
                    $destinationCity,
                    $destinationState
                );

                return $this->calculateRouteDistance(
                    $origin,
                    $destination
                );
            }
        );
    }

    /**
     * Geocodifica una ciudad venezolana.
     *
     * Primero intenta:
     * Ciudad + Estado + Venezuela
     *
     * y luego:
     * Ciudad + Venezuela
     *
     * @return array{
     *     latitude:float,
     *     longitude:float
     * }
     */
    protected function geocode(
        string $city,
        ?string $state
    ): array {
        $city = trim($city);
        $state = $this->normalizeState($state);

        $cacheKey = 'venexpress.geocode.' . md5(
            mb_strtolower(
                $city . '|' . ($state ?? '')
            )
        );

        return Cache::remember(
            $cacheKey,
            now()->addDays(self::CACHE_DAYS),
            function () use ($city, $state) {
                $queries = [];

                if ($state) {
                    $queries[] = "{$city}, {$state}, Venezuela";
                }

                $queries[] = "{$city}, Venezuela";

                foreach (array_unique($queries) as $query) {
                    try {
                        $response = Http::withHeaders([
                            'User-Agent' =>
                                'Venexpress/1.0 (shipping application)',
                            'Accept-Language' => 'es',
                        ])
                            ->timeout(10)
                            ->retry(2, 300)
                            ->get(
                                self::GEOCODER_URL,
                                [
                                    'q' => $query,
                                    'format' => 'jsonv2',
                                    'limit' => 1,
                                    'countrycodes' => 've',
                                    'addressdetails' => 1,
                                ]
                            );

                        if (! $response->successful()) {
                            continue;
                        }

                        $result = $response->json('0');

                        if (
                            is_array($result)
                            && isset(
                                $result['lat'],
                                $result['lon']
                            )
                        ) {
                            return [
                                'latitude' =>
                                    (float) $result['lat'],
                                'longitude' =>
                                    (float) $result['lon'],
                            ];
                        }
                    } catch (\Throwable) {
                        continue;
                    }
                }

                throw new RuntimeException(
                    "No se pudo localizar la ciudad {$city}"
                    . ($state ? " en {$state}" : '')
                    . ' en la API de mapas.'
                );
            }
        );
    }

    /**
     * Calcula la ruta mediante OSRM.
     *
     * @param array{latitude:float,longitude:float} $origin
     * @param array{latitude:float,longitude:float} $destination
     */
    protected function calculateRouteDistance(
        array $origin,
        array $destination
    ): int {
        $url = self::ROUTER_URL . '/'
            . $origin['longitude']
            . ','
            . $origin['latitude']
            . ';'
            . $destination['longitude']
            . ','
            . $destination['latitude'];

        try {
            $response = Http::timeout(15)
                ->retry(2, 300)
                ->get(
                    $url,
                    [
                        'overview' => 'false',
                        'alternatives' => 'false',
                        'steps' => 'false',
                    ]
                );
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'No fue posible comunicarse con la API de rutas.',
                previous: $e
            );
        }

        if (
            ! $response->successful()
            || $response->json('code') !== 'Ok'
        ) {
            throw new RuntimeException(
                'La API de rutas no pudo calcular la distancia '
                . 'entre las ubicaciones seleccionadas.'
            );
        }

        $meters = (float) $response->json(
            'routes.0.distance',
            0
        );

        if ($meters <= 0) {
            throw new RuntimeException(
                'La API de rutas no devolvió una distancia válida.'
            );
        }

        return max(
            1,
            (int) round($meters / 1000)
        );
    }

    /**
     * Genera una clave estable para la distancia.
     *
     * Incluye estados para evitar colisiones entre ciudades
     * con nombres iguales.
     */
    protected function distanceCacheKey(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState
    ): string {
        $origin = mb_strtolower(
            trim($originCity)
            . '|'
            . ($originState ?? '')
        );

        $destination = mb_strtolower(
            trim($destinationCity)
            . '|'
            . ($destinationState ?? '')
        );

        $locations = [
            $origin,
            $destination,
        ];

        sort($locations);

        return 'venexpress.distance.'
            . md5(implode('||', $locations));
    }

    /**
     * Determina si dos ubicaciones son realmente iguales.
     */
    protected function sameLocation(
        string $cityOne,
        ?string $stateOne,
        string $cityTwo,
        ?string $stateTwo
    ): bool {
        $sameCity = mb_strtolower(trim($cityOne))
            === mb_strtolower(trim($cityTwo));

        if (! $sameCity) {
            return false;
        }

        /*
         * Si ambos estados están disponibles, deben coincidir.
         */
        if ($stateOne !== null && $stateTwo !== null) {
            return mb_strtolower($stateOne)
                === mb_strtolower($stateTwo);
        }

        /*
         * Si no tenemos estados, mantenemos compatibilidad
         * con registros antiguos.
         */
        return $stateOne === null && $stateTwo === null;
    }

    protected function normalizeState(
        ?string $state
    ): ?string {
        if ($state === null) {
            return null;
        }

        $state = trim($state);

        return $state === ''
            ? null
            : $state;
    }
}
