<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Opcional: el cliente del marketplace nunca necesitó cuenta ni
     * correo (accede al chat por chat_token). Si lo deja, se usa para
     * notificarle por correo los cambios de estado del pedido
     * (PedidoService); si no, sigue funcionando igual que hoy, solo
     * sin esas notificaciones.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cliente_email')->nullable()->after('cliente_telefono');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('cliente_email');
        });
    }
};
