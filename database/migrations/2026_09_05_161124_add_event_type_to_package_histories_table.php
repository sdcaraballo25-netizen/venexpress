<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->string('event_type')
                ->default('MOVIMIENTO')
                ->after('status');

            $table->index([
                'package_id',
                'event_type',
                'created_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->dropIndex([
                'package_id',
                'event_type',
                'created_at',
            ]);

            $table->dropColumn('event_type');
        });
    }
};