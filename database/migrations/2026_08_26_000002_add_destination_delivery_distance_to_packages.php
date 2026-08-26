<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('origin_state')->nullable()->after('origin_city');
            $table->string('destination_state')->nullable()->after('destination_city');
            $table->unsignedInteger('distance_km')->default(0)->after('destination_state');

            $table->boolean('requires_delivery')->default(false)->after('distance_km');
            $table->text('delivery_address')->nullable()->after('requires_delivery');
            $table->string('delivery_sector')->nullable()->after('delivery_address');
            $table->text('delivery_reference')->nullable()->after('delivery_sector');
            $table->decimal('delivery_fee_usd', 12, 2)->default(0)->after('delivery_reference');

            $table->index(['destination_state', 'destination_city']);
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['destination_state', 'destination_city']);
            $table->dropColumn([
                'origin_state',
                'destination_state',
                'distance_km',
                'requires_delivery',
                'delivery_address',
                'delivery_sector',
                'delivery_reference',
                'delivery_fee_usd',
            ]);
        });
    }
};
