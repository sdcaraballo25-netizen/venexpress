<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Fase 4 — Resolución logística. Migración aditiva: no toca ni
     * borra ninguna columna existente, y no hace backfill de los
     * paquetes ya creados (quedan con ambas columnas en null).
     *
     * - destination_warehouse_id: HUB que LogisticsResolutionService
     *   resuelve a partir de destination_state/destination_city. Se
     *   deja como columna para que una fase posterior pueda
     *   persistirlo de forma explícita; esta fase solo resuelve y
     *   devuelve el valor, no lo escribe automáticamente.
     *
     * - current_warehouse_id: HUB donde el paquete se encuentra
     *   físicamente en este momento. Todavía no existe ningún flujo
     *   (escaneo, recepción) que lo actualice — eso es Fase 5+.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('destination_warehouse_id')
                ->nullable()
                ->after('pickup_ally_id')
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->foreignId('current_warehouse_id')
                ->nullable()
                ->after('destination_warehouse_id')
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->index('destination_warehouse_id');
            $table->index('current_warehouse_id');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_warehouse_id');
            $table->dropConstrainedForeignId('destination_warehouse_id');
        });
    }
};
