<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use RuntimeException;

class VenezuelaLocationService
{
    protected string $path;

    public function __construct()
    {
        $this->path = database_path('data/venezuela.json');
    }

    protected function data(): array
    {
        if (!File::exists($this->path)) {
            throw new RuntimeException(
                'No existe database/data/venezuela.json'
            );
        }

        $json = File::get($this->path);
        $data = json_decode($json, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                'venezuela.json no tiene un formato válido.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Algunos archivos JSON vienen envueltos en "estados"
        |--------------------------------------------------------------------------
        */

        if (isset($data['estados']) && is_array($data['estados'])) {
            return $data['estados'];
        }

        return $data;
    }

    /**
     * Devuelve todos los estados.
     */
    public function states(): array
    {
        return collect($this->data())
            ->map(function ($state) {

                if (is_string($state)) {
                    return $state;
                }

                return $state['estado']
                    ?? $state['nombre']
                    ?? $state['name']
                    ?? null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Devuelve las ciudades de un estado.
     */
    public function citiesByState(string $state): array
    {
        $found = collect($this->data())->first(function ($item) use ($state) {

            if (!is_array($item)) {
                return false;
            }

            $name = $item['estado']
                ?? $item['nombre']
                ?? $item['name']
                ?? null;

            return $name === $state;
        });

        if (!$found || !is_array($found)) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | Intentamos encontrar ciudades directamente
        |--------------------------------------------------------------------------
        */

        if (isset($found['ciudades']) && is_array($found['ciudades'])) {
            return collect($found['ciudades'])
                ->map(function ($city) {

                    if (is_string($city)) {
                        return $city;
                    }

                    return $city['ciudad']
                        ?? $city['nombre']
                        ?? $city['name']
                        ?? null;
                })
                ->filter()
                ->values()
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | Si vienen dentro de municipios
        |--------------------------------------------------------------------------
        */

        if (isset($found['municipios']) && is_array($found['municipios'])) {

            return collect($found['municipios'])
                ->flatMap(function ($municipality) {

                    if (!is_array($municipality)) {
                        return [];
                    }

                    if (
                        isset($municipality['ciudades']) &&
                        is_array($municipality['ciudades'])
                    ) {
                        return $municipality['ciudades'];
                    }

                    return [];
                })
                ->map(function ($city) {

                    if (is_string($city)) {
                        return $city;
                    }

                    return $city['ciudad']
                        ?? $city['nombre']
                        ?? $city['name']
                        ?? null;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }

    /**
     * Devuelve el estado completo.
     */
    public function state(string $state): ?array
    {
        return collect($this->data())
            ->first(function ($item) use ($state) {

                if (!is_array($item)) {
                    return false;
                }

                $name = $item['estado']
                    ?? $item['nombre']
                    ?? $item['name']
                    ?? null;

                return $name === $state;
            });
    }

    /**
     * Devuelve todos los estados con sus ciudades.
     */
    public function all(): array
    {
        return $this->data();
    }
}
