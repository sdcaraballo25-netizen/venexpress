<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * Igual que con volumetric_weight_kg / total_price_usd, estos
     * valores se guardan como snapshot del cálculo en el momento
     * de crear el paquete: si el admin cambia la tarifa después,
     * los paquetes ya emitidos no cambian de precio retroactivamente.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('is_fragile')->default(false)->after('package_type');
            $table->boolean('has_insurance')->default(false)->after('is_fragile');

            /**
             * Solo se usa (y se exige) cuando has_insurance = true.
             */
            $table->decimal('declared_value_usd', 12, 2)->nullable()->after('has_insurance');

            /**
             * Montos ya calculados y aplicados a este paquete
             * específico (snapshot), no recalculables después.
             */
            $table->decimal('fragile_surcharge_usd', 12, 2)->default(0)->after('bcv_rate_used');
            $table->decimal('insurance_price_usd', 12, 2)->default(0)->after('fragile_surcharge_usd');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'is_fragile',
                'has_insurance',
                'declared_value_usd',
                'fragile_surcharge_usd',
                'insurance_price_usd',
            ]);
        });
    }
};
