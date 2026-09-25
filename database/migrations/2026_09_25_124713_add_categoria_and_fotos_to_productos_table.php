<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * categoria_legacy / fotos_legacy: implementación simple (lista
     * fija + columna JSON) que quedó superada por el sistema
     * relacional ya mergeado en main (categoria_id -> categorias,
     * tabla producto_fotos). Se conserva sin exponerse en la UI y con
     * nombre "_legacy" para no chocar con las relaciones
     * Producto::categoria() / Producto::fotos() de ese sistema.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('categoria_legacy')->nullable()->after('descripcion');
            $table->json('fotos_legacy')->nullable()->after('foto_path');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['categoria_legacy', 'fotos_legacy']);
        });
    }
};
