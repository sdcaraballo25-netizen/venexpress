<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->string('storefront_photo_path')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn('storefront_photo_path');
        });
    }
};
