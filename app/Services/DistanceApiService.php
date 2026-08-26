<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DistanceApiService
{
    private const GEOCODER_URL = 'https://nominatim.openstreetmap.org/search';
    private const ROUTER_URL = 'https://router.project-osrm.org/route/v1/driving';

    /**
     * Calcula distancia real por carretera entre dos ciudades venezolanas.
     *
     * Geocodificación: OpenStreetMap Nominatim.
     * Ruta: OSRM (OpenStreetMap).
     *
     * Las coordenadas y distancias se almacenan temporalmente en caché
     * para no consultar las APIs repetidamente mientras se llena una guía.
     */
    public function drivingDistanceKm(
        string $originCity,
        ?string $originState,
        string $destinationCity,
        ?string $destinationState,
    ): int {
        if (mb_strtolower(trim($originCity)) === mb_strtolower(trim($destinationCity))) {
            return 0;
        }

        $origin = $this->geocode($originCity, $originState);
        $destination = $this->geocode($destinationCity, $destinationState);

        $cacheKey = 'venexpress.distance.' . md5(
            mb_strtolower(trim($originCity . '|' . $originState . '|' . $destinationCity . '|' . $destinationState))
        );

        return (int) Cache::remember($cacheKey, now()->addDays(30), function () use ($origin, $destination) {
            $response = Http::timeout(15)
                ->retry(2, 300)
                ->get(self::ROUTER_URL . '/' .
                    $origin['longitude'] . ',' . $origin['latitude'] . ';' .
                    $destination['longitude'] . ',' . $destination['latitude'],
                    [
                        'overview' => 'false',
                        'alternatives' => 'false',
                        'steps' => 'false',
                    ]
                );

            if (! $response->successful() || $response->json('code') !== 'Ok') {
                throw new RuntimeException('La API de rutas no pudo calcular la distancia entre las ciudades seleccionadas.');
            }

            $meters = (float) $response->json('routes.0.distance', 0);

            if ($meters <= 0) {
                throw new RuntimeException('La API de rutas no devolvió una distancia válida.');
            }

            return (int) round($meters / 1000);
        });
    }

    /**
     * Obtiene coordenadas de una ciudad. Se intenta primero con ciudad +
     * estado + Venezuela y luego solo con ciudad + Venezuela.
     *
     * @return array{latitude: float, longitude: float}
     */
    protected function geocode(string $city, ?string $state): array
    {
        $cacheKey = 'venexpress.geocode.' . md5(
            mb_strtolower(trim($city . '|' . $state))
        );

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($city, $state) {
            $queries = array_filter([
                trim($city . ', ' . $state . ', Venezuela'),
                trim($city . ', Venezuela'),
            ]);

            foreach ($queries as $query) {
                $response = Http::withHeaders([
                    'User-Agent' => 'Venexpress/1.0 (shipping application)',
                    'Accept-Language' => 'es',
                ])->timeout(15)
                    ->retry(2, 300)
                    ->get(self::GEOCODER_URL, [
                        'q' => $query,
                        'format' => 'jsonv2',
                        'limit' => 1,
                        'countrycodes' => 've',
                    ]);

                if (! $response->successful()) {
                    continue;
                }

                $result = $response->json('0');

                if (is_array($result) && isset($result['lat'], $result['lon'])) {
                    return [
                        'latitude' => (float) $result['lat'],
                        'longitude' => (float) $result['lon'],
                    ];
                }
            }

            throw new RuntimeException(
                "No se pudo localizar la ciudad {$city} en la API de mapas."
            );
        });
    }
}
