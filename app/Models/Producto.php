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
        'foto_path',
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

    /**
     * Precio en bolívares al tipo de cambio BCV vigente, para mostrar
     * junto al precio en USD en la tienda pública (mismo criterio que
     * el resto de la plataforma: todo se cotiza en USD y se convierte
     * a Bs. con la tasa BCV actual). Null si todavía no hay ninguna
     * tasa cargada.
     */
    public function getPrecioVesAttribute(): ?float
    {
        $rate = BcvRate::current()?->rate;

        return $rate ? (float) $this->precio_usd * (float) $rate : null;
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function resenas(): HasMany
    {
        return $this->hasMany(Resena::class);
    }

    /**
     * Promedio de estrellas (1-5) entre todas las reseñas del
     * producto, redondeado a 1 decimal. Null si todavía no tiene
     * ninguna, para poder distinguir "sin reseñas" de "0 estrellas" en
     * la vista.
     */
    public function getPromedioEstrellasAttribute(): ?float
    {
        $promedio = $this->resenas()->avg('estrellas');

        return $promedio !== null ? round((float) $promedio, 1) : null;
    }

    public function getTotalResenasAttribute(): int
    {
        return $this->resenas()->count();
    }
}
