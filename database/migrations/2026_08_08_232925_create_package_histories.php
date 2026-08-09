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
        Schema::create('package_histories', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | GUÍA
            |--------------------------------------------------------------------------
            */

            /**
             * Guía a la que pertenece este evento.
             */
            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
            */

            /**
             * Estado alcanzado por la guía.
             */
            $table->enum('status', [
                'RECIBIDO_AGENCIA',
                'RECOLECTADO_VENEXPRESS',
                'EN_HUB',
                'EN_TRANSITO_NACIONAL',
                'LISTO_RETIRO',
                'ENTREGADO',
            ]);

            /*
            |--------------------------------------------------------------------------
            | UBICACIÓN
            |--------------------------------------------------------------------------
            */

            /**
             * Lugar donde se produjo el evento.
             *
             * Ejemplos:
             *
             * Agencia Caracas Centro
             * Hub Valencia
             * Ruta Caracas-Valencia
             */
            $table->string('location_description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | USUARIO
            |--------------------------------------------------------------------------
            */

            /**
             * Usuario que realizó el escaneo
             * o registró el evento.
             *
             * Puede ser:
             *
             * - Aliado
             * - Chofer
             * - Administrador
             */
            $table->foreignId('scanned_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | ÍNDICES
            |--------------------------------------------------------------------------
            */

            /**
             * Permite obtener rápidamente
             * el historial de una guía.
             */
            $table->index([
                'package_id',
                'created_at',
            ]);

            /**
             * Permite consultar los eventos
             * registrados por un usuario.
             */
            $table->index([
                'scanned_by_user_id',
                'created_at',
            ]);

            /**
             * Consultas por estado.
             */
            $table->index('status');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_histories');
    }
};