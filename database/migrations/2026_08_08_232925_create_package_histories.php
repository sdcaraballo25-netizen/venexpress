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

            $table->foreignId('package_id')
                ->constrained('packages')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ESTADO
            |--------------------------------------------------------------------------
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
            | TIPO DE EVENTO
            |--------------------------------------------------------------------------
            */

            $table->string('event_type')
                ->default('MOVIMIENTO');

            /*
            |--------------------------------------------------------------------------
            | ORIGEN / DESTINO
            |--------------------------------------------------------------------------
            */

            $table->string('origin_location')
                ->nullable();

            $table->string('destination_location')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | UBICACIÓN
            |--------------------------------------------------------------------------
            */

            $table->string('location_description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | USUARIO
            |--------------------------------------------------------------------------
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

            $table->index([
                'package_id',
                'created_at',
            ]);

            $table->index([
                'package_id',
                'event_type',
                'created_at',
            ]);

            $table->index([
                'scanned_by_user_id',
                'created_at',
            ]);

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
