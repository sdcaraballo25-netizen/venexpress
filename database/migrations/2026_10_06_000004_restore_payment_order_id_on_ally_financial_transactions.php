<?php

use App\Support\PostgresEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Restaura payment_order_id y el tipo "payment" del ledger del aliado
|--------------------------------------------------------------------------
|
| 2026_09_05_210000_add_payment_type_to_ally_financial_transactions
| creaba la columna y ampliaba el enum en 440ac13, pero 2dde469 dejó
| esa migración solo con el índice único. Las bases migradas antes la
| tienen; una instalación nueva no, y
| AllyFinancialService::recordAllyDebtPayment() fallaba con error SQL.
|
| Solo agrega lo que falte: en una base que ya lo tiene no cambia nada.
|
*/

return new class extends Migration
{
    private string $table = 'ally_financial_transactions';

    private string $uniqueIndex = 'ally_financial_transactions_payment_order_id_unique';

    public function up(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        if (PostgresEnum::active()) {
            // En PostgreSQL el enum es un CHECK: se reemplaza (idempotente).
            PostgresEnum::allow($this->table, 'type', ['commission', 'settlement', 'payment', 'adjustment', 'reversal']);
        } elseif (! $this->typeAcceptsPayment()) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->enum('type', [
                    'commission',
                    'settlement',
                    'payment',
                    'adjustment',
                    'reversal',
                ])->change();
            });
        }

        if (! Schema::hasColumn($this->table, 'payment_order_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreignId('payment_order_id')
                    ->nullable()
                    ->after('source_id')
                    ->constrained('payment_orders')
                    ->nullOnDelete();

                $table->unique('payment_order_id', $this->uniqueIndex);
            });
        }
    }

    public function down(): void
    {
        // No se revierte: en las bases antiguas estas columnas vienen de
        // 2026_09_05_210000 y quitarlas aquí borraría datos de pagos.
    }

    private function typeAcceptsPayment(): bool
    {
        $column = collect(Schema::getColumns($this->table))
            ->firstWhere('name', 'type');

        return $column !== null
            && str_contains((string) ($column['type'] ?? ''), "'payment'");
    }
};
