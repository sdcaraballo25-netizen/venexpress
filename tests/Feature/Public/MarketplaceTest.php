<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\Marketplace;
use App\Models\Categoria;
use App\Models\Customer;
use App\Models\Emprendedor;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Resena;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class MarketplaceTest extends TestCase
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

    /**
     * Fase 2: la visibilidad pública ahora exige verification_status
     * === VERIFICADO, además de status === ACTIVO (Emprendedor::canOperate()).
     * Un emprendedor con status ACTIVO pero todavía no verificado
     * documentalmente no debe aparecer en el listado.
     */
    public function test_products_from_an_active_but_unverified_emprendedor_are_not_listed(): void
    {
        $emprendedor = $this->createEmprendedor(['verification_status' => Emprendedor::VERIFICATION_PENDING]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto De No Verificado']);

        Livewire::test(Marketplace::class)
            ->assertDontSee('Producto De No Verificado');
    }

    public function test_products_from_a_verified_and_active_emprendedor_are_listed(): void
    {
        $emprendedor = $this->createEmprendedor(['verification_status' => Emprendedor::VERIFICATION_VERIFIED]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto De Verificado']);

        Livewire::test(Marketplace::class)
            ->assertSee('Producto De Verificado');
    }

    /**
     * Mismo criterio que test_products_from_an_active_but_unverified_emprendedor_are_not_listed,
     * pero sobre el contador de productos por categoría (el tercer
     * filtro que dependía de Emprendedor::STATUS_ACTIVE en Marketplace.php).
     */
    public function test_category_product_count_excludes_unverified_emprendedor_products(): void
    {
        $categoria = Categoria::first();

        $emprendedor = $this->createEmprendedor(['verification_status' => Emprendedor::VERIFICATION_PENDING]);
        $this->createProducto($emprendedor, ['nombre' => 'De No Verificado', 'categoria_id' => $categoria->id]);

        $categorias = Livewire::test(Marketplace::class)->viewData('categorias');

        $this->assertSame(0, $categorias->firstWhere('id', $categoria->id)->productos_count);
    }

    public function test_a_guest_can_place_a_pedido_for_a_product(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['stock' => 5]);

        Livewire::test(Marketplace::class)
            ->call('comprarProducto', $producto->id, 2)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-99999999')
            ->set('cliente_telefono', '0414-9999999')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
            ->call('confirmarPedido')
            ->assertHasNoErrors();

        $pedido = Pedido::where('cliente_id_doc', 'V-99999999')->first();

        $this->assertNotNull($pedido);
        $this->assertSame(2, $pedido->items->first()->cantidad);
        $this->assertSame('30.00', (string) $pedido->precio_total_usd);
        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
    }

    public function test_products_can_be_searched_by_name_or_description(): void
    {
        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Camisa azul', 'descripcion' => 'Talla M, algodón']);
        $this->createProducto($emprendedor, ['nombre' => 'Zapatos deportivos', 'descripcion' => 'Ideales para correr']);

        Livewire::test(Marketplace::class)
            ->set('busqueda', 'camisa')
            ->assertSee('Camisa azul')
            ->assertDontSee('Zapatos deportivos');

        Livewire::test(Marketplace::class)
            ->set('busqueda', 'correr')
            ->assertSee('Zapatos deportivos')
            ->assertDontSee('Camisa azul');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $emprendedor = $this->createEmprendedor();
        $ropa = Categoria::first();
        $otraCategoria = Categoria::skip(1)->first();

        $this->createProducto($emprendedor, ['nombre' => 'Producto En Ropa', 'categoria_id' => $ropa->id]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto En Otra', 'categoria_id' => $otraCategoria->id]);

        Livewire::test(Marketplace::class)
            ->set('categoriaId', (string) $ropa->id)
            ->assertSee('Producto En Ropa')
            ->assertDontSee('Producto En Otra');
    }

    /**
     * El badge de cantidad del menú/sidebar de categorías (ver
     * Marketplace::render()) debe contar solo lo que el catálogo
     * realmente lista: activo, con stock y de un emprendedor activo.
     */
    public function test_category_product_count_only_includes_really_available_products(): void
    {
        $categoria = Categoria::first();

        $emprendedor = $this->createEmprendedor();
        $this->createProducto($emprendedor, ['nombre' => 'Disponible 1', 'categoria_id' => $categoria->id]);
        $this->createProducto($emprendedor, ['nombre' => 'Disponible 2', 'categoria_id' => $categoria->id]);
        $this->createProducto($emprendedor, ['nombre' => 'Sin Stock', 'categoria_id' => $categoria->id, 'stock' => 0]);
        $this->createProducto($emprendedor, ['nombre' => 'Inactivo', 'categoria_id' => $categoria->id, 'activo' => false]);

        $emprendedorSuspendido = $this->createEmprendedor(['status' => Emprendedor::STATUS_SUSPENDED]);
        $this->createProducto($emprendedorSuspendido, ['nombre' => 'De Suspendido', 'categoria_id' => $categoria->id]);

        $categorias = Livewire::test(Marketplace::class)->viewData('categorias');

        $this->assertSame(2, $categorias->firstWhere('id', $categoria->id)->productos_count);
    }

    public function test_the_catalog_shows_a_products_average_rating(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, ['nombre' => 'Producto Calificado']);

        foreach ([5, 3] as $i => $estrellas) {
            $pedido = Pedido::create([
                'emprendedor_id' => $emprendedor->id,
                'precio_total_usd' => 15.00,
                'cliente_nombre' => 'Cliente '.$i,
                'cliente_id_doc' => 'V-'.(1000 + $i),
                'cliente_telefono' => '0424-0000000',
                'destino_ciudad' => 'Valencia',
                'destino_estado' => 'Carabobo',
                'status' => Pedido::STATUS_CONFIRMADO,
                'chat_token' => 'token-rating-'.$i,
            ]);

            PedidoItem::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $producto->id,
                'cantidad' => 1,
                'precio_unitario_usd' => 15.00,
                'subtotal_usd' => 15.00,
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

    public function test_clicking_a_product_shows_its_detail_without_opening_the_checkout_form(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor, [
            'nombre' => 'Producto Detallado',
            'descripcion' => 'Una descripción de prueba.',
        ]);

        Livewire::test(Marketplace::class)
            ->call('verProducto', $producto->id)
            ->assertSee('Una descripción de prueba.')
            ->assertSet('showCheckout', false);
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

    public function test_the_store_page_shows_trust_information_about_the_emprendedor(): void
    {
        $emprendedor = $this->createEmprendedor([
            'descripcion' => 'Vendemos artesanías venezolanas hechas a mano.',
            'address' => 'Av. Bolívar, C.C. Los Próceres, local 12, Valencia',
        ]);
        $emprendedor->user->update(['phone' => '0414-1234567']);
        $this->createProducto($emprendedor);

        $this->get(route('public.marketplace.store', $emprendedor->id))
            ->assertOk()
            ->assertSee('Vendemos artesanías venezolanas hechas a mano.')
            ->assertSee('Av. Bolívar, C.C. Los Próceres, local 12, Valencia')
            ->assertSee('0414-1234567')
            ->assertSee($emprendedor->user->email);
    }

    public function test_the_per_emprendedor_store_page_404s_for_a_suspended_emprendedor(): void
    {
        $emprendedor = $this->createEmprendedor(['status' => Emprendedor::STATUS_SUSPENDED]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto Suspendido']);

        $this->get(route('public.marketplace.store', $emprendedor->id))
            ->assertNotFound();
    }

    /**
     * Fase 2: la tienda pública de un emprendedor no verificado (aunque
     * su status siga ACTIVO) no debe exponer sus productos.
     */
    public function test_the_per_emprendedor_store_page_404s_for_an_unverified_emprendedor(): void
    {
        $emprendedor = $this->createEmprendedor(['verification_status' => Emprendedor::VERIFICATION_IN_REVIEW]);
        $this->createProducto($emprendedor, ['nombre' => 'Producto En Revision']);

        $this->get(route('public.marketplace.store', $emprendedor->id))
            ->assertNotFound();
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
            ->call('comprarProducto', $producto->id)
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
            ->call('comprarProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-55566677')
            ->set('cliente_telefono', '0414-5556667')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
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
            ->call('comprarProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-77788899')
            ->set('cliente_telefono', '0414-7778889')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
            ->set('direccion_entrega', 'Av. Bolívar, casa 1')
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
            ->call('comprarProducto', $producto->id)
            ->set('cliente_nombre', 'Comprador de Prueba')
            ->set('cliente_id_doc', 'V-44455566')
            ->set('cliente_telefono', '0414-4445556')
            ->set('destino_estado', 'Carabobo')
            ->set('destino_ciudad', 'Valencia')
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

        $component = Livewire::test(Marketplace::class)
            ->call('comprarProducto', $producto->id, 5)
            ->assertSet('compraError', "Solo quedan 2 unidad(es) de \"{$producto->nombre}\".");

        $this->assertSame(false, $component->get('showCheckout'));

        $this->assertDatabaseMissing('pedidos', [
            'cliente_id_doc' => 'V-11122233',
        ]);
    }
}
