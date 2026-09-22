<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El pedido siempre se marcó requires_delivery=true al generar la
     * guía (PedidoService), pero nunca se pidió una dirección real —
     * solo ciudad/estado. Sin esto, un repartidor no tiene a dónde
     * entregar. Nullable porque ya pueden existir pedidos previos sin
     * este dato (entorno de demo/desarrollo).
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('direccion_entrega')->nullable()->after('destino_estado');
            $table->string('referencia_entrega')->nullable()->after('direccion_entrega');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['direccion_entrega', 'referencia_entrega']);
        });
    }
};
