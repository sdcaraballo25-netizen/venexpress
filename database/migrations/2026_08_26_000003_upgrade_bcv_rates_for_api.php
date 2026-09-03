<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bcv_rates', function (Blueprint $table) {
            $table->dateTime('effective_at')->nullable()->after('effective_date');
            $table->string('source')->nullable()->after('effective_at');
            $table->string('api_updated_at')->nullable()->after('source');
        });

        // Las tasas anteriores se conservan y se les asigna como hora
        // de vigencia la hora de creación del registro.
        \Illuminate\Support\Facades\DB::table('bcv_rates')
            ->whereNull('effective_at')
            ->update([
                'effective_at' => \Illuminate\Support\Facades\DB::raw('created_at'),
            ]);

        // Ya no puede existir una sola tasa por día: el BCV puede cambiar
        // dos veces el mismo día.
        Schema::table('bcv_rates', function (Blueprint $table) {
            if (Schema::hasIndex('bcv_rates', 'bcv_rates_effective_date_unique')) {
                $table->dropUnique('bcv_rates_effective_date_unique');
            }

            if (! Schema::hasIndex('bcv_rates', 'bcv_rates_effective_at_index')) {
                $table->index('effective_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bcv_rates', function (Blueprint $table) {
            if (Schema::hasIndex('bcv_rates', 'bcv_rates_effective_at_index')) {
                $table->dropIndex(['effective_at']);
            }

            $columnsToDrop = array_filter(
                ['effective_at', 'source', 'api_updated_at'],
                fn (string $column) => Schema::hasColumn('bcv_rates', $column)
            );

            if ($columnsToDrop !== []) {
                $table->dropColumn($columnsToDrop);
            }

            if (! Schema::hasIndex('bcv_rates', 'bcv_rates_effective_date_unique')) {
                $table->unique('effective_date');
            }
        });
    }
};
