<?php

namespace App\Support;

/**
 * Aritmética decimal exacta para cálculos financieros.
 *
 * Hallazgo de auditoría: TariffService (y por extensión cualquier
 * cadena tarifa -> comisión -> liquidación) operaba con float nativo
 * de PHP. Los floats no pueden representar exactamente casi ningún
 * decimal (0.1, 0.2, etc. son aproximaciones binarias), y aunque cada
 * paso individual se redondeaba con round(), en una cadena larga de
 * sumas y multiplicaciones esos errores de representación se pueden
 * acumular y producir un total que no cuadra exactamente con la suma
 * manual esperada, especialmente al mezclar tasas USD->VES con 6
 * decimales que cambian a diario.
 *
 * Esta clase envuelve la extensión bcmath (aritmética decimal en
 * strings, sin binario de por medio) para que toda la aritmética
 * intermedia sea exacta. Los valores de entrada y salida siguen
 * siendo float/string normales -para no romper los casts
 * `decimal:2` de Eloquent ni las firmas de métodos existentes-, pero
 * el cálculo interno nunca pasa por una operación float.
 */
final class Money
{
    /**
     * Precisión interna usada mientras se encadenan operaciones,
     * antes del redondeo final a 2 (o las decimales que corresponda).
     * Suficientemente alta para no perder información en ningún
     * cálculo de este sistema (porcentajes, tasas BCV con 6
     * decimales, pesos volumétricos con 3 decimales, etc.).
     */
    private const INTERNAL_SCALE = 8;

    public static function add(float|string $a, float|string $b): string
    {
        return bcadd(self::str($a), self::str($b), self::INTERNAL_SCALE);
    }

    public static function sub(float|string $a, float|string $b): string
    {
        return bcsub(self::str($a), self::str($b), self::INTERNAL_SCALE);
    }

    public static function mul(float|string $a, float|string $b): string
    {
        return bcmul(self::str($a), self::str($b), self::INTERNAL_SCALE);
    }

    public static function div(float|string $a, float|string $b): string
    {
        if (bccomp(self::str($b), '0', self::INTERNAL_SCALE) === 0) {
            throw new \DivisionByZeroError(
                'División entre cero en un cálculo monetario.'
            );
        }

        return bcdiv(self::str($a), self::str($b), self::INTERNAL_SCALE);
    }

    /**
     * Suma una lista de valores en una sola pasada (evita anidar
     * add(add(add(...))) en cadenas largas).
     */
    public static function sum(array $values): string
    {
        $total = '0';

        foreach ($values as $value) {
            $total = self::add($total, $value);
        }

        return $total;
    }

    /**
     * Redondeo decimal exacto (half-up), sin pasar por floats en
     * ningún momento del cálculo. Devuelve float porque es lo que
     * consumen los casts `decimal:2` de Eloquent y el resto del
     * sistema, pero el propio redondeo ocurre en bcmath.
     */
    public static function round(float|string $value, int $precision = 2): float
    {
        $value = self::str($value);

        // Trico estándar de bcmath para redondeo half-up: sumar medio
        // "último dígito" y truncar a la precisión deseada usando el
        // parámetro de escala de bcadd (que trunca, no redondea).
        $halfUnit = '0.' . str_repeat('0', $precision) . '5';

        $rounded = bccomp($value, '0', self::INTERNAL_SCALE) >= 0
            ? bcadd($value, $halfUnit, $precision)
            : bcsub($value, $halfUnit, $precision);

        return (float) $rounded;
    }

    private static function str(float|string $value): string
    {
        if (is_string($value)) {
            // Los atributos con cast `decimal:N` de Eloquent ya
            // llegan como string ("2.00"); los dejamos pasar tal cual.
            return trim($value) === '' ? '0' : $value;
        }

        // number_format evita que PHP convierta floats muy pequeños
        // o muy grandes a notación científica (p. ej. 1.0E-5), que
        // bcmath no puede interpretar.
        return number_format($value, self::INTERNAL_SCALE, '.', '');
    }
}
