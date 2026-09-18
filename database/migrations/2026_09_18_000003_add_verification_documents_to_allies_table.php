<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->string('rif_document_path')->nullable()->after('storefront_photo_path');
            $table->string('mercantile_registry_document_path')->nullable()->after('rif_document_path');
            $table->string('owner_id_document_path')->nullable()->after('mercantile_registry_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropColumn([
                'rif_document_path',
                'mercantile_registry_document_path',
                'owner_id_document_path',
            ]);
        });
    }
};
