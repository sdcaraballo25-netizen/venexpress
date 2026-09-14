<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            /**
             * Método de pago con el que el destinatario canceló el
             * cobro contra entrega (COD) al repartidor. Se exige al
             * completar la entrega de un paquete is_cod=true — antes,
             * completeDelivery() marcaba el COD como cobrado
             * automáticamente sin ningún registro de cómo se pagó.
             */
            $table->string('cod_payment_method')->nullable()->after('cod_collected_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('cod_payment_method');
        });
    }
};
