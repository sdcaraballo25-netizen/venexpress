<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adjunto opcional en un mensaje del chat de pedido — pensado para
     * que el cliente mande la captura del comprobante de pago sin
     * tener que describirlo por texto. archivo_nombre guarda el
     * nombre original para mostrarlo/descargarlo cuando no es una
     * imagen (ej. un PDF).
     */
    public function up(): void
    {
        Schema::table('mensajes_pedido', function (Blueprint $table) {
            $table->string('archivo_path')->nullable()->after('texto');
            $table->string('archivo_nombre')->nullable()->after('archivo_path');
        });
    }

    public function down(): void
    {
        Schema::table('mensajes_pedido', function (Blueprint $table) {
            $table->dropColumn(['archivo_path', 'archivo_nombre']);
        });
    }
};
