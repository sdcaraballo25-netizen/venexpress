<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->string('origin_location')
                ->nullable()
                ->after('event_type');

            $table->string('destination_location')
                ->nullable()
                ->after('origin_location');
        });
    }

    public function down(): void
    {
        Schema::table('package_histories', function (Blueprint $table) {
            $table->dropColumn([
                'origin_location',
                'destination_location',
            ]);
        });
    }
};