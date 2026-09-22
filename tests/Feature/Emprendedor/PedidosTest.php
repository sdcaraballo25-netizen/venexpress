<?php

namespace Tests\Feature\Emprendedor;

use App\Livewire\Emprendedor\Pedidos;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RateMatrix;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class PedidosTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

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

    private function createPedido(int $emprendedorId, int $productoId, int $cantidad = 1): Pedido
    {
        return Pedido::create([
            'producto_id' => $productoId,
            'emprendedor_id' => $emprendedorId,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => 15.00,
            'precio_total_usd' => 15.00 * $cantidad,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
        ]);
    }

    public function test_confirming_a_pedido_marks_it_as_paid_and_reserves_stock_without_a_package_yet(): void
    {
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 2.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = $this->createPedido($emprendedor->id, $producto->id);

        Livewire::actingAs($emprendedor->user)
            ->test(Pedidos::class)
            ->call('confirmar', $pedido->id)
            ->assertSet('errorMessage', null);

        $pedido->refresh();

        $this->assertSame(Pedido::STATUS_PAGADO, $pedido->status);
        $this->assertNull($pedido->package_id);
        $this->assertSame(0, Package::count());

        $producto->refresh();
        $this->assertSame(4, $producto->stock);
    }

    public function test_confirming_a_pedido_with_insufficient_stock_shows_an_error_and_does_not_create_a_package(): void
    {
        $emprendedor = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto sin stock',
            'precio_usd' => 15.00,
            'peso_kg' => 2.0,
            'stock' => 0,
            'activo' => true,
        ]);

        $pedido = $this->createPedido($emprendedor->id, $producto->id, cantidad: 3);

        Livewire::actingAs($emprendedor->user)
            ->test(Pedidos::class)
            ->call('confirmar', $pedido->id);

        $pedido->refresh();

        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
        $this->assertSame(0, Package::count());
    }

    public function test_an_emprendedor_cannot_confirm_another_emprendedors_pedido(): void
    {
        $owner = $this->createEmprendedor();
        $intruder = $this->createEmprendedor();

        $producto = Producto::create([
            'emprendedor_id' => $owner->id,
            'nombre' => 'Producto ajeno',
            'precio_usd' => 15.00,
            'peso_kg' => 2.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $pedido = $this->createPedido($owner->id, $producto->id);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($intruder->user)
            ->test(Pedidos::class)
            ->call('confirmar', $pedido->id);
    }
}
