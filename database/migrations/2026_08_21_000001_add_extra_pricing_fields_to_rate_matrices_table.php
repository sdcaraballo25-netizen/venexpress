<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Agrega los componentes de precio necesarios para:
     * - sobres (precio fijo, no usa peso/volumen)
     * - recargo por paquete frágil (monto fijo)
     * - seguro (porcentaje sobre el valor declarado del paquete)
     */
    public function up(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            /**
             * Precio fijo para envíos tipo "sobre". No se calcula
             * con peso ni volumen, pero sí sigue sumando distancia
             * (price_per_km_usd), igual que un paquete normal.
             */
            $table->decimal('envelope_price_usd', 12, 2)->default(0)->after('price_per_km_usd');

            /**
             * Monto fijo que se suma al subtotal cuando el envío
             * se marca como frágil (aplica a sobre y a paquete).
             */
            $table->decimal('fragile_surcharge_usd', 12, 2)->default(0)->after('envelope_price_usd');

            /**
             * Porcentaje aplicado sobre el valor declarado del
             * paquete cuando el cliente contrata seguro.
             *
             * Ejemplo: 3.50 significa 3.5%.
             */
            $table->decimal('insurance_percentage', 5, 2)->default(0)->after('fragile_surcharge_usd');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropColumn(['envelope_price_usd', 'fragile_surcharge_usd', 'insurance_percentage']);
        });
    }
};
