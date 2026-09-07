<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'ally_financial_transactions';

    private string $column = 'payment_order_id';

    private string $indexName =
        'ally_financial_transactions_payment_order_id_unique';

    public function up(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        if (! Schema::hasColumn($this->table, $this->column)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | No eliminar el índice de la clave foránea
        |--------------------------------------------------------------------------
        |
        | Laravel/MySQL puede haber creado:
        |
        | ally_financial_transactions_payment_order_id_foreign
        |
        | Ese índice debe conservarse porque la clave foránea lo necesita.
        |
        */

        if (DB::getDriverName() === 'mysql') {
            $existingUniqueIndex = DB::select("
                SHOW INDEX
                FROM `{$this->table}`
                WHERE Key_name = ?
                  AND Non_unique = 0
            ", [$this->indexName]);

            if (! empty($existingUniqueIndex)) {
                return;
            }
        } else {
            $existingIndexes = DB::select("
                SELECT name
                FROM sqlite_master
                WHERE type = 'index'
                  AND tbl_name = ?
                  AND name = ?
            ", [$this->table, $this->indexName]);

            if (! empty($existingIndexes)) {
                return;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Crear el índice único
        |--------------------------------------------------------------------------
        |
        | Se conserva intacto el índice de la clave foránea.
        | MySQL puede tener ambos índices sobre la misma columna.
        |
        */

        Schema::table($this->table, function (Blueprint $table) {
            $table->unique(
                $this->column,
                $this->indexName
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable($this->table)) {
            return;
        }

        if (! Schema::hasColumn($this->table, $this->column)) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Eliminar solamente el índice único creado por esta migración
        |--------------------------------------------------------------------------
        |
        | Nunca se elimina el índice:
        |
        | ally_financial_transactions_payment_order_id_foreign
        |
        */

        if (DB::getDriverName() === 'mysql') {
            $existingUniqueIndex = DB::select("
                SHOW INDEX
                FROM `{$this->table}`
                WHERE Key_name = ?
                  AND Non_unique = 0
            ", [$this->indexName]);

            if (! empty($existingUniqueIndex)) {
                DB::statement("
                    ALTER TABLE `{$this->table}`
                    DROP INDEX `{$this->indexName}`
                ");
            }

            return;
        }

        Schema::table($this->table, function (Blueprint $table) {
            try {
                $table->dropUnique($this->indexName);
            } catch (\Throwable $exception) {
                // El índice puede no existir.
            }
        });
    }
};
