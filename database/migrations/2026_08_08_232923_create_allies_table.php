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
        Schema::create('allies', function (Blueprint $table) {
            $table->id();

            /**
             * Usuario asociado a la taquilla aliada.
             *
             * Un usuario puede tener solamente
             * un registro de aliado.
             */
            $table->foreignId('user_id')
                ->unique()
                ->constrained('users')
                ->cascadeOnDelete();

            /**
             * Nombre comercial de la taquilla.
             */
            $table->string('business_name');

            /**
             * RIF venezolano.
             *
             * Ejemplo:
             * J-12345678-9
             */
            $table->string('rif', 20)
                ->unique();

            /**
             * Ciudad donde se encuentra
             * la taquilla.
             */
            $table->string('city');

            /**
             * Dirección física de la taquilla.
             */
            $table->string('address');

            /**
             * Porcentaje de comisión que recibe
             * el aliado por los envíos registrados.
             *
             * Ejemplo:
             * 10.00 = 10%
             */
            $table->decimal(
                'commission_percentage',
                5,
                2
            )->default(10.00);

            $table->timestamps();

            /**
             * Índices para búsquedas administrativas.
             */
            $table->index('city');
            $table->index('business_name');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('allies');
    }
};