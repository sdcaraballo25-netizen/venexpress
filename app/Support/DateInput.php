<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Fechas que llegan del navegador en filtros (wire:model sobre
 * <input type="date">). Un valor manipulado o incompleto ("abc",
 * "2026-13-45") hacía fallar Carbon::parse() o la consulta SQL con un
 * error 500. Aquí solo se acepta AAAA-MM-DD válido; lo demás se trata
 * como "sin filtro".
 */
class DateInput
{
    public static function parse(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        [$y, $m, $d] = array_map('intval', explode('-', $value));

        if (! checkdate($m, $d, $y)) {
            return null;
        }

        return Carbon::createFromFormat('!Y-m-d', $value);
    }

    /**
     * El mismo valor si es una fecha válida, o '' si no.
     */
    public static function clean(?string $value): string
    {
        return self::parse($value)?->toDateString() ?? '';
    }
}
