<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enlace de acceso al chat del pedido, sin necesidad de cuenta:
     * el cliente del marketplace no inicia sesión, así que no hay otra
     * forma de identificarlo en visitas futuras más que este token,
     * igual que el número de guía sirve para el rastreo público.
     */
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('chat_token')->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn('chat_token');
        });
    }
};
