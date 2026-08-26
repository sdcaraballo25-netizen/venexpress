<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->decimal('delivery_price_usd', 12, 2)
                ->default(0)
                ->after('insurance_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('rate_matrices', function (Blueprint $table) {
            $table->dropColumn('delivery_price_usd');
        });
    }
};
