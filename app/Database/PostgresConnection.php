<?php

namespace App\Database;

use Illuminate\Database\PostgresConnection as BasePostgresConnection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\PostgresGrammar;

/**
 * Conexión PostgreSQL (despliegue en Render).
 *
 * El código busca con where(..., 'like', ...) dando por hecho que no
 * distingue mayúsculas, como en MySQL (collation *_ci). En PostgreSQL
 * `like` sí las distingue, así que aquí se compila como `ilike`.
 */
class PostgresConnection extends BasePostgresConnection
{
    protected function getDefaultQueryGrammar()
    {
        return new class($this) extends PostgresGrammar
        {
            protected function whereBasic(Builder $query, $where)
            {
                $operator = strtolower((string) $where['operator']);

                if ($operator === 'like' || $operator === 'not like') {
                    $where['operator'] = $operator === 'like' ? 'ilike' : 'not ilike';
                }

                return parent::whereBasic($query, $where);
            }
        };
    }
}
