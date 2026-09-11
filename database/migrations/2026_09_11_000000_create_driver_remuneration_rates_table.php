<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_remuneration_rates', function (Blueprint $table) {
            $table->id();

            /**
             * Monto en USD que se paga al repartidor por cada
             * paquete entregado. Es una tarifa GLOBAL: aplica por
             * igual a todos los repartidores.
             */
            $table->decimal('amount_usd', 10, 2);

            $table->timestamp('effective_at');

            $table->string('source', 20)->default('manual');

            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('effective_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_remuneration_rates');
    }
};
