<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Fase 4 — Resolución logística. Migración aditiva: crea una
     * tabla nueva, no toca ninguna existente.
     *
     * Cobertura de un Warehouse (HUB) sobre una zona geográfica del
     * catálogo unificado (Fase 1):
     *
     * - state + city: cobertura específica de una ciudad.
     * - state + city = null: cobertura de todo el estado (fallback).
     *
     * La prioridad entre ambas, la detección de duplicados y de
     * ambigüedad (dos almacenes activos cubriendo exactamente la
     * misma zona) se resuelven en la capa de aplicación
     * (LogisticsResolutionService / WarehousesManager), no aquí: un
     * índice único no puede expresar "una sola fila con city NULL por
     * estado" de forma portable.
     */
    public function up(): void
    {
        Schema::create('warehouse_coverages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            $table->string('state');
            $table->string('city')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('warehouse_id');
            $table->index(['state', 'city']);
            $table->index('is_active');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_coverages');
    }
};
