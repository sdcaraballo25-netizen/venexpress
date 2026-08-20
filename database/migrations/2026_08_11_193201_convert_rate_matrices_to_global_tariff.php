<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Antes: cada fila de rate_matrices representaba una ruta
     * específica (origin_city → destination_city).
     *
     * Ahora: la diferencia de precio entre rutas la aporta
     * city_distances (price_per_km_usd × distancia_km), así que
     * rate_matrices deja de tener columnas de ruta y pasa a ser
     * una tarifa única global (base + por kg + por km).
     */
    public function up(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropUnique(['origin_city', 'destination_city']);
            $table->dropIndex(['origin_city']);
            $table->dropIndex(['destination_city']);
            $table->dropColumn(['origin_city', 'destination_city']);

            $table->decimal('price_per_km_usd', 12, 2)->default(0)->after('price_per_kg_usd');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropColumn('price_per_km_usd');

            $table->string('origin_city')->after('id');
            $table->string('destination_city')->after('origin_city');

            $table->unique(['origin_city', 'destination_city']);
            $table->index('origin_city');
            $table->index('destination_city');
        });
    }
};
