<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\Marketplace;
use App\Models\Customer;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use App\Models\User;
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
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
            ->set('cantidad', '2')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('cliente_id_doc', 'V-99999999')->first();

        $this->assertNotNull($pedido);
        $this->assertSame(2, $pedido->cantidad);
        $this->assertSame('30.00', (string) $pedido->precio_total_usd);
        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
    }

    public function test_the_catalog_shows_a_products_average_rating(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['nombre' => 'Producto Calificado']);

        foreach ([5, 3] as $i => $estrellas) {
            $pedido = Pedido::create([
                'producto_id' => $producto->id,
                'emprendedor_id' => $emprendedor->id,
                'cantidad' => 1,
                'precio_unitario_usd' => 15.00,
                'precio_total_usd' => 15.00,
                'cliente_nombre' => 'Cliente ' . $i,
                'cliente_id_doc' => 'V-' . (1000 + $i),
                'cliente_telefono' => '0424-0000000',
                'destino_ciudad' => 'Valencia',
                'destino_estado' => 'Carabobo',
                'status' => Pedido::STATUS_CONFIRMADO,
                'chat_token' => 'token-rating-' . $i,
            ]);

            Resena::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $producto->id,
                'estrellas' => $estrellas,
            ]);
        }

        $this->get(route('public.marketplace'))
            ->assertOk()
            ->assertSee('4.0')
            ->assertSee('(2)');
    }

    public function test_clicking_a_product_shows_its_detail_without_opening_the_order_form(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, [
            'nombre' => 'Producto Detallado',
            'descripcion' => 'Una descripción de prueba.',
        ]);

        Livewire::test(Marketplace::class)
            ->call('verProducto', $producto->id)
            ->assertSee('Una descripción de prueba.')
            ->assertSet('selectedProductoId', null);
    }

    public function test_the_per_emprendedor_store_page_only_lists_that_emprendedors_products(): void
    {
        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Producto Propio']);

        $otroEmprendedor = $this->createEmprendedor();
        $this->createProducto($otroEmprendedor, ['nombre' => 'Producto Ajeno']);

        $this->get(route('public.marketplace.store', $emprendedor->id))
            ->assertOk()
            ->assertSee('Producto Propio')
            ->assertDontSee('Producto Ajeno')
            ->assertSee($emprendedor->business_name);
    }

    public function test_checkout_prefills_and_hides_name_id_and_phone_for_a_client_with_a_customer_record(): void
    {
        $client = User::factory()->create([
            'name' => 'Cliente Con Cuenta',
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'account_verified_at' => now(),
        ]);

        Customer::create([
            'id_doc' => 'V-12345678',
            'user_id' => $client->id,
            'name' => $client->name,
            'phone' => '0414-1234567',
            'email' => $client->email,
        ]);

        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::actingAs($client)
            ->test(Marketplace::class)
            ->assertSet('clienteAutenticadoConDatos', true)
            ->assertSet('cliente_nombre', 'Cliente Con Cuenta')
            ->assertSet('cliente_id_doc', 'V-12345678')
            ->assertSet('cliente_telefono', '0414-1234567')
            ->call('pedirProducto', $producto->id)
            ->assertDontSee('Cédula')
            ->assertSee('Cliente Con Cuenta')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('user_id', $client->id)->first();

        $this->assertNotNull($pedido);
        $this->assertSame('V-12345678', $pedido->cliente_id_doc);
    }

    public function test_a_pedido_is_linked_to_the_authenticated_client_so_they_can_find_it_later(): void
    {
        $client = User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'account_verified_at' => now(),
        ]);

        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::actingAs($client)
            ->test(Marketplace::class)
            ->call('pedirProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-55566677')
            ->set('cliente_telefono', '0414-5556667')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
            ->set('cantidad', '1')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('cliente_id_doc', 'V-55566677')->first();

        $this->assertNotNull($pedido);
        $this->assertSame($client->id, $pedido->user_id);
    }

    public function test_a_guest_pedido_has_no_user_linked(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::test(Marketplace::class)
            ->call('pedirProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-77788899')
            ->set('cliente_telefono', '0414-7778889')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
            ->set('cantidad', '1')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('cliente_id_doc', 'V-77788899')->first();

        $this->assertNotNull($pedido);
        $this->assertNull($pedido->user_id);
    }

    public function test_a_pedido_requires_a_delivery_address(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::test(Marketplace::class)
            ->call('pedirProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-44455566')
            ->set('cliente_telefono', '0414-4445556')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('cantidad', '1')
            ->call('confirmarPedido')
            ->assertHasErrors(['direccion_entrega']);

        $this->assertDatabaseMissing('pedidos', [
            'cliente_id_doc' => 'V-44455566',
        ]);
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
