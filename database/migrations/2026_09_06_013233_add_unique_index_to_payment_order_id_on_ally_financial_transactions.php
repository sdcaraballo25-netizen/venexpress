<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'ally_financial_transactions';
        $column = 'payment_order_id';
        $indexName = 'ally_financial_transactions_payment_order_id_unique';

        if (! Schema::hasColumn($table, $column)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar índices anteriores solamente si existen
        |--------------------------------------------------------------------------
        */

        $indexes = DB::select("
            SHOW INDEX
            FROM `{$table}`
            WHERE Column_name = ?
        ", [$column]);

        foreach ($indexes as $index) {
            $existingIndexName = $index->Key_name;

            /*
            | No eliminar la clave primaria.
            | Tampoco eliminar el índice que vamos a conservar.
            */
            if (
                $existingIndexName !== 'PRIMARY' &&
                $existingIndexName !== $indexName
            ) {
                DB::statement("
                    ALTER TABLE `{$table}`
                    DROP INDEX `{$existingIndexName}`
                ");
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Crear índice único si todavía no existe
        |--------------------------------------------------------------------------
        */

        $uniqueIndexExists = DB::select("
            SHOW INDEX
            FROM `{$table}`
            WHERE Key_name = ?
              AND Non_unique = 0
        ", [$indexName]);

        if (empty($uniqueIndexExists)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->unique(
                    'payment_order_id',
                    $indexName
                );
            });
        }
    }

    public function down(): void
    {
        $table = 'ally_financial_transactions';
        $indexName = 'ally_financial_transactions_payment_order_id_unique';

        if (DB::getDriverName() === 'mysql') {
            $indexes = DB::select("
                SHOW INDEX
                FROM `{$table}`
                WHERE Key_name = ?
            ", [$indexName]);

            if (! empty($indexes)) {
                DB::statement("
                    ALTER TABLE `{$table}`
                    DROP INDEX `{$indexName}`
                ");
            }

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Compatibilidad con SQLite
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable($table)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                try {
                    $table->dropUnique($indexName);
                } catch (\Throwable $exception) {
                    // El índice puede no existir.
                }
            });
        }
    }
};
