<?php

namespace Tests\Feature\Public;

use App\Models\Emprendedor;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

/**
 * Fase 2 (corrección puntual): los "productos destacados" de la
 * landing (routes/web.php::home) usaban solamente
 * Emprendedor::STATUS_ACTIVE, sin exigir verification_status ===
 * VERIFICADO — inconsistente con la regla que ya aplica
 * App\Livewire\Public\Marketplace::render(). Un emprendedor activo
 * pero no verificado podía aparecer en la home aunque ya estuviera
 * bloqueado del Marketplace real.
 */
class LandingFeaturedProductsTest extends TestCase
{
    use CreatesTestEmprendedores;
    use RefreshDatabase;

    private function createProducto(Emprendedor $emprendedor, array $overrides = []): Producto
    {
        return Producto::create(array_merge([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ], $overrides));
    }

    public function test_a_product_from_a_verified_and_active_emprendedor_appears_in_featured_products(): void
    {
        $emprendedor = $this->createEmprendedor([
            'status' => Emprendedor::STATUS_ACTIVE,
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
        ]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto Destacado Verificado']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Producto Destacado Verificado');
    }

    public function test_a_product_from_an_active_but_unverified_emprendedor_does_not_appear_in_featured_products(): void
    {
        $emprendedor = $this->createEmprendedor([
            'status' => Emprendedor::STATUS_ACTIVE,
            'verification_status' => Emprendedor::VERIFICATION_PENDING,
        ]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto De No Verificado']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Producto De No Verificado');
    }

    public function test_a_product_from_a_verified_but_suspended_emprendedor_does_not_appear_in_featured_products(): void
    {
        $emprendedor = $this->createEmprendedor([
            'status' => Emprendedor::STATUS_SUSPENDED,
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
        ]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto De Suspendido']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Producto De Suspendido');
    }
}
