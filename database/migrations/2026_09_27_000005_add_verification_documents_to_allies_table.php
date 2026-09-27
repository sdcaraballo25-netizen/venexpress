<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * owner_id_document_path (ya existente) se reutiliza como "cédula
     * frente" del responsable; rif_document_path (ya existente) como
     * "RIF"; storefront_photo_path (ya existente) como "foto de
     * fachada". Solo faltaba el reverso de la cédula.
     *
     * mercantile_registry_document_path se mantiene en la tabla por
     * compatibilidad con aliados ya registrados, pero no forma parte
     * de los documentos obligatorios del nuevo flujo de verificación.
     */
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->string('owner_id_back_document_path')->nullable()->after('owner_id_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn('owner_id_back_document_path');
        });
    }
};
