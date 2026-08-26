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
        'base_price_usd',
        'price_per_kg_usd',
        'price_per_km_usd',
        'envelope_price_usd',
        'fragile_surcharge_usd',
        'insurance_percentage',
        'delivery_price_usd',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price_usd' => 'decimal:2',
            'price_per_kg_usd' => 'decimal:2',
            'price_per_km_usd' => 'decimal:2',
            'envelope_price_usd' => 'decimal:2',
            'fragile_surcharge_usd' => 'decimal:2',
            'insurance_percentage' => 'decimal:2',
            'delivery_price_usd' => 'decimal:2',
        ];
    }

    /**
     * La tarifa vigente. Actualmente es global (una sola fila
     * para toda la plataforma): si en el futuro se necesitan
     * varias tarifas (por ejemplo por package_type), este es
     * el único método que habría que cambiar.
     */
    public static function current(): ?self
    {
        return static::query()->latest('updated_at')->first();
    }
}
