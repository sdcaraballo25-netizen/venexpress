<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Prepara —sin implementarlo todavía— el concepto de "Aliado
     * verificado como punto final de entrega/retiro" (distinto de un
     * Aliado normal, que solo origina pedidos). Migración puramente
     * aditiva: no toca ninguna columna existente ni borra datos.
     *
     * El flujo completo de verificación documental (solicitud,
     * revisión, documentos adjuntos) se deja para una fase posterior;
     * por ahora solo se deja el estado listo para que el Admin pueda
     * activar manualmente esta capacidad de forma temporal.
     */
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->boolean('is_verified_destination')
                ->default(false)
                ->after('status');

            $table->string('destination_verification_status')
                ->nullable()
                ->after('is_verified_destination');

            $table->timestamp('destination_verified_at')
                ->nullable()
                ->after('destination_verification_status');

            $table->index('is_verified_destination');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropIndex(['is_verified_destination']);
            $table->dropColumn([
                'is_verified_destination',
                'destination_verification_status',
                'destination_verified_at',
            ]);
        });
    }
};
