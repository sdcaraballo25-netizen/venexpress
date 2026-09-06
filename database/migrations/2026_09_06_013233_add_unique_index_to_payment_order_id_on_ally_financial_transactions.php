<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refuerza a nivel de base de datos la idempotencia que ya
     * aplica AllyFinancialService::recordAllyDebtPayment(): una
     * misma PaymentOrder nunca debe generar más de un movimiento
     * financiero. El índice anterior (no único) solo ayudaba a
     * las consultas, no lo impedía.
     *
     * MySQL no permite borrar un índice mientras una llave
     * foránea dependa de él, así que primero se elimina la FK,
     * luego el índice viejo, y al final se recrean ambos usando
     * el nuevo índice único como respaldo.
     */
    public function up(): void
    {
        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_order_id']);
            $table->dropIndex(['payment_order_id']);
        });

        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->unique('payment_order_id');

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_order_id']);
            $table->dropUnique(['payment_order_id']);
        });

        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->index('payment_order_id');

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders')
                ->nullOnDelete();
        });
    }
};
