<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'emprendedor_id',
        'nombre',
        'descripcion',
        'precio_usd',
        'peso_kg',
        'stock',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_usd' => 'decimal:2',
            'peso_kg' => 'decimal:3',
            'activo' => 'boolean',
        ];
    }

    public function emprendedor(): BelongsTo
    {
        return $this->belongsTo(Emprendedor::class);
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
