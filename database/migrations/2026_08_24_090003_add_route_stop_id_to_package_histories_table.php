<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Vincula (opcionalmente) un evento del historial de un paquete
     * con la parada de ruta en la que ocurrió. Esto evita tener que
     * crear una tabla "collections" paralela: el evento de
     * recolección YA es un PackageHistory con status
     * RECOLECTADO_VENEXPRESS; solo le faltaba saber en qué ruta/ciclo
     * ocurrió.
     */
    public function up(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->foreignId('route_stop_id')
                ->nullable()
                ->after('package_id')
                ->constrained('route_stops')
                ->nullOnDelete();

            $table->index(['route_stop_id', 'status']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->dropIndex(['package_histories_route_stop_id_status_index']);
            $table->dropConstrainedForeignId('route_stop_id');
        });
    }
};
