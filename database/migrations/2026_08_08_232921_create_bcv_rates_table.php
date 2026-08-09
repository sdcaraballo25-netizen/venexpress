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
        Schema::create('bcv_rates', function (Blueprint $table) {
            $table->id();

            /**
             * Tasa oficial del dólar BCV en VES.
             *
             * Ejemplo:
             * 150.250000
             */
            $table->decimal('rate', 15, 6);

            /**
             * Fecha desde la cual esta tasa es válida.
             *
             * Solo puede existir una tasa por fecha.
             */
            $table->date('effective_date')
                ->unique();

            $table->timestamps();

            /**
             * Índice para consultar rápidamente
             * la tasa más reciente.
             */
            $table->index('effective_date');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('bcv_rates');
    }
};