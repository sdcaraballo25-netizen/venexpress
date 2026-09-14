<?php

namespace Tests\Feature\Services;

use App\Services\VenezuelaLocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase 1: unificación del catálogo geográfico.
 *
 * Antes existían dos fuentes de estados/ciudades independientes
 * (config/venezuela.php y database/data/venezuela.json), con al
 * menos una inconsistencia real entre ambas ("Vargas" vs "La
 * Guaira" para el mismo estado). Este test fija que
 * VenezuelaLocationService (leyendo database/data/venezuela.json)
 * es ahora la única fuente oficial, con el nombre correcto.
 */
class VenezuelaLocationServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_venezuela_source_no_longer_exists(): void
    {
        $this->assertFalse(
            file_exists(config_path('venezuela.php')),
            'config/venezuela.php debía retirarse tras migrar sus consumidores '
            . 'a VenezuelaLocationService.'
        );

        $this->assertNull(
            config('venezuela.states'),
            'Ya no debe quedar ninguna configuración "venezuela" cargada.'
        );
    }

    public function test_catalog_exposes_the_official_state_name(): void
    {
        $service = new VenezuelaLocationService();

        $this->assertContains('La Guaira', $service->states());
        $this->assertNotContains('Vargas', $service->states());
    }

    public function test_catalog_returns_cities_for_a_known_state(): void
    {
        $service = new VenezuelaLocationService();

        $cities = $service->citiesByState('Carabobo');

        $this->assertNotEmpty($cities);
        $this->assertContains('Valencia', $cities);
    }

    public function test_catalog_returns_empty_list_for_unknown_state(): void
    {
        $service = new VenezuelaLocationService();

        $this->assertSame([], $service->citiesByState('Estado Que No Existe'));
    }
}
