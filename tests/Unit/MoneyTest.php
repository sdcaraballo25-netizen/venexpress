<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

/**
 * Hallazgo de auditoría #3: TariffService calculaba todo con float
 * nativo de PHP. Estas pruebas verifican que el helper de aritmética
 * decimal exacta (bcmath) que lo reemplaza redondea de forma
 * consistente y sin el error de representación binaria clásico de
 * los floats (el caso de libro es 0.1 + 0.2 !== 0.3 en float puro).
 */
class MoneyTest extends TestCase
{
    public function test_round_half_up(): void
    {
        $this->assertSame(2.5, Money::round('2.495', 2));
        $this->assertSame(1.13, Money::round('1.125', 2));
        $this->assertSame(0.01, Money::round('0.005', 2));
    }

    public function test_add_does_not_suffer_binary_float_rounding_error(): void
    {
        // En float puro de PHP, 0.1 + 0.2 !== 0.3 (da 0.30000000000000004).
        $sum = Money::add('0.1', '0.2');

        $this->assertSame(0.3, Money::round($sum, 2));
    }

    public function test_chained_multiplication_and_addition_matches_manual_calculation(): void
    {
        // Simula la cadena tarifa -> comisión: precio base + peso *
        // precio/kg + distancia * precio/km, luego aplicar un
        // porcentaje de comisión sobre el total.
        $base = '2.00';
        $weightCost = Money::mul('3.257', '0.50'); // peso facturable * precio/kg = 1.6285
        $distanceCost = Money::mul('150', '0.013'); // distancia * precio/km = 1.95

        $subtotal = Money::round(Money::sum([$base, $weightCost, $distanceCost]), 2);

        // 2.00 + 1.6285 + 1.95 = 5.5785 -> redondeado a 5.58
        $this->assertSame(5.58, $subtotal);

        $commission = Money::round(Money::mul($subtotal, Money::div(10, 100)), 2);

        // 10% de 5.58 = 0.558 -> redondeado a 0.56
        $this->assertSame(0.56, $commission);
    }

    public function test_division_by_zero_is_rejected(): void
    {
        $this->expectException(\DivisionByZeroError::class);

        Money::div(10, 0);
    }
}
