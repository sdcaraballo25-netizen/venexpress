<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La landing ofrece franquicias con un slide propio en el carrusel. No
 * hay postulación pública: el botón abre un correo a la empresa, y un
 * Admin crea al Franquiciado desde Gestión de Usuarios.
 */
class LandingFranchiseSlideTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_shows_the_franchise_slide_with_a_contact_button(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="hero-slide-4"', false)
            ->assertSee('data-hero-dot="4"', false)
            ->assertSee('Lleva ViaYaa')
            ->assertSee('images/hero-franquicia.png', false)
            ->assertSee('mailto:viayaenvios@gmail.com', false)
            ->assertSee('Contáctanos para más información')
            ->assertDontSee('register?role=franquiciado', false);
    }

    public function test_landing_uses_the_viayaa_brand(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('images/viayaa-logo.png', false)
            ->assertDontSee('Venexpress')
            ->assertDontSee('VenExpress');
    }
}
