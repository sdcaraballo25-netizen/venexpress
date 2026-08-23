<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convierte rate_matrices de una matriz por ruta a una tarifa global.
     *
     * La tabla originalmente tenía:
     * - origin_city
     * - destination_city
     * - price_per_kg_usd
     * - base_price_usd
     *
     * La tarifa global conserva los precios y agrega posteriormente
     * price_per_km_usd. Las ciudades pasan a gestionarse mediante
     * city_distances.
     */
    public function up(): void
    {
        // IMPORTANTE para SQLite: antes de eliminar columnas debemos eliminar
        // todos los índices que dependen de ellas. Si SQLite reconstruye la
        // tabla mientras esos índices siguen registrados, falla con:
        // "error in index ... after drop column".
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropIndex('rate_matrices_destination_city_index');
            $table->dropUnique('rate_matrices_origin_city_destination_city_unique');
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropColumn(['origin_city', 'destination_city']);
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            if (! Schema::hasColumn('rate_matrices', 'price_per_km_usd')) {
                $table->decimal('price_per_km_usd', 12, 2)
                    ->default(0)
                    ->after('price_per_kg_usd');
            }
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
