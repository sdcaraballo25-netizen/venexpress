<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Fase 2 — Rutas multiestado. Migración aditiva: no borra ni
     * renombra ninguna columna existente.
     *
     * - origin_warehouse_id / return_warehouse_id: HUB desde el que
     *   el driver parte y al que regresa (Reglas 32/41). Ambos
     *   opcionales — una ruta creada antes de esta fase, o una ruta
     *   nueva sin HUB definido explícitamente, sigue siendo válida.
     *
     * - routes.city pasa de NOT NULL a nullable: el Estado y la
     *   Ciudad de la ruta dejan de ser un dato obligatorio para
     *   crearla (Reglas 24/26) — quedan como metadato descriptivo
     *   opcional, ya no como requisito. routes.state ya era nullable
     *   desde 2026_08_25_214614_add_state_to_routes_table.php.
     */
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->foreignId('origin_warehouse_id')
                ->nullable()
                ->after('route_type')
                ->constrained('warehouses')
                ->nullOnDelete();

            $table->foreignId('return_warehouse_id')
                ->nullable()
                ->after('origin_warehouse_id')
                ->constrained('warehouses')
                ->nullOnDelete();
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->string('city')->nullable()->change();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('city')->nullable(false)->change();
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('return_warehouse_id');
            $table->dropConstrainedForeignId('origin_warehouse_id');
        });
    }
};
