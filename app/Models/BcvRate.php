<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcvRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate',
        'effective_date',
        'effective_at',
        'source',
        'api_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'effective_date' => 'date',
            'effective_at' => 'datetime',
        ];
    }

    /**
     * Obtiene la última tasa registrada, incluyendo las dos tasas
     * que el BCV puede publicar durante un mismo día.
     */
    public static function current(): ?self
    {
        return static::query()
            ->orderByDesc('effective_at')
            ->orderByDesc('effective_date')
            ->orderByDesc('id')
            ->first();
    }
}
