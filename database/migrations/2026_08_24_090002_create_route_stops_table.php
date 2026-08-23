<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * route_stops es el corazón del módulo: representa el estado
     * de UNA agencia dentro de UNA ruta/ciclo específico (azul/gris).
     * Nunca se reutiliza entre ciclos — cada ruta nueva crea sus
     * propias filas en estado "pending", lo que da historial gratis.
     */
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();

            $table->foreignId('route_id')
                ->constrained('routes')
                ->cascadeOnDelete();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->restrictOnDelete();

            /**
             * Orden de visita dentro de la ruta.
             */
            $table->unsignedInteger('sequence');

            /**
             * pending = AZUL (todavía no visitada)
             * visited = GRIS (recolección completada)
             * skipped = quedó pendiente al cerrar la ruta
             */
            $table->enum('status', [
                'pending',
                'visited',
                'skipped',
            ])->default('pending');

            $table->timestamp('visited_at')->nullable();

            /**
             * Denormalizado para que el dashboard no tenga que
             * contar package_histories en cada carga.
             */
            $table->unsignedInteger('packages_collected_count')->default(0);

            $table->timestamps();

            /**
             * Una agencia no puede repetirse dos veces en la misma ruta.
             */
            $table->unique(['route_id', 'ally_id']);

            /**
             * El orden de visita debe ser único dentro de una ruta.
             */
            $table->unique(['route_id', 'sequence']);

            $table->index(['route_id', 'status']);
            $table->index('ally_id');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
