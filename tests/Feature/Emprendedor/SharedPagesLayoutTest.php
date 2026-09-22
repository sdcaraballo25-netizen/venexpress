<?php

namespace Tests\Feature\Emprendedor;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class SharedPagesLayoutTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

    public function test_an_emprendedor_sees_their_own_layout_on_the_shared_recommendations_page(): void
    {
        $emprendedor = $this->createEmprendedor();

        $this->actingAs($emprendedor->user)
            ->get(route('recommendations.create'))
            ->assertOk()
            ->assertSee('Panel Emprendedor');
    }

    public function test_an_emprendedor_sees_their_own_layout_on_the_shared_profile_page(): void
    {
        $emprendedor = $this->createEmprendedor();

        $this->actingAs($emprendedor->user)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Panel Emprendedor');
    }
}
