<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityDistance extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'city_a',
        'city_b',
        'distance_km',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'distance_km' => 'integer',
        ];
    }

    /**
     * Busca la distancia registrada entre dos ciudades.
     *
     * Las distancias son simétricas, así que no importa el orden
     * en el que se pasen origen/destino: siempre se normalizan
     * alfabéticamente antes de consultar.
     */
    public static function between(string $cityOne, string $cityTwo): ?self
    {
        [$a, $b] = self::normalizePair($cityOne, $cityTwo);

        return static::query()
            ->where('city_a', $a)
            ->where('city_b', $b)
            ->first();
    }

    /**
     * Crea o actualiza la distancia entre dos ciudades, normalizando
     * el orden alfabético para no duplicar la ruta inversa.
     */
    public static function setDistance(string $cityOne, string $cityTwo, int $distanceKm): self
    {
        [$a, $b] = self::normalizePair($cityOne, $cityTwo);

        return static::updateOrCreate(
            ['city_a' => $a, 'city_b' => $b],
            ['distance_km' => $distanceKm],
        );
    }

    /**
     * Ordena alfabéticamente un par de ciudades para que
     * "Caracas, Valencia" y "Valencia, Caracas" sean la misma fila.
     *
     * @return array{0: string, 1: string}
     */
    protected static function normalizePair(string $cityOne, string $cityTwo): array
    {
        $pair = [$cityOne, $cityTwo];
        sort($pair);

        return $pair;
    }
}
