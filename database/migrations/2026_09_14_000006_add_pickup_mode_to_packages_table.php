<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Modalidad de destino final del paquete. Migración aditiva: no
     * borra ni renombra ninguna columna existente, no hace backfill
     * de paquetes ya creados.
     *
     * pickup_mode solo aplica cuando requires_delivery = false:
     * - 'hub': el cliente retira directamente en el HUB destino
     *   resuelto (destination_warehouse_id). NO se selecciona un HUB
     *   manualmente ni se guarda ningún ID de HUB aquí — eso lo sigue
     *   resolviendo LogisticsResolutionService, sin cambios.
     * - 'ally': el cliente retira en pickup_ally_id (Fase 3, sin
     *   cambios en su validación ni en su significado).
     *
     * Cuando requires_delivery = true, pickup_mode permanece NULL —
     * el flujo de Delivery no cambia en absoluto.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('pickup_mode')
                ->nullable()
                ->after('pickup_ally_id');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('pickup_mode');
        });
    }
};
