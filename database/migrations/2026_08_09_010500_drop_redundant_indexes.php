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
        // Cada drop se protege con Schema::hasIndex() porque el estado real
        // de la tabla puede no coincidir con lo que el historial de
        // migraciones asume (p. ej. una base sqlite de otra sesión de
        // trabajo). Sin la comprobación, un índice ya ausente tumba toda
        // la migración con "no such index".
        Schema::table('bcv_rates', function (Blueprint $table) {
            // Duplicado del índice que ya crea unique('effective_date').
            if (Schema::hasIndex('bcv_rates', 'bcv_rates_effective_date_index')) {
                $table->dropIndex('bcv_rates_effective_date_index');
            }
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            // Duplicado del índice compuesto unique(origin_city, destination_city),
            // ya que origin_city es la columna izquierda del compuesto.
            if (Schema::hasIndex('rate_matrices', 'rate_matrices_origin_city_index')) {
                $table->dropIndex('rate_matrices_origin_city_index');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            // Duplicado del índice que ya crea unique('tracking_number').
            if (Schema::hasIndex('packages', 'packages_tracking_number_index')) {
                $table->dropIndex('packages_tracking_number_index');
            }
        });
    }

    /**
     * Revierte la migración, recreando los índices eliminados.
     */
    public function down(): void
    {
        Schema::table('bcv_rates', function (Blueprint $table) {
            if (! Schema::hasIndex('bcv_rates', 'bcv_rates_effective_date_index')) {
                $table->index('effective_date');
            }
        });

        Schema::table('rate_matrices', function (Blueprint $table) {
            if (! Schema::hasIndex('rate_matrices', 'rate_matrices_origin_city_index')) {
                $table->index('origin_city');
            }
        });

        Schema::table('packages', function (Blueprint $table) {
            if (! Schema::hasIndex('packages', 'packages_tracking_number_index')) {
                $table->index('tracking_number');
            }
        });
    }
};
