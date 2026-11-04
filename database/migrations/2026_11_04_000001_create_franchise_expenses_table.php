<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gastos operativos que registra un Franquiciado (alquiler, nómina,
 * servicios...) para calcular su utilidad neta en Ally\FranchiseFinance.
 * Tabla nueva y aditiva: no toca el ledger de aliados
 * (ally_financial_transactions), que es la relación de comisiones con
 * la empresa y que una franquicia no usa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('franchise_expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Texto libre validado contra FranchiseExpense::CATEGORIES
            // (no enum de BD, para no depender de PostgresEnum).
            $table->string('category', 40);

            $table->string('description', 255);

            $table->decimal('amount_usd', 12, 2);

            $table->date('spent_on');

            $table->timestamps();

            $table->index(['ally_id', 'spent_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('franchise_expenses');
    }
};
