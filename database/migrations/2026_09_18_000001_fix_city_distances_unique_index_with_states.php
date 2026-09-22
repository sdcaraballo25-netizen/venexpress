<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El índice único original solo cubría (city_a, city_b), de antes de
 * que existieran state_a/state_b (ver
 * 2026_09_03_000001_add_states_to_city_distances_table). Pero
 * CityDistance::setDistance()/between() ya buscan y comparan por las
 * cuatro columnas (city_a, state_a, city_b, state_b) — así que dos
 * ciudades homónimas en estados distintos (ej. "San Fernando" en
 * Apure y en otro estado) nunca se ven como "la misma fila" para la
 * app, pero SÍ chocan contra el índice único viejo al intentar
 * insertar la segunda: setDistance() lanza una QueryException que
 * TariffService::findDistanceKm() atrapa con un catch(\Throwable)
 * amplio y cae de vuelta a betweenCities() (que ignora el estado),
 * devolviendo silenciosamente la distancia de la ciudad equivocada
 * para calcular el precio.
 *
 * Se agrega esta migración nueva en lugar de editar las dos
 * originales para no romper el historial de `php artisan migrate` en
 * entornos donde ya hayan corrido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('city_distances', function (Blueprint $table) {
            $table->dropUnique(['city_a', 'city_b']);

            $table->unique(
                ['city_a', 'state_a', 'city_b', 'state_b'],
                'city_distances_city_state_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('city_distances', function (Blueprint $table) {
            $table->dropUnique('city_distances_city_state_unique');

            $table->unique(['city_a', 'city_b']);
        });
    }
};
