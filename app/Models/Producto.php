<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    /**
     * Categorías fijas del catálogo (mismo criterio simple que los
     * STATUS_* de otros modelos: no amerita una tabla aparte en esta
     * etapa del MVP). null/"" se trata como "sin categoría".
     */
    public const CATEGORIAS = [
        'ropa' => 'Ropa y accesorios',
        'hogar' => 'Hogar y decoración',
        'comida' => 'Alimentos y bebidas',
        'belleza' => 'Belleza y cuidado personal',
        'tecnologia' => 'Tecnología',
        'otros' => 'Otros',
    ];

    protected $fillable = [
        'emprendedor_id',
        'nombre',
        'descripcion',
        'categoria',
        'foto_path',
        'fotos',
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
            'fotos' => 'array',
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

    /**
     * Galería completa para mostrar en la tienda: la portada (foto_path)
     * primero, seguida de las fotos adicionales. Se calcula aquí para no
     * repetir el orden en cada vista que necesite mostrar imágenes.
     */
    public function getGaleriaAttribute(): array
    {
        return collect([$this->foto_path])
            ->merge($this->fotos ?? [])
            ->filter()
            ->values()
            ->all();
    }
}
