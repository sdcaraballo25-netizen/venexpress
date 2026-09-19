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
        Schema::table('allies', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique()->after('id');
        });

        DB::table('allies')
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($allies) {
                foreach ($allies as $ally) {
                    DB::table('allies')
                        ->where('id', $ally->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn('public_id');
        });
    }
};
