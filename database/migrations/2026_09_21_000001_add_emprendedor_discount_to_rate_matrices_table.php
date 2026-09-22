<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Descuento único, definido por el admin, que se aplica al precio
     * de envío de cualquier paquete registrado por un Emprendedor
     * (módulo de marketplace). Es el mismo porcentaje para todos los
     * emprendedores por ahora, así que vive junto a la tarifa global
     * en vez de por-emprendedor.
     */
    public function up(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->decimal('emprendedor_discount_percentage', 5, 2)->default(0)->after('delivery_price_usd');
        });
    }

    public function down(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropColumn('emprendedor_discount_percentage');
        });
    }
};
