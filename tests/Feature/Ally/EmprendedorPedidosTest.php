<?php

namespace Tests\Feature\Ally;

use App\Livewire\Ally\EmprendedorPedidos;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\RateMatrix;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class EmprendedorPedidosTest extends TestCase
{
    use CreatesTestEmprendedores;
    use CreatesTestPackages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateMatrix::create([
            'base_price_usd' => 2.00,
            'price_per_kg_usd' => 0.50,
            'price_per_km_usd' => 0.01,
            'envelope_price_usd' => 1.50,
            'fragile_surcharge_usd' => 3.00,
            'insurance_percentage' => 2.00,
            'delivery_price_usd' => 4.00,
            'emprendedor_discount_percentage' => 10.00,
        ]);

        BcvRate::create([
            'rate' => 40.00,
            'effective_date' => now()->toDateString(),
            'effective_at' => now(),
            'source' => 'manual',
        ]);

        CityDistance::setDistance(
            cityOne: 'Caracas',
            stateOne: 'Distrito Capital',
            cityTwo: 'Valencia',
            stateTwo: 'Carabobo',
            distanceKm: 150,
        );
    }

    private function createPedidoPagado(): Pedido
    {
        // createEmprendedor() ya crea su agencia de retiro en Caracas
        // (createActivePickupAlly()), que es de donde CityDistance
        // resuelve la distancia hacia Valencia en este test.
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'direccion_entrega' => 'Av. Bolívar, casa 1',
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'subtotal_usd' => 15.00,
        ]);

        app(PedidoService::class)->marcarComoPagado($pedido);

        return $pedido->fresh();
    }

    public function test_ally_can_search_and_generate_a_guide_for_a_paid_pedido(): void
    {
        $pedido = $this->createPedidoPagado();
        $ally = $pedido->emprendedor->pickupAlly;

        Livewire::actingAs($ally->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->assertSet('searchError', null)
            ->set('physical_weight_kg', 3.0)
            ->set('is_fragile', true)
            ->call('generarGuia')
            ->assertSet('errorMessage', null);

        $pedido->refresh();

        $this->assertSame(Pedido::STATUS_CONFIRMADO, $pedido->status);
        $this->assertNotNull($pedido->package_id);

        $package = Package::find($pedido->package_id);
        $this->assertSame(3.0, (float) $package->physical_weight_kg);
        $this->assertTrue((bool) $package->is_fragile);
        $this->assertSame('Av. Bolívar, casa 1', $package->delivery_address);
    }

    public function test_generating_a_guide_populates_the_printable_receipt_fields(): void
    {
        $pedido = $this->createPedidoPagado();
        $ally = $pedido->emprendedor->pickupAlly;

        $component = Livewire::actingAs($ally->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->set('physical_weight_kg', 3.0)
            ->call('generarGuia')
            ->assertSet('errorMessage', null);

        $package = Package::find($pedido->fresh()->package_id);

        $component
            ->assertSet('createdPedidoId', $pedido->id)
            ->assertSet('createdPackageId', $package->id)
            ->assertSet('createdTrackingNumber', $package->tracking_number)
            ->assertSet('createdTotalUsd', (float) $package->total_price_usd)
            ->assertSet('createdTotalVes', (float) $package->total_price_ves);
    }

    public function test_submitting_only_some_dimensions_is_rejected_without_a_server_error(): void
    {
        $pedido = $this->createPedidoPagado();
        $ally = $pedido->emprendedor->pickupAlly;

        Livewire::actingAs($ally->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->set('physical_weight_kg', 3.0)
            ->set('length_cm', 10)
            ->set('width_cm', 10)
            // height_cm queda vacío a propósito.
            ->call('generarGuia')
            ->assertHasErrors(['height_cm']);

        $this->assertSame(Pedido::STATUS_PAGADO, $pedido->fresh()->status);
        $this->assertSame(0, Package::count());
    }

    public function test_searching_a_pedido_from_another_agency_is_rejected(): void
    {
        $pedido = $this->createPedidoPagado();
        $otraAgencia = $this->createAlly(['city' => 'Valencia', 'state' => 'Carabobo']);

        Livewire::actingAs($otraAgencia->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->assertSet('pedido', null)
            ->assertSet('searchError', 'Este pedido no pertenece a tu agencia.');
    }

    public function test_searching_a_pedido_that_is_still_pending_payment_is_rejected(): void
    {
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'direccion_entrega' => 'Av. Bolívar, casa 1',
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'subtotal_usd' => 15.00,
        ]);

        $ally = $emprendedor->pickupAlly;

        Livewire::actingAs($ally->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->assertSet('pedido', null)
            ->assertSet('searchError', 'Este pedido todavía no fue confirmado como pagado por el emprendedor.');
    }

    public function test_generating_a_guide_twice_for_the_same_pedido_is_rejected(): void
    {
        $pedido = $this->createPedidoPagado();
        $ally = $pedido->emprendedor->pickupAlly;

        app(PedidoService::class)->registrarGuia($pedido, ['physical_weight_kg' => 2.0], null, $ally);

        Livewire::actingAs($ally->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->assertSet('pedido', null)
            ->assertSet('searchError', 'Este pedido ya tiene una guía generada.');

        $this->assertSame(1, Package::count());
    }
}
