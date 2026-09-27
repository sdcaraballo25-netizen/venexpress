<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingTrackingAndStripTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_has_no_duplicated_tracking_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="rastreo"', false)
            ->assertDontSee('href="#rastreo"', false)
            ->assertDontSee('¿Dónde está tu envío?')
            ->assertSee('href="' . route('tracking.index') . '"', false);
    }

    public function test_dedicated_tracking_page_still_loads(): void
    {
        $this->get(route('tracking.index'))->assertOk();
    }

    public function test_landing_renders_carousel_with_arrows_and_track(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="hero-track"', false)
            ->assertSee('data-hero-arrow="-1"', false)
            ->assertSee('data-hero-arrow="1"', false)
            ->assertSee('id="hero-slide-3"', false);
    }

    public function test_public_navbars_show_register_like_the_landing(): void
    {
        $urls = [
            '/',
            route('public.calculator'),
            route('tracking.index'),
            route('public.offices'),
            route('tracking.show', ['guia' => 'VEN-NO-EXISTE']),
        ];

        foreach ($urls as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('href="' . route('register') . '"', false)
                ->assertSee('Regístrate')
                ->assertSee('href="' . route('login') . '"', false);
        }
    }

    public function test_shared_navbar_shows_tienda_for_guests(): void
    {
        $tiendaLink = preg_quote(route('public.marketplace'), '/');

        foreach ([
            route('public.calculator'),
            route('public.offices'),
            route('public.help'),
            route('tracking.index'),
            route('tracking.show', ['guia' => 'VEN-NO-EXISTE']),
        ] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression('/href="' . $tiendaLink . '"\s+class="pnav-link "/', $html, $url);
            $this->assertDoesNotMatchRegularExpression('/href="' . $tiendaLink . '"\s+class="pnav-link is-active"/', $html, $url);
        }
    }

    /**
     * /tienda dejó de usar el navbar público compartido (layouts.public +
     * x-public-navbar): ahora tiene su propio layout (layouts.marketplace)
     * con un header/nav propio, independiente del navbar general — ver
     * Marketplace::render() y ResolvesLayoutForViewer.
     */
    public function test_marketplace_page_uses_its_own_dedicated_header_not_the_shared_navbar(): void
    {
        $html = $this->get(route('public.marketplace'))->assertOk()->getContent();

        $this->assertStringNotContainsString('pnav-link', $html);
        $this->assertStringNotContainsString('pnav-menu-link', $html);
        $this->assertStringContainsString('aria-label="Ver carrito"', $html);
        $this->assertStringContainsString('Buscar productos, marcas y más...', $html);
    }

    public function test_shared_navbar_login_button_uses_the_landing_yellow_and_scroll_hide(): void
    {
        $html = $this->get(route('public.calculator'))->assertOk()->getContent();

        $this->assertStringContainsString('background: #F7FF00;', $html);
        $this->assertStringContainsString('background: #DEE600;', $html);
        $this->assertStringContainsString('nav-hidden', $html);
    }

    public function test_shared_navbar_always_leaves_a_way_to_reach_the_search(): void
    {
        $html = $this->get(route('public.calculator'))->assertOk()->getContent();

        // El buscador solo se muestra en el navbar con ancho suficiente (>=1240px);
        // por debajo el ☰ debe estar disponible para llegar a él dentro del menú.
        $this->assertStringContainsString('min-width: 176px;', $html);
        $this->assertMatchesRegularExpression('/@media \(max-width: 1239px\)\s*\{\s*\.pnav-search\s*\{\s*display: none;\s*\}\s*\.pnav-burger\s*\{\s*display: inline-flex;/', $html);
        $this->assertMatchesRegularExpression('/@media \(min-width: 1240px\)\s*\{\s*\.pnav-menu,/', $html);
        $this->assertStringContainsString('id="pnav-menu"', $html);
        $this->assertStringContainsString('name="guia"', $html);
    }

    public function test_black_strip_only_lives_in_the_landing(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="utility-strip"', false)
            ->assertSee('Cobertura nacional');

        foreach (['public.calculator', 'public.offices', 'public.help'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertDontSee('utility-strip', false)
                ->assertDontSee('Cobertura nacional');
        }
    }
}
