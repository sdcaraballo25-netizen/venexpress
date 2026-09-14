<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\PriceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regresión de Fase 1: la calculadora pública de precio leía
 * config('venezuela.states') directamente en su Blade para los
 * selects de Estado/Ciudad. Esa fuente se retiró en favor de
 * VenezuelaLocationService; este test confirma que la página sigue
 * renderizando y que los selects siguen poblándose correctamente.
 *
 * No existía ningún test previo para este componente.
 */
class PriceCalculatorCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_calculator_renders_without_a_logged_in_user(): void
    {
        Livewire::test(PriceCalculator::class)
            ->assertOk();
    }

    public function test_states_list_comes_from_the_unified_catalog(): void
    {
        $component = Livewire::test(PriceCalculator::class);

        $states = $component->get('states');

        $this->assertContains('La Guaira', $states);
        $this->assertNotContains('Vargas', $states);
    }

    public function test_selecting_an_origin_state_populates_its_cities(): void
    {
        $component = Livewire::test(PriceCalculator::class)
            ->set('origin_state', 'Carabobo');

        $this->assertContains('Valencia', $component->get('originCities'));
        $component->assertSet('origin_city', '');
    }

    public function test_selecting_a_destination_state_populates_its_cities(): void
    {
        $component = Livewire::test(PriceCalculator::class)
            ->set('destination_state', 'Zulia');

        $this->assertContains('Maracaibo', $component->get('destinationCities'));
        $component->assertSet('destination_city', '');
    }
}
