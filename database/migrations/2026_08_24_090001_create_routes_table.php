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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();

            /**
             * Ciudad de la ruta.
             *
             * Se guarda como string, igual convención que
             * allies.city y CityDistance.city_a/city_b — en
             * Venexpress la ciudad no es una tabla, es un
             * string normalizado.
             */
            $table->string('city');

            /**
             * Nombre/identificador de la ruta.
             *
             * Ejemplo: "Ruta Centro - Turno Mañana"
             */
            $table->string('name');

            /**
             * Chofer asignado. Nullable porque una ruta puede
             * crearse en borrador antes de asignar repartidor.
             */
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('drivers')
                ->nullOnDelete();

            /**
             * Administrador (admin_principal o admin_operativo)
             * que creó la ruta.
             */
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            /**
             * Máquina de estados de la ruta.
             */
            $table->enum('status', [
                'draft',
                'assigned',
                'in_progress',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('city');
            $table->index('status');
            $table->index(['driver_id', 'status']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
