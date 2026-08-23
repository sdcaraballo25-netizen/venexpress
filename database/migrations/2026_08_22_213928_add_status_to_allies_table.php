<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->enum('status', [
                'PENDIENTE',
                'ACTIVO',
                'RECHAZADO',
                'SUSPENDIDO',
            ])
            ->default('PENDIENTE')
            ->after('commission_percentage')
            ->index();
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};