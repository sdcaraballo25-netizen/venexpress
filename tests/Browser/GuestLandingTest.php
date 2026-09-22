<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GuestLandingTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_the_landing_page_loads_and_the_hero_carousel_advances(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('Venexpress')
                ->assertSee('Rastrea siempre.')

                // El carrusel del hero es JS puro (sin Alpine): si el
                // script de abajo de welcome.blade.php no corrió, el
                // segundo slide nunca aparece.
                ->waitUntilMissing('.hero-dot[data-hero-dot="0"].w-6', 8)
                ->assertVisible('#hero-slide-1');
        });
    }
}
