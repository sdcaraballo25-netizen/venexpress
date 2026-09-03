<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('city_distances', function (Blueprint $table) {
            $table->string('state_a', 120)
                ->nullable()
                ->after('city_a');

            $table->string('state_b', 120)
                ->nullable()
                ->after('city_b');

            $table->index(
                ['city_a', 'state_a', 'city_b', 'state_b'],
                'city_distances_location_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('city_distances', function (Blueprint $table) {
            $table->dropIndex(
                'city_distances_location_index'
            );

            $table->dropColumn([
                'state_a',
                'state_b',
            ]);
        });
    }
};
