<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Emprendedor no tenía ningún dato/documento de verificación de
     * identidad todavía (document_id ya guarda el RIF como texto,
     * pero no había foto de respaldo). logo_path/cover_photo_path NO
     * son parte de esto: son imágenes públicas de marketing del
     * Marketplace (Emprendedor/Perfil.php), no evidencia de
     * verificación.
     */
    public function up(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->string('cedula')->nullable()->after('document_id');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');

            $table->string('cedula_front_photo_path')->nullable()->after('state');
            $table->string('cedula_back_photo_path')->nullable()->after('cedula_front_photo_path');
            $table->string('rif_document_path')->nullable()->after('cedula_back_photo_path');
            $table->string('product_or_workspace_photo_path')->nullable()->after('rif_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('emprendedores', function (Blueprint $table) {
            $table->dropColumn([
                'cedula',
                'city',
                'state',
                'cedula_front_photo_path',
                'cedula_back_photo_path',
                'rif_document_path',
                'product_or_workspace_photo_path',
            ]);
        });
    }
};
