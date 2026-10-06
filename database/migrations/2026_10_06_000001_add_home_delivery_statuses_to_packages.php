<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PREVIOUS_STATUSES = [
        'RECIBIDO_AGENCIA',
        'RECOLECTADO_VENEXPRESS',
        'EN_HUB',
        'EN_TRANSITO_NACIONAL',
        'LISTO_RETIRO',
        'ENTREGADO',
        'EN_DEVOLUCION',
        'DEVUELTO',
    ];

    private const DELIVERY_STATUSES = [
        'PENDIENTE_ENTREGA',
        'EN_RUTA',
        'ENTREGA_FALLIDA',
    ];

    /**
     * Fase 2 — entrega a domicilio con estados propios:
     *
     * - PENDIENTE_ENTREGA: en el almacén destino esperando repartidor
     *   (antes compartía LISTO_RETIRO con el retiro en agencia).
     * - EN_RUTA: un repartidor lo lleva (antes volvía a
     *   EN_TRANSITO_NACIONAL, como si viajara entre HUBs otra vez).
     * - ENTREGA_FALLIDA: el repartidor no pudo entregarlo.
     *
     * Además, el PIN de entrega (solo su hash; el PIN en claro se envía
     * al destinatario por correo al salir a reparto), el número de
     * intentos fallidos de PIN, y la referencia/comprobante del cobro
     * contra entrega por pago móvil o transferencia.
     *
     * Cambios aditivos: valores nuevos en los enum y columnas nullable.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('current_status', [...self::PREVIOUS_STATUSES, ...self::DELIVERY_STATUSES])
                ->default('RECIBIDO_AGENCIA')
                ->change();

            $table->string('delivery_pin_hash')->nullable();
            $table->timestamp('delivery_pin_generated_at')->nullable();
            $table->unsignedTinyInteger('delivery_pin_failed_attempts')->default(0);

            $table->string('cod_payment_reference', 100)->nullable();
            $table->string('cod_payment_proof_path')->nullable();
        });

        Schema::table('package_histories', function (Blueprint $table) {
            $table->enum('status', [...self::PREVIOUS_STATUSES, ...self::DELIVERY_STATUSES])
                ->change();
        });

        // Paquetes a domicilio que ya esperaban repartidor en su almacén
        // destino: pasan al estado nuevo para que se puedan seguir
        // tomando/asignando (solo se toma desde PENDIENTE_ENTREGA).
        DB::table('packages')
            ->where('current_status', 'LISTO_RETIRO')
            ->where('requires_delivery', true)
            ->whereNull('driver_id')
            ->update(['current_status' => 'PENDIENTE_ENTREGA']);

        // Los que un repartidor de entrega ya llevaba (asignados o
        // tomados antes de esta fase) quedan EN_RUTA, que es desde donde
        // ahora se entrega. Un paquete en tránsito con un chofer de HUB
        // sigue viajando entre HUBs y no se toca.
        DB::table('packages')
            ->where('current_status', 'EN_TRANSITO_NACIONAL')
            ->where('requires_delivery', true)
            ->whereIn('driver_id', DB::table('drivers')->where('driver_type', 'delivery')->select('id'))
            ->update(['current_status' => 'EN_RUTA']);
    }

    public function down(): void
    {
        // Revertir los enum con filas que ya usan los valores nuevos
        // fallaría (o truncaría el dato en MySQL sin modo estricto).
        $inUse = DB::table('packages')->whereIn('current_status', self::DELIVERY_STATUSES)->exists()
            || DB::table('package_histories')->whereIn('status', self::DELIVERY_STATUSES)->exists();

        if ($inUse) {
            throw new RuntimeException(
                'Hay guías en los estados de entrega a domicilio: no se puede revertir esta migración sin perder esos datos.'
            );
        }

        Schema::table('package_histories', function (Blueprint $table) {
            $table->enum('status', self::PREVIOUS_STATUSES)->change();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_pin_hash',
                'delivery_pin_generated_at',
                'delivery_pin_failed_attempts',
                'cod_payment_reference',
                'cod_payment_proof_path',
            ]);

            $table->enum('current_status', self::PREVIOUS_STATUSES)
                ->default('RECIBIDO_AGENCIA')
                ->change();
        });
    }
};
