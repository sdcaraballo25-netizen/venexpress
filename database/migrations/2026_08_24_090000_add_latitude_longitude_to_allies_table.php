<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Coordenadas de la taquilla aliada, necesarias para pintarla
     * en el mapa del módulo de Gestión de Rutas. Nullable porque
     * las agencias existentes no las tienen todavía: se completan
     * al editar la agencia (clic en el mapa) hasta que se migren.
     */
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)
                ->nullable()
                ->after('address');

            $table->decimal('longitude', 10, 7)
                ->nullable()
                ->after('latitude');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
