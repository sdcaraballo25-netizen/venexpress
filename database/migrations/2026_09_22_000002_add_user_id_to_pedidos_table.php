<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El comprador del marketplace nunca necesitó cuenta (acceso al
     * chat por chat_token, igual que el rastreo público por guía). Si
     * SÍ estaba logueado como Cliente al hacer el pedido, lo enlazamos
     * aquí para que pueda volver a encontrarlo desde su panel (Mis
     * Compras) sin depender de guardar el enlace del chat. Nullable:
     * un comprador anónimo sigue funcionando exactamente igual que
     * antes.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('emprendedor_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
