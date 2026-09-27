<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `status` (PENDIENTE/ACTIVO/RECHAZADO/SUSPENDIDO) mezclaba hasta
     * ahora dos conceptos distintos: si el repartidor fue verificado
     * documentalmente y si su cuenta está operativa. Esta columna
     * separa la verificación de identidad; `status` sigue existiendo
     * sin cambios (ver comentario en EnsureAccountIsApproved) y
     * representa desde ahora solo el estado operativo.
     *
     * Regla para operar: verification_status === VERIFICADO
     * AND status === ACTIVO (ver Driver::canOperate()).
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
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

        /*
         * Backfill idempotente: todo registro que ya llegó a ACTIVO o
         * SUSPENDIDO tuvo que pasar por la aprobación manual de un
         * Admin (ver DriversApprovalManager::approve/suspend), así que
         * ya estaba verificado documentalmente. RECHAZADO y PENDIENTE
         * mantienen el mismo significado en la nueva columna.
         */
        DB::table('drivers')->where('status', 'ACTIVO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('drivers')->where('status', 'SUSPENDIDO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('drivers')->where('status', 'RECHAZADO')->update(['verification_status' => 'RECHAZADO']);
        // status = 'PENDIENTE' ya queda cubierto por el default('PENDIENTE').
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_rejection_reason',
                'verification_reviewed_at',
            ]);
        });
    }
};
