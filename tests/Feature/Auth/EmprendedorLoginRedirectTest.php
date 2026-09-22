<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class EmprendedorLoginRedirectTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

    public function test_an_emprendedor_is_redirected_to_their_own_dashboard_after_login(): void
    {
        $emprendedor = $this->createEmprendedor();

        Volt::test('pages.auth.login')
            ->set('form.email', $emprendedor->user->email)
            ->set('form.password', 'password')
            ->call('login')
            ->assertRedirect(route('emprendedor.dashboard', absolute: false));
    }

    public function test_home_route_name_points_an_emprendedor_to_their_dashboard(): void
    {
        $emprendedor = $this->createEmprendedor();

        $this->assertSame('emprendedor.dashboard', $emprendedor->user->homeRouteName());
    }
}
