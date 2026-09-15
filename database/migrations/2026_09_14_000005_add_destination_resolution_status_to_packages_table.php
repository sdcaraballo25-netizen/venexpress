<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Fase 5A — Recepción/verificación interna en HUB. Migración
     * aditiva: no borra ni renombra ninguna columna existente, y no
     * hace backfill de los paquetes ya EN_HUB.
     *
     * destination_resolution_status guarda el status de
     * LogisticsResolutionResult obtenido en el momento de la
     * recepción interna (HubReceptionService::receiveAtWarehouse()):
     * 'resolved', 'no_coverage', 'ambiguous' o 'invalid'. NULL
     * significa que todavía no se ha intentado ninguna resolución
     * logística para este paquete. No es un nuevo estado de Package:
     * current_status sigue siendo la única máquina de estados.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('destination_resolution_status')
                ->nullable()
                ->after('current_warehouse_id');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('destination_resolution_status');
        });
    }
};
