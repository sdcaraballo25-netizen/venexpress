<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Una parada de ruta (RouteStop) representaba únicamente una
     * agencia aliada. Las rutas de HUB Distribución terminan en un
     * almacén propio de Venexpress, no en un Ally — por eso ally_id
     * pasa a ser opcional y se agrega warehouse_id como alternativa.
     * Una parada apunta a UNO de los dos, nunca a ambos; cuál de los
     * dos se usa depende del route_type de la ruta (ver
     * RouteService::syncStops()).
     */
    public function up(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->foreignId('ally_id')->nullable()->change();

            $table->foreignId('warehouse_id')
                ->nullable()
                ->after('ally_id')
                ->constrained('warehouses')
                ->restrictOnDelete();

            $table->unique(['route_id', 'warehouse_id']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('route_stops', function (Blueprint $table) {
            $table->dropUnique(['route_id', 'warehouse_id']);
            $table->dropConstrainedForeignId('warehouse_id');

            $table->foreignId('ally_id')->nullable(false)->change();
        });
    }
};
