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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ally_id')
                ->constrained('allies')
                ->cascadeOnDelete();

            /**
             * Paquete relacionado, si aplica. Puede haber incidencias
             * sin paquete asociado (ej. reclamo general de servicio).
             */
            $table->foreignId('package_id')
                ->nullable()
                ->constrained('packages')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | REPORTADO POR
            |--------------------------------------------------------------------------
            */

            /**
             * Usuario que reportó la incidencia (Aliado Administrador
             * o Aliado Taquilla; ambos son users.id, diferenciados
             * por users.role).
             */
            $table->foreignId('reported_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | CONTENIDO
            |--------------------------------------------------------------------------
            */

            $table->string('type');

            $table->text('description');

            $table->enum('status', ['abierta', 'en_proceso', 'resuelta', 'cerrada'])
                ->default('abierta');

            $table->text('resolution_notes')->nullable();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['ally_id', 'status']);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
