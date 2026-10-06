<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Repartidores y agencias aliadas que ya estaban operando (status
     * ACTIVO, aprobados por un admin) pero quedaron con la verificación
     * documental en PENDIENTE: desde la Fase 1 solo opera quien está
     * VERIFICADO y ACTIVO, así que se les cortaría el trabajo de golpe.
     * Se aprueban todos de una vez, como se acordó.
     *
     * No toca a quien está EN_REVISION, RECHAZADO o con status distinto
     * de ACTIVO: esos siguen esperando la revisión normal.
     */
    public function up(): void
    {
        foreach (['drivers', 'allies'] as $table) {
            DB::table($table)
                ->where('status', 'ACTIVO')
                ->where('verification_status', 'PENDIENTE')
                ->update([
                    'verification_status' => 'VERIFICADO',
                    'verification_reviewed_at' => now(),
                ]);
        }
    }

    /**
     * Sin reversa: no hay forma de distinguir a los aprobados aquí de
     * los que un admin aprobó a mano después.
     */
    public function down(): void
    {
        //
    }
};
