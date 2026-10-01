<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cancelación de pedidos del marketplace (ver
     * PedidoService::cancelar()). El estado CANCELADO ya existía en
     * Pedido pero nada lo usaba; status es string, así que solo se
     * agregan quién canceló, cuándo y por qué.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cancelado_por')->nullable()->after('status');
            $table->string('motivo_cancelacion', 500)->nullable()->after('cancelado_por');
            $table->timestamp('cancelado_at')->nullable()->after('motivo_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cancelado_por', 'motivo_cancelacion', 'cancelado_at']);
        });
    }
};
