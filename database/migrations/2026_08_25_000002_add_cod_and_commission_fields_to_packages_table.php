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
            /*
            |--------------------------------------------------------------------------
            | COBRO EN DESTINO (COD) — RF-ALI-04
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_cod')
                ->default(false)
                ->after('current_status');

            /**
             * Monto a cobrar al destinatario contra entrega.
             *
             * Independiente de total_price_usd (que es el costo del
             * envío): este es el valor del producto/mercancía.
             */
            $table->decimal('cod_amount_usd', 12, 2)
                ->nullable()
                ->after('is_cod');

            $table->enum('cod_status', ['pendiente', 'liquidado'])
                ->nullable()
                ->after('cod_amount_usd');

            $table->timestamp('cod_liquidated_at')
                ->nullable()
                ->after('cod_status');

            /*
            |--------------------------------------------------------------------------
            | COMISIÓN DEL ALIADO — RF-ALI-08 / RF-ALI-09
            |--------------------------------------------------------------------------
            */

            /**
             * Copia histórica del % de comisión del aliado al momento
             * de crear la guía (mismo patrón que bcv_rate_used). Evita
             * que cambios futuros en allies.commission_percentage
             * alteren el historial ya generado.
             */
            $table->decimal('commission_percentage_used', 5, 2)
                ->nullable()
                ->after('bcv_rate_used');

            $table->decimal('commission_amount_usd', 12, 2)
                ->nullable()
                ->after('commission_percentage_used');

            /**
             * Consultas del aliado sobre cobros COD pendientes/liquidados.
             */
            $table->index(['ally_id', 'cod_status']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['ally_id', 'cod_status']);

            $table->dropColumn([
                'is_cod',
                'cod_amount_usd',
                'cod_status',
                'cod_liquidated_at',
                'commission_percentage_used',
                'commission_amount_usd',
            ]);
        });
    }
};
