<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('license_photo_path')->nullable()->after('vehicle_type');
            $table->string('id_photo_path')->nullable()->after('license_photo_path');
            $table->string('vehicle_registration_photo_path')->nullable()->after('id_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'license_photo_path',
                'id_photo_path',
                'vehicle_registration_photo_path',
            ]);
        });
    }
};
