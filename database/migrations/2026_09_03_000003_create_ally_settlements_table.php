<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ally_settlements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            $table->decimal(
                'amount_usd',
                12,
                2
            );

            $table->enum('status', [
                'pending',
                'paid',
                'cancelled',
                'reversed',
            ])->default('pending');

            $table->enum('payment_method', [
                'transferencia',
                'pago_movil',
                'efectivo_usd',
                'efectivo_ves',
                'zelle',
                'otro',
            ])->nullable();

            $table->string(
                'payment_reference',
                150
            )->nullable();

            $table->text(
                'notes'
            )->nullable();

            $table->foreignId(
                'requested_by_user_id'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId(
                'paid_by_user_id'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId(
                'reversed_by_user_id'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp(
                'requested_at'
            )->nullable();

            $table->timestamp(
                'paid_at'
            )->nullable();

            $table->timestamp(
                'reversed_at'
            )->nullable();

            $table->foreignId(
                'reversal_transaction_id'
            )
                ->nullable()
                ->constrained(
                    'ally_financial_transactions'
                )
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'ally_id',
                'status',
            ]);

            $table->index(
                'payment_reference'
            );

            $table->index(
                'paid_at'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'ally_settlements'
        );
    }
};
