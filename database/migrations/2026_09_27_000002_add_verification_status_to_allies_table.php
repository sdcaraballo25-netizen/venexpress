<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mismo motivo que la migración equivalente de drivers: separar
     * la verificación de identidad del aliado (documentos) del estado
     * operativo de su agencia (`status`, sin cambios).
     *
     * No confundir con destination_verification_status: esa columna
     * es sobre si la agencia es punto final de entrega/retiro
     * (Regla 9-12), un concepto distinto de la verificación de
     * identidad del responsable/agencia que se agrega aquí.
     */
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->enum('verification_status', [
                'PENDIENTE',
                'EN_REVISION',
                'VERIFICADO',
                'RECHAZADO',
            ])
                ->default('PENDIENTE')
                ->after('bank_account_holder_id')
                ->index();

            $table->text('verification_rejection_reason')
                ->nullable()
                ->after('verification_status');

            $table->timestamp('verification_reviewed_at')
                ->nullable()
                ->after('verification_rejection_reason');
        });

        DB::table('allies')->where('status', 'ACTIVO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('allies')->where('status', 'SUSPENDIDO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('allies')->where('status', 'RECHAZADO')->update(['verification_status' => 'RECHAZADO']);
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_rejection_reason',
                'verification_reviewed_at',
            ]);
        });
    }
};
