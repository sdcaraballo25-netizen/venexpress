<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')
                ->constrained('drivers')
                ->restrictOnDelete();

            $table->foreignId('package_id')
                ->unique()
                ->constrained('packages')
                ->restrictOnDelete();

            $table->decimal('amount_usd', 12, 2);

            $table->enum('status', [
                'pendiente',
                'pagada',
                'cancelada',
            ])->default('pendiente');

            $table->timestamp('paid_at')->nullable();

            $table->foreignId('paid_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payments');
    }
};
