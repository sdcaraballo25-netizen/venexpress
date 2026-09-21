<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\Marketplace;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

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

    public function test_the_storefront_renders_and_lists_active_products(): void
    {
        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Producto Visible']);

        $this->get(route('public.marketplace'))
            ->assertOk()
            ->assertSee('Producto Visible');
    }

    public function test_products_with_no_stock_are_not_listed(): void
    {
        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Producto Agotado', 'stock' => 0]);

        Livewire::test(Marketplace::class)
            ->assertDontSee('Producto Agotado');
    }

    public function test_inactive_products_are_not_listed(): void
    {
        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Producto Inactivo', 'activo' => false]);

        Livewire::test(Marketplace::class)
            ->assertDontSee('Producto Inactivo');
    }

    public function test_products_from_a_non_active_emprendedor_are_not_listed(): void
    {
        $emprendedor = $this->createEmprendedor(['status' => Emprendedor::STATUS_SUSPENDED]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto De Suspendido']);

        Livewire::test(Marketplace::class)
            ->assertDontSee('Producto De Suspendido');
    }

    public function test_a_guest_can_place_a_pedido_for_a_product(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::test(Marketplace::class)
            ->call('pedirProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-99999999')
            ->set('cliente_telefono', '0414-9999999')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('cantidad', '2')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('cliente_id_doc', 'V-99999999')->first();

        $this->assertNotNull($pedido);
        $this->assertSame(2, $pedido->cantidad);
        $this->assertSame('30.00', (string) $pedido->precio_total_usd);
        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
    }

    public function test_a_pedido_cannot_request_more_units_than_available_stock(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 2]);

        Livewire::test(Marketplace::class)
            ->call('pedirProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-11122233')
            ->set('cliente_telefono', '0414-1112223')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('cantidad', '5')
            ->call('confirmarPedido')
            ->assertHasErrors(['cantidad']);

        $this->assertDatabaseMissing('pedidos', [
            'cliente_id_doc' => 'V-11122233',
        ]);
    }
}
