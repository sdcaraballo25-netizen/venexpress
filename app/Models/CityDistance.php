<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityDistance extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_a',
        'state_a',
        'city_b',
        'state_b',
        'distance_km',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'integer',
        ];
    }

    /**
     * Busca la distancia entre dos ubicaciones.
     *
     * La distancia es simétrica:
     * Caracas, Miranda -> Valencia, Carabobo
     * es la misma que:
     * Valencia, Carabobo -> Caracas, Miranda
     */
    public static function between(
        string $cityOne,
        ?string $stateOne,
        string $cityTwo,
        ?string $stateTwo
    ): ?self {
        [$aCity, $aState, $bCity, $bState] = self::normalizePair(
            $cityOne,
            $stateOne,
            $cityTwo,
            $stateTwo
        );

        return static::query()
            ->where('city_a', $aCity)
            ->where('state_a', $aState)
            ->where('city_b', $bCity)
            ->where('state_b', $bState)
            ->first();
    }

    /**
     * Guarda o actualiza una distancia.
     */
    public static function setDistance(
        string $cityOne,
        ?string $stateOne,
        string $cityTwo,
        ?string $stateTwo,
        int $distanceKm
    ): self {
        if ($distanceKm < 0) {
            throw new \InvalidArgumentException(
                'La distancia no puede ser negativa.'
            );
        }

        [$aCity, $aState, $bCity, $bState] = self::normalizePair(
            $cityOne,
            $stateOne,
            $cityTwo,
            $stateTwo
        );

        $existing = static::query()
            ->where('city_a', $aCity)
            ->where('state_a', $aState)
            ->where('city_b', $bCity)
            ->where('state_b', $bState)
            ->first();

        if ($existing) {
            $existing->update([
                'distance_km' => $distanceKm,
            ]);

            return $existing->fresh();
        }

        return static::create([
            'city_a' => $aCity,
            'state_a' => $aState,
            'city_b' => $bCity,
            'state_b' => $bState,
            'distance_km' => $distanceKm,
        ]);
    }

    /**
     * Compatibilidad con código antiguo que solo tenía ciudades.
     */
    public static function betweenCities(
        string $cityOne,
        string $cityTwo
    ): ?self {
        [$a, $b] = self::normalizeCityPair($cityOne, $cityTwo);

        return static::query()
            ->where('city_a', $a)
            ->where('city_b', $b)
            ->first();
    }

    /**
     * Normaliza una ubicación individual.
     */
    protected static function normalizeLocation(
        string $city,
        ?string $state
    ): array {
        $city = trim($city);
        $state = $state !== null ? trim($state) : null;

        return [
            'city' => mb_strtolower($city),
            'state' => $state !== ''
                ? mb_strtolower($state)
                : null,
        ];
    }

    /**
     * Normaliza el par de ubicaciones para que el orden no importe.
     *
     * @return array{
     *     0:string,
     *     1:?string,
     *     2:string,
     *     3:?string
     * }
     */
    protected static function normalizePair(
        string $cityOne,
        ?string $stateOne,
        string $cityTwo,
        ?string $stateTwo
    ): array {
        $one = self::normalizeLocation($cityOne, $stateOne);
        $two = self::normalizeLocation($cityTwo, $stateTwo);

        $oneKey = $one['city'] . '|' . ($one['state'] ?? '');
        $twoKey = $two['city'] . '|' . ($two['state'] ?? '');

        if ($oneKey <= $twoKey) {
            return [
                $one['city'],
                $one['state'],
                $two['city'],
                $two['state'],
            ];
        }

        return [
            $two['city'],
            $two['state'],
            $one['city'],
            $one['state'],
        ];
    }

    /**
     * Normalización antigua por ciudad.
     */
    protected static function normalizeCityPair(
        string $cityOne,
        string $cityTwo
    ): array {
        $pair = [
            mb_strtolower(trim($cityOne)),
            mb_strtolower(trim($cityTwo)),
        ];

        sort($pair);

        return $pair;
    }
}
