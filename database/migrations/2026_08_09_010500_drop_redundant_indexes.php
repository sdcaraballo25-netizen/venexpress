<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Elimina índices explícitos que quedaron duplicados
     * con índices únicos ya existentes en las tablas.
     */
    public function up(): void
    {
        Schema::table('bcv_rates', function (Blueprint $table) {
            // Duplicado del índice que ya crea unique('effective_date').
            $table->dropIndex('bcv_rates_effective_date_index');
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            // Duplicado del índice compuesto unique(origin_city, destination_city),
            // ya que origin_city es la columna izquierda del compuesto.
            $table->dropIndex('rate_matrices_origin_city_index');
        });

        Schema::table('packages', function (Blueprint $table) {
            // Duplicado del índice que ya crea unique('tracking_number').
            $table->dropIndex('packages_tracking_number_index');
        });
    }

    /**
     * Revierte la migración, recreando los índices eliminados.
     */
    public function down(): void
    {
        Schema::table('bcv_rates', function (Blueprint $table) {
            $table->index('effective_date');
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->index('origin_city');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->index('tracking_number');
        });
    }
};
