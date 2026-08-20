<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('city_distances', function (Blueprint $table) {
            $table->id();

            /**
             * Las distancias son simétricas (A→B = B→A), así que
             * SIEMPRE se guardan y consultan con las ciudades
             * ordenadas alfabéticamente (ver CityDistance::between()).
             * Esto evita tener dos filas para la misma ruta.
             *
             * Ejemplo:
             * "Barquisimeto" y "Caracas" → se guarda como
             * city_a = "Barquisimeto", city_b = "Caracas"
             */
            $table->string('city_a');
            $table->string('city_b');

            $table->unsignedInteger('distance_km');

            $table->timestamps();

            $table->unique(['city_a', 'city_b']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('city_distances');
    }
};
