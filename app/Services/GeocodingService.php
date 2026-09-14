<?php

namespace App\Services;

use App\Models\Package;
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
     * Geocodifica la dirección de entrega de un paquete y guarda el
     * resultado en delivery_latitude/longitude. Reutilizada tanto por
     * el job en cola (GeocodePackageDeliveryAddress, para reintentar
     * en segundo plano si Nominatim falla) como por
     * DriverDeliveryController::routeOrder(), que la llama de forma
     * síncrona para no depender de que un worker de colas esté
     * corriendo — así "Mi Ruta de Entrega" funciona de una vez, sin
     * esperar.
     *
     * Intenta primero la dirección completa (calle/urbanización) y,
     * si Nominatim no la encuentra —muy común en zonas residenciales
     * pequeñas, que OpenStreetMap no siempre mapea a nivel de calle—,
     * cae primero al sector/calle (delivery_sector suele ser un
     * nombre de calle o avenida más "estándar", mientras que
     * delivery_address suele incluir el nombre de un edificio/torre/
     * apartamento que Nominatim no reconoce) y, si tampoco eso
     * aparece, a nivel de ciudad. Una coordenada aproximada sigue
     * siendo suficiente para lo único que este dato se usa: ORDENAR
     * la ruta de más lejos a más cerca, no ubicar la casa exacta en
     * un mapa — pero mientras más precisa, mejor refleja distancias
     * reales entre pedidos de una misma ciudad.
     *
     * Devuelve true si el paquete quedó geocodificado (ya sea ahora
     * o de antes, exacto o aproximado); false si ni siquiera la
     * ciudad se pudo ubicar o la consulta falló.
     */
    public function geocodePackageDeliveryAddress(Package $package): bool
    {
        if ($package->delivery_latitude !== null && $package->delivery_longitude !== null) {
            return true;
        }

        $fullAddress = trim(implode(', ', array_filter([
            $package->delivery_address,
            $package->delivery_sector,
            $package->destination_city,
            $package->destination_state,
            'Venezuela',
        ])));

        $sectorLevelAddress = trim(implode(', ', array_filter([
            $package->delivery_sector,
            $package->destination_city,
            $package->destination_state,
            'Venezuela',
        ])));

        $cityLevelAddress = trim(implode(', ', array_filter([
            $package->destination_city,
            $package->destination_state,
            'Venezuela',
        ])));

        $candidates = array_unique(array_filter([$fullAddress, $sectorLevelAddress, $cityLevelAddress]));

        foreach ($candidates as $index => $address) {
            if ($index > 0) {
                // Respetamos la política de uso justo de Nominatim
                // (~1 petición/segundo) entre el intento exacto y el
                // de respaldo por ciudad.
                sleep(1);
            }

            $coords = $this->geocode($address);

            if ($coords) {
                $package->forceFill([
                    'delivery_latitude' => $coords['latitude'],
                    'delivery_longitude' => $coords['longitude'],
                    'delivery_geocoded_at' => now(),
                ])->save();

                return true;
            }
        }

        return false;
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
