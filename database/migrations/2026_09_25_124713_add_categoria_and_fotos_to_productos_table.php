<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * categoria: filtro fijo del catálogo (ver Producto::CATEGORIAS),
     * igual de simple que los STATUS_* de otros modelos. fotos: galería
     * adicional del producto; foto_path se mantiene como portada.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('categoria')->nullable()->after('descripcion');
            $table->json('fotos')->nullable()->after('foto_path');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['categoria', 'fotos']);
        });
    }
};
