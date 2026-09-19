<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique()->after('id');
        });

        DB::table('drivers')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($drivers) {
                foreach ($drivers as $driver) {
                    DB::table('drivers')
                        ->where('id', $driver->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
