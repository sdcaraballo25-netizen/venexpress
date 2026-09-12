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
             * Coordenadas de la dirección de entrega, obtenidas por
             * geocodificación (Google Geocoding API) a partir de
             * delivery_address + delivery_sector + destination_city.
             * Nullable porque se geocodifica bajo demanda (lazy), no
             * al crear la guía.
             */
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_reference');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            $table->timestamp('delivery_geocoded_at')->nullable()->after('delivery_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['delivery_latitude', 'delivery_longitude', 'delivery_geocoded_at']);
        });
    }
};
