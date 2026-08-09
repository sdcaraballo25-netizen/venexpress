<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateMatrix extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'origin_city',
        'destination_city',
        'price_per_kg_usd',
        'base_price_usd',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_kg_usd' => 'decimal:2',
            'base_price_usd' => 'decimal:2',
        ];
    }

    /**
     * Busca la tarifa configurada para una ruta específica.
     */
    public static function forRoute(string $originCity, string $destinationCity): ?self
    {
        return static::query()
            ->where('origin_city', $originCity)
            ->where('destination_city', $destinationCity)
            ->first();
    }
}
