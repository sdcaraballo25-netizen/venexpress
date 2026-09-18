<?php

namespace Tests\Feature;

use App\Models\CityDistance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El índice único original de city_distances solo cubría
 * (city_a, city_b), de antes de que existieran state_a/state_b.
 * setDistance()/between() ya comparan por las cuatro columnas, así
 * que dos ciudades homónimas en estados distintos deben poder
 * coexistir como filas separadas en vez de chocar contra el índice
 * viejo (ver migración 2026_09_18_000001).
 */
class CityDistanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_cities_with_the_same_name_in_different_states_are_stored_separately(): void
    {
        $first = CityDistance::setDistance(
            cityOne: 'San Fernando',
            stateOne: 'Apure',
            cityTwo: 'Caracas',
            stateTwo: 'Distrito Capital',
            distanceKm: 700,
        );

        $second = CityDistance::setDistance(
            cityOne: 'San Fernando',
            stateOne: 'Aragua',
            cityTwo: 'Caracas',
            stateTwo: 'Distrito Capital',
            distanceKm: 100,
        );

        $this->assertNotSame($first->id, $second->id);

        $this->assertSame(
            700,
            CityDistance::between('San Fernando', 'Apure', 'Caracas', 'Distrito Capital')->distance_km
        );

        $this->assertSame(
            100,
            CityDistance::between('San Fernando', 'Aragua', 'Caracas', 'Distrito Capital')->distance_km
        );
    }

    public function test_setting_the_same_city_state_pair_again_updates_instead_of_duplicating(): void
    {
        CityDistance::setDistance(
            cityOne: 'Maracay',
            stateOne: 'Aragua',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 50,
        );

        CityDistance::setDistance(
            cityOne: 'Maracay',
            stateOne: 'Aragua',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 55,
        );

        $this->assertSame(1, CityDistance::count());
        $this->assertSame(
            55,
            CityDistance::between('Maracay', 'Aragua', 'Valencia', 'Carabobo')->distance_km
        );
    }
}
