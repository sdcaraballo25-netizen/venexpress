<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Fase 3 — Registro de pedido y destino. Migración aditiva.
     *
     * pickup_ally_id: el Aliado verificado (Ally.is_verified_destination,
     * ver Fase 1) que el cliente eligió como punto de retiro, para los
     * pedidos que NO requieren delivery (requires_delivery = false).
     * Distinto de ally_id, que siempre es el Aliado de ORIGEN donde se
     * registró el pedido.
     *
     * No implementa todavía ninguna resolución logística: es
     * únicamente el dato que el cliente eligió al momento del
     * registro (Regla 58-B). Nullable porque un pedido con delivery
     * no tiene punto de retiro.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('pickup_ally_id')
                ->nullable()
                ->after('ally_id')
                ->constrained('allies')
                ->nullOnDelete();

            $table->index('pickup_ally_id');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pickup_ally_id');
        });
    }
};
