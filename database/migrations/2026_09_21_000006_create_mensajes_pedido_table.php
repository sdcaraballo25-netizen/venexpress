<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chat entre el cliente (anónimo, sin cuenta) y el emprendedor sobre
     * un pedido puntual. autor es texto libre ('cliente'/'emprendedor'),
     * no un user_id: el cliente del marketplace no necesita cuenta,
     * igual que remitente/destinatario de una guía normal.
     */
    public function up(): void
    {
        Schema::create('mensajes_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->string('autor');
            $table->text('texto');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes_pedido');
    }
};
