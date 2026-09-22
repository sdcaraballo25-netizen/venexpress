<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reseña de un producto del marketplace, una por pedido (no por
     * producto): un cliente que compró el mismo producto dos veces
     * puede reseñar cada compra por separado. Solo se puede dejar una
     * vez el pedido está CONFIRMADO (guía generada) — mismo criterio
     * que "reportar problema" en PedidoChat.
     */
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->unique()->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->unsignedTinyInteger('estrellas');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
