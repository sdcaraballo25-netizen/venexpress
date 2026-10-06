<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 — entregas fallidas y entrega a un tercero autorizado.
     *
     * - delivery_attempts: intentos de entrega a domicilio fallidos;
     *   failed_delivery_*: el último motivo, sus notas y cuándo fue.
     * - received_by_third_party: lo recibió alguien distinto del
     *   destinatario, autorizado por él; third_party_id_photo_path es la
     *   foto de la cédula de esa persona y recipient_id_copy_path la de
     *   la copia de la cédula del destinatario (ambas en el disco
     *   privado documents). receiver_name/receiver_id_doc ya guardan
     *   quién lo recibió.
     *
     * Cambios aditivos: columnas nuevas con default o nullable.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedTinyInteger('delivery_attempts')->default(0);
            $table->string('failed_delivery_reason', 40)->nullable();
            $table->text('failed_delivery_notes')->nullable();
            $table->timestamp('failed_delivery_at')->nullable();

            $table->boolean('received_by_third_party')->default(false);
            $table->string('third_party_id_photo_path')->nullable();
            $table->string('recipient_id_copy_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_attempts',
                'failed_delivery_reason',
                'failed_delivery_notes',
                'failed_delivery_at',
                'received_by_third_party',
                'third_party_id_photo_path',
                'recipient_id_copy_path',
            ]);
        });
    }
};
