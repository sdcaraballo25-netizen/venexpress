<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GuestLandingTest extends DuskTestCase
{
    // DatabaseTruncation (no DatabaseMigrations): no depende de que
    // cada down() de las migraciones funcione en SQLite al revertir.
    use DatabaseTruncation;

    public function test_the_landing_page_loads_and_the_hero_carousel_advances(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Venexpress')
                ->assertSee('Conectamos')

                // El carrusel del hero es JS puro (sin Alpine): si el
                // script de abajo de welcome.blade.php no corrió, el
                // primer punto nunca deja de estar activo.
                ->waitUntilMissing('.hero-dot[data-hero-dot="0"].is-active', 8)
                ->assertVisible('#hero-slide-1');
        });
    }
}
