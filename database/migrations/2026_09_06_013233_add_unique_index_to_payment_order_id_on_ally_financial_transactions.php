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
     */
    public function up(): void
    {
        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->dropIndex(['payment_order_id']);

            $table->unique('payment_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->dropUnique(['payment_order_id']);

            $table->index('payment_order_id');
        });
    }
};
