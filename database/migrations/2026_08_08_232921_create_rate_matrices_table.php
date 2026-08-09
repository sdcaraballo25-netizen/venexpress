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
        Schema::create('rate_matrices', function (Blueprint $table) {
            $table->id();

            /**
             * Ciudad donde se origina el envío.
             *
             * Ejemplo:
             * Caracas
             */
            $table->string('origin_city');

            /**
             * Ciudad destino del envío.
             *
             * Ejemplo:
             * Valencia
             */
            $table->string('destination_city');

            /**
             * Precio cobrado por cada kilogramo
             * facturable, expresado en USD.
             */
            $table->decimal(
                'price_per_kg_usd',
                12,
                2
            );

            /**
             * Precio base de la ruta en USD.
             */
            $table->decimal(
                'base_price_usd',
                12,
                2
            )->default(0);

            $table->timestamps();

            /**
             * Una misma ruta no puede repetirse.
             *
             * Ejemplo:
             *
             * Caracas → Valencia
             *
             * solo puede tener una configuración.
             */
            $table->unique([
                'origin_city',
                'destination_city',
            ]);

            /**
             * Índices para búsquedas rápidas
             * por origen y destino.
             */
            $table->index('origin_city');
            $table->index('destination_city');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_matrices');
    }
};