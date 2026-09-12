<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Nominatim (OpenStreetMap) es gratuito y no requiere API key,
     * pero SÍ exige un User-Agent identificable y respetar ~1
     * petición por segundo (política de uso justo). Como geocodificamos
     * cada dirección UNA sola vez y guardamos el resultado en
     * packages.delivery_latitude/longitude, el volumen real de
     * peticiones es bajo (una por guía, no una por consulta).
     *
     * Si el volumen crece mucho en el futuro, la opción es
     * autohospedar Nominatim o pasar a un proveedor de pago — pero
     * para el tamaño actual de Venexpress esto es más que suficiente.
     */
    protected const BASE_URL = 'https://nominatim.openstreetmap.org/search';

    public function geocode(string $address): ?array
    {
        $address = trim($address);

        if ($address === '') {
            return null;
        }

        try {
            $response = Http::withHeaders([
                // Nominatim exige identificar la app que consume el servicio.
                'User-Agent' => 'Venexpress/1.0 (contacto@venexpress.com)',
            ])->get(self::BASE_URL, [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'countrycodes' => 've',
            ]);

            if (! $response->successful()) {
                Log::warning('Geocoding: respuesta no exitosa de Nominatim.', [
                    'address' => $address,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $results = $response->json();

            if (empty($results)) {
                return null;
            }

            return [
                'latitude' => (float) $results[0]['lat'],
                'longitude' => (float) $results[0]['lon'],
            ];
        } catch (\Throwable $e) {
            Log::warning('Geocoding: error al consultar Nominatim.', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Distancia en línea recta entre dos coordenadas, en kilómetros
     * (fórmula de Haversine). Suficiente para ORDENAR pedidos por
     * cercanía — no pretende ser la distancia real por calles.
     */
    public static function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }
}
