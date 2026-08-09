<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcvRate extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar de forma masiva.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rate',
        'effective_date',
    ];

    /**
     * Obtiene los atributos que deben convertirse.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'effective_date' => 'date',
        ];
    }

    /**
     * Obtiene la tasa BCV vigente más reciente.
     */
    public static function current(): ?self
    {
        return static::query()
            ->orderByDesc('effective_date')
            ->first();
    }
}
