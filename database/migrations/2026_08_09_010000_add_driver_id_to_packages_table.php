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
        Schema::table('packages', function (Blueprint $table) {
            /**
             * Chofer asignado actualmente a este paquete.
             *
             * Nullable porque un paquete puede no tener
             * chofer asignado todavía (ej. RECIBIDO_AGENCIA,
             * EN_HUB, LISTO_RETIRO).
             */
            $table->foreignId('driver_id')
                ->nullable()
                ->after('ally_id')
                ->constrained('drivers')
                ->nullOnDelete();

            /**
             * Consultas del chofer: sus paquetes activos
             * filtrados por estado.
             */
            $table->index([
                'driver_id',
                'current_status',
            ]);
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex([
                'packages_driver_id_current_status_index',
            ]);

            $table->dropConstrainedForeignId('driver_id');
        });
    }
};
