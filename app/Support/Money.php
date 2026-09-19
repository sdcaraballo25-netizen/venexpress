<?php

namespace App\Support;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

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
 * Esta clase usa brick/math (aritmética decimal de precisión
 * arbitraria en strings, sin binario de por medio) para que toda la
 * aritmética intermedia sea exacta. Los valores de entrada y salida
 * siguen siendo float/string normales -para no romper los casts
 * `decimal:2` de Eloquent ni las firmas de métodos existentes-, pero
 * el cálculo interno nunca pasa por una operación float.
 *
 * brick/math ya viene instalado como dependencia transitiva de
 * laravel/framework (no se agregó solo para esto), y elige
 * automáticamente la implementación más rápida disponible en el
 * servidor: GMP, luego bcmath, y si ninguna extensión está instalada,
 * una implementación 100% en PHP puro igual de exacta (más lenta,
 * pero nunca falla por falta de una extensión). Antes esta clase
 * llamaba directo a bcadd/bcmul/etc., así que en cualquier entorno sin
 * la extensión bcmath habilitada (algunos XAMPP/WAMP no la traen
 * activada por defecto) toda la cotización de tarifas quedaba rota.
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
        return self::truncate(self::big($a)->plus(self::big($b)));
    }

    public static function sub(float|string $a, float|string $b): string
    {
        return self::truncate(self::big($a)->minus(self::big($b)));
    }

    public static function mul(float|string $a, float|string $b): string
    {
        return self::truncate(self::big($a)->multipliedBy(self::big($b)));
    }

    public static function div(float|string $a, float|string $b): string
    {
        $divisor = self::big($b);

        if ($divisor->isZero()) {
            throw new \DivisionByZeroError(
                'División entre cero en un cálculo monetario.'
            );
        }

        return self::big($a)
            ->dividedBy($divisor, self::INTERNAL_SCALE, RoundingMode::Down)
            ->__toString();
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
     * Redondeo decimal exacto (half-up, es decir, "hacia afuera" del
     * cero en el punto medio), sin pasar por floats en ningún momento
     * del cálculo. Devuelve float porque es lo que consumen los casts
     * `decimal:2` de Eloquent y el resto del sistema, pero el propio
     * redondeo ocurre en aritmética decimal exacta.
     */
    public static function round(float|string $value, int $precision = 2): float
    {
        return (float) self::big($value)
            ->toScale($precision, RoundingMode::HalfUp)
            ->__toString();
    }

    /**
     * Trunca (no redondea) a INTERNAL_SCALE decimales, igual que
     * hacían bcadd/bcsub/bcmul con el parámetro de escala explícito:
     * son solo pasos intermedios de una cadena de cálculo, el
     * redondeo real ocurre al final con round().
     */
    private static function truncate(BigDecimal $value): string
    {
        return $value
            ->toScale(self::INTERNAL_SCALE, RoundingMode::Down)
            ->__toString();
    }

    private static function big(float|string $value): BigDecimal
    {
        return BigDecimal::of(self::str($value));
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
        // BigDecimal no puede interpretar.
        return number_format($value, self::INTERNAL_SCALE, '.', '');
    }
}
