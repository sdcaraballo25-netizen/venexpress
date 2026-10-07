<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Columnas enum en PostgreSQL (despliegue en Render).
 *
 * En PostgreSQL Laravel crea un enum como varchar + CHECK (col IN (...)),
 * y Schema::table(...)->change() no sabe modificar ese CHECK (genera SQL
 * inválido). Las migraciones que agregan o quitan valores de un enum usan
 * esto en PostgreSQL; en MySQL y SQLite siguen usando ->change().
 */
final class PostgresEnum
{
    public static function active(): bool
    {
        return DB::getDriverName() === 'pgsql';
    }

    /**
     * Reemplaza los valores permitidos (y opcionalmente el default).
     *
     * @param  array<int, string>  $values
     */
    public static function allow(string $table, string $column, array $values, ?string $default = null): void
    {
        self::dropCheck($table, $column);

        $pdo = DB::getPdo();
        $list = implode(', ', array_map(fn (string $value) => $pdo->quote($value), $values));

        DB::statement("ALTER TABLE \"{$table}\" ADD CONSTRAINT \"{$table}_{$column}_check\" CHECK (\"{$column}\" IN ({$list}))");

        if ($default !== null) {
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" SET DEFAULT ".$pdo->quote($default));
        }
    }

    /**
     * Quita el CHECK de la columna (pasa a ser un varchar libre).
     */
    public static function dropCheck(string $table, string $column): void
    {
        $constraints = DB::select(<<<'SQL'
            SELECT c.conname
            FROM pg_constraint c
            JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY (c.conkey)
            WHERE c.conrelid = to_regclass(?) AND c.contype = 'c' AND a.attname = ?
            SQL, [$table, $column]);

        foreach ($constraints as $constraint) {
            DB::statement("ALTER TABLE \"{$table}\" DROP CONSTRAINT \"{$constraint->conname}\"");
        }
    }
}
