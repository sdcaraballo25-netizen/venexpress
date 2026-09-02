<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->timestamp('delivery_started_at')
                ->nullable()
                ->after('delivery_status');

            $table->index('delivery_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['delivery_started_at']);
            $table->dropColumn('delivery_started_at');
        });
    }
};
