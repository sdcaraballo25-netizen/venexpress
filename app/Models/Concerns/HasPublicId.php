<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Da a un modelo un identificador público (UUID) además de su id
 * autoincremental, y lo usa como route key: las URLs que referencian
 * este modelo ("/aliados/{ally}/...", "/repartidores/{driver}/...")
 * ya no exponen un número secuencial adivinable/enumerable.
 *
 * El id numérico sigue existiendo tal cual para todo lo demás
 * (relaciones, claves foráneas, Model::find()) — esto solo cambia
 * qué columna usa el route-model binding de Laravel.
 */
trait HasPublicId
{
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
