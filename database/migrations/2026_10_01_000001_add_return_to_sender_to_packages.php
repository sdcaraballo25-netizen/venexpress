<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BASE_STATUSES = [
        'RECIBIDO_AGENCIA',
        'RECOLECTADO_VENEXPRESS',
        'EN_HUB',
        'EN_TRANSITO_NACIONAL',
        'LISTO_RETIRO',
        'ENTREGADO',
    ];

    private const RETURN_STATUSES = [
        'EN_DEVOLUCION',
        'DEVUELTO',
    ];

    /**
     * Devolución al remitente: un admin la inicia sobre un paquete que
     * no se pudo entregar (EN_DEVOLUCION) y la agencia de origen la
     * cierra al entregárselo al remitente verificando su cédula
     * (DEVUELTO). Ver PackageService::startReturn()/completeReturn().
     *
     * Cambios aditivos: dos valores nuevos en los enum de estado, el
     * valor "cancelado" para el COD de un paquete devuelto (nunca se
     * entregó, así que no hay nada que cobrar) y tres columnas
     * nullable con el motivo y las fechas de la devolución.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->enum('current_status', [...self::BASE_STATUSES, ...self::RETURN_STATUSES])
                ->default('RECIBIDO_AGENCIA')
                ->change();

            $table->enum('cod_status', ['pendiente', 'liquidado', 'cancelado'])
                ->nullable()
                ->change();

            $table->text('return_reason')->nullable();
            $table->timestamp('return_requested_at')->nullable();
            $table->timestamp('returned_at')->nullable();
        });

        Schema::table('package_histories', function (Blueprint $table) {
            $table->enum('status', [...self::BASE_STATUSES, ...self::RETURN_STATUSES])
                ->change();
        });
    }

    public function down(): void
    {
        // Revertir los enum con filas que ya usan los valores nuevos
        // fallaría (o, peor, truncaría el dato en silencio en MySQL sin
        // modo estricto). Mejor detenerse y que alguien decida qué
        // hacer con esas guías.
        $inUse = DB::table('packages')->whereIn('current_status', self::RETURN_STATUSES)->exists()
            || DB::table('packages')->where('cod_status', 'cancelado')->exists()
            || DB::table('package_histories')->whereIn('status', self::RETURN_STATUSES)->exists();

        if ($inUse) {
            throw new RuntimeException(
                'Hay guías con devoluciones registradas: no se puede revertir esta migración sin perder esos datos.'
            );
        }

        Schema::table('package_histories', function (Blueprint $table) {
            $table->enum('status', self::BASE_STATUSES)->change();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['return_reason', 'return_requested_at', 'returned_at']);

            $table->enum('cod_status', ['pendiente', 'liquidado'])
                ->nullable()
                ->change();

            $table->enum('current_status', self::BASE_STATUSES)
                ->default('RECIBIDO_AGENCIA')
                ->change();
        });
    }
};
