<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campos y documentos que faltaban para la verificación de
     * identidad del repartidor. `id_photo_path` (ya existente) se
     * reutiliza como "cédula frente"; `license_photo_path` (ya
     * existente) se reutiliza como "licencia". `cedula_back_photo_path`
     * es el reverso que faltaba.
     *
     * vehicle_photo_path/plate_photo_path son fotos nuevas: NO deben
     * confundirse con vehicle_registration_photo_path (el carnet de
     * circulación, un documento distinto que ya existía).
     */
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('city')->nullable()->after('vehicle_type');
            $table->string('state')->nullable()->after('city');

            $table->string('cedula_back_photo_path')->nullable()->after('id_photo_path');
            $table->string('selfie_photo_path')->nullable()->after('cedula_back_photo_path');
            $table->string('vehicle_photo_path')->nullable()->after('vehicle_registration_photo_path');
            $table->string('plate_photo_path')->nullable()->after('vehicle_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn([
                'city',
                'state',
                'cedula_back_photo_path',
                'selfie_photo_path',
                'vehicle_photo_path',
                'plate_photo_path',
            ]);
        });
    }
};
