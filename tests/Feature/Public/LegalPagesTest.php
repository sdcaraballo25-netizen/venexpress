<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    public function test_terms_and_conditions_page_renders(): void
    {
        $this->get(route('public.terms'))
            ->assertOk()
            ->assertSee('Términos y Condiciones');
    }

    public function test_privacy_policy_page_renders(): void
    {
        $this->get(route('public.privacy'))
            ->assertOk()
            ->assertSee('Política de Privacidad');
    }

    public function test_landing_page_links_to_both_legal_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('public.terms'), false)
            ->assertSee(route('public.privacy'), false);
    }
}
