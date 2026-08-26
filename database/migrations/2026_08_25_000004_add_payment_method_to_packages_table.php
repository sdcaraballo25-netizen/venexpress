<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            /**
             * Método de pago usado en taquilla al registrar la guía.
             *
             * String simple (no enum) para poder ajustar la lista de
             * métodos sin nueva migración. Valores sugeridos iniciales:
             * efectivo_usd, efectivo_ves, pago_movil, transferencia, zelle.
             *
             * Necesario para el desglose de "métodos de pago" y el
             * "cuadre de caja" del dashboard del Aliado (RF-ALI-01).
             */
            $table->string('payment_method', 30)
                ->nullable()
                ->after('is_cod');

            $table->index(['ally_id', 'payment_method']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['ally_id', 'payment_method']);
            $table->dropColumn('payment_method');
        });
    }
};
