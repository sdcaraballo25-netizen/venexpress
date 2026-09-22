<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Cotizar" y "Sucursales" en el menú de Cliente reutilizan las
 * páginas públicas (PriceCalculator/OfficeLocator) tal cual, pero
 * antes las renderizaban con layouts.public (navbar/footer de
 * marketing), sacando al cliente del panel. Ahora, para un usuario
 * autenticado, se renderizan con el layout de su propio rol — ver
 * App\Livewire\Concerns\ResolvesLayoutForViewer.
 */
class ClientMenuStaysInsideDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createClientUser(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
            'account_verified_at' => now(),
        ]);
    }

    public function test_a_logged_in_client_sees_the_calculator_inside_the_client_layout(): void
    {
        $user = $this->createClientUser();

        $response = $this->actingAs($user)->get(route('public.calculator'));

        $response->assertOk();
        // Presente en el sidebar de layouts.client, ausente del layout público.
        $response->assertSee('Mis pedidos');
        $response->assertSee('Cerrar sesión');
    }

    public function test_a_logged_in_client_sees_office_locator_inside_the_client_layout(): void
    {
        $user = $this->createClientUser();

        $response = $this->actingAs($user)->get(route('public.offices'));

        $response->assertOk();
        $response->assertSee('Mis pedidos');
        $response->assertSee('Cerrar sesión');
    }

    public function test_a_guest_still_sees_the_calculator_in_the_public_layout(): void
    {
        $response = $this->get(route('public.calculator'));

        $response->assertOk();
        $response->assertDontSee('Mis pedidos');
    }
}
