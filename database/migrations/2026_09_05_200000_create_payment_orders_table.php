<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | REFERENCIA ÚNICA
            |--------------------------------------------------------------------------
            */

            $table->string('order_number', 40)->unique();

            /*
            |--------------------------------------------------------------------------
            | QUIÉN PAGA
            |--------------------------------------------------------------------------
            |
            | ally:      aliado
            | customer:  cliente
            | driver:    repartidor
            | user:      otro usuario autorizado
            |
            */

            $table->enum('payer_type', [
                'ally',
                'customer',
                'driver',
                'user',
            ]);

            $table->unsignedBigInteger('payer_id');

            /*
            |--------------------------------------------------------------------------
            | CONCEPTO DEL PAGO
            |--------------------------------------------------------------------------
            |
            | ally_debt:       deuda del aliado
            | package:         pago de envío
            | cod:             cobro a destino
            | other:           otro concepto autorizado
            |
            */

            $table->enum('purpose', [
                'ally_debt',
                'package',
                'cod',
                'other',
            ]);

            /*
            |--------------------------------------------------------------------------
            | RELACIONES OPCIONALES
            |--------------------------------------------------------------------------
            */

            $table->foreignId('ally_id')
                ->nullable()
                ->constrained('allies')
                ->nullOnDelete();

            $table->foreignId('package_id')
                ->nullable()
                ->constrained('packages')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | MONTO
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount_usd', 12, 2);

            /*
            |--------------------------------------------------------------------------
            | MÉTODO DE PAGO
            |--------------------------------------------------------------------------
            |
            | pago_movil:       pago móvil
            | automatic_debit:  débito automático
            |
            */

            $table->enum('payment_method', [
                'pago_movil',
                'automatic_debit',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'processing',
                'confirmed',
                'rejected',
                'expired',
                'reversed',
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | REFERENCIA BANCARIA
            |--------------------------------------------------------------------------
            |
            | Se llenará cuando el banco confirme la operación.
            |
            */

            $table->string('bank_reference', 150)
                ->nullable()
                ->unique();

            /*
            |--------------------------------------------------------------------------
            | DATOS DE LA OPERACIÓN
            |--------------------------------------------------------------------------
            */

            $table->string('bank_code', 30)
                ->nullable();

            $table->string('bank_name', 150)
                ->nullable();

            $table->timestamp('confirmed_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | DATOS ADICIONALES
            |--------------------------------------------------------------------------
            |
            | Aquí podremos guardar información no sensible
            | de la operación, como respuesta del banco,
            | código de rechazo o datos de conciliación.
            |
            */

            $table->json('metadata')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUDITORÍA
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('confirmed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */

            $table->index([
                'payer_type',
                'payer_id',
            ]);

            $table->index([
                'status',
                'payment_method',
            ]);

            $table->index([
                'ally_id',
                'status',
            ]);

            $table->index([
                'package_id',
                'purpose',
            ]);

            $table->index('confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
