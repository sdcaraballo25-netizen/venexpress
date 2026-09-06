<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | AGREGAR TIPO PAYMENT
        |--------------------------------------------------------------------------
        */

        DB::statement("
            ALTER TABLE ally_financial_transactions
            MODIFY type ENUM(
                'commission',
                'settlement',
                'payment',
                'adjustment',
                'reversal'
            ) NOT NULL
        ");

        /*
        |--------------------------------------------------------------------------
        | RELACIÓN CON LA ORDEN DE PAGO
        |--------------------------------------------------------------------------
        */

        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->foreignId('payment_order_id')
                ->nullable()
                ->after('source_id')
                ->constrained('payment_orders')
                ->nullOnDelete();

            $table->index('payment_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('ally_financial_transactions', function (Blueprint $table) {
            $table->dropForeign(['payment_order_id']);
            $table->dropIndex(['payment_order_id']);
            $table->dropColumn('payment_order_id');
        });

        DB::statement("
            ALTER TABLE ally_financial_transactions
            MODIFY type ENUM(
                'commission',
                'settlement',
                'adjustment',
                'reversal'
            ) NOT NULL
        ");
    }
};
