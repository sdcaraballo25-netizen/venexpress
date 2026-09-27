<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mismo motivo que la migración equivalente de drivers/allies.
     */
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->enum('verification_status', [
                'PENDIENTE',
                'EN_REVISION',
                'VERIFICADO',
                'RECHAZADO',
            ])
                ->default('PENDIENTE')
                ->after('status')
                ->index();

            $table->text('verification_rejection_reason')
                ->nullable()
                ->after('verification_status');

            $table->timestamp('verification_reviewed_at')
                ->nullable()
                ->after('verification_rejection_reason');
        });

        DB::table('emprendedores')->where('status', 'ACTIVO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('emprendedores')->where('status', 'SUSPENDIDO')->update(['verification_status' => 'VERIFICADO']);
        DB::table('emprendedores')->where('status', 'RECHAZADO')->update(['verification_status' => 'RECHAZADO']);
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_rejection_reason',
                'verification_reviewed_at',
            ]);
        });
    }
};
