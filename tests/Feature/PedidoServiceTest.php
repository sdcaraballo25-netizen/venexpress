<?php

namespace Tests\Feature;

use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Emprendedor;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\RateMatrix;
use App\Models\User;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Etapa 1 del módulo Emprendedores: confirmar un pedido debe generar
 * una guía de envío real (Package), reutilizando PackageService en
 * vez de duplicar el cálculo de tarifa. Cubre también el descuento
 * global para emprendedores y las validaciones de stock/estado.
 */
class PedidoServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestPackages;

    protected PedidoService $service;

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
            'emprendedor_discount_percentage' => 20.00,
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

        $this->service = app(PedidoService::class);
    }

    private function createEmprendedorConProducto(int $stock = 10): Producto
    {
        $pickupAlly = $this->createAlly([
            'city' => 'Caracas',
            'state' => 'Distrito Capital',
        ]);

        $user = User::factory()->create([
            'role' => User::ROLE_EMPRENDEDOR,
            'status' => User::STATUS_ACTIVE,
            'phone' => '0414-1112233',
        ]);

        $emprendedor = Emprendedor::create([
            'user_id' => $user->id,
            'pickup_ally_id' => $pickupAlly->id,
            'business_name' => 'Tienda de Prueba',
            'document_id' => 'J-12345678-9',
            'status' => Emprendedor::STATUS_ACTIVE,
        ]);

        return Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 2.0,
            'stock' => $stock,
            'activo' => true,
        ]);
    }

    private function createPedido(Producto $producto, int $cantidad = 1): Pedido
    {
        return Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $producto->emprendedor_id,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $producto->precio_usd,
            'precio_total_usd' => $producto->precio_usd * $cantidad,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
        ]);
    }

    public function test_confirmar_pedido_genera_una_guia_real_con_el_descuento_aplicado(): void
    {
        $producto = $this->createEmprendedorConProducto(stock: 10);
        $pedido = $this->createPedido($producto, cantidad: 2);

        $package = $this->service->confirmarPedido($pedido);

        $this->assertInstanceOf(Package::class, $package);

        // 2kg * 2 unidades = 4kg de peso facturable. El pedido siempre
        // se entrega a domicilio del cliente (requires_delivery=true
        // en PedidoService), así que suma el delivery_price_usd fijo:
        // base(2.00) + peso(4 * 0.50) + distancia(150 * 0.01) + delivery(4.00) = 9.50
        // con 20% de descuento: 9.50 * 0.8 = 7.60
        $this->assertSame(7.60, (float) $package->total_price_usd);
        $this->assertTrue((bool) $package->requires_delivery);
        $this->assertSame('Tienda de Prueba', $package->sender_name);
        $this->assertSame('Cliente de Prueba', $package->recipient_name);
        $this->assertSame('Caracas', $package->origin_city);
        $this->assertSame('Valencia', $package->destination_city);

        $pedido->refresh();
        $this->assertSame(Pedido::STATUS_CONFIRMADO, $pedido->status);
        $this->assertSame($package->id, $pedido->package_id);

        $producto->refresh();
        $this->assertSame(8, $producto->stock);
    }

    public function test_confirmar_pedido_falla_si_no_hay_stock_suficiente(): void
    {
        $producto = $this->createEmprendedorConProducto(stock: 1);
        $pedido = $this->createPedido($producto, cantidad: 5);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No hay stock suficiente');

        $this->service->confirmarPedido($pedido);
    }

    public function test_confirmar_pedido_falla_si_ya_fue_procesado(): void
    {
        $producto = $this->createEmprendedorConProducto();
        $pedido = $this->createPedido($producto);

        $this->service->confirmarPedido($pedido);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ya fue procesado');

        $this->service->confirmarPedido($pedido->fresh());
    }

    public function test_confirmar_pedido_falla_si_el_emprendedor_no_tiene_agencia_de_retiro(): void
    {
        $producto = $this->createEmprendedorConProducto();
        $producto->emprendedor()->update(['pickup_ally_id' => null]);
        $pedido = $this->createPedido($producto);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('agencia aliada de retiro');

        $this->service->confirmarPedido($pedido);
    }

    public function test_no_deja_stock_negativo_ni_crea_guia_cuando_falla_la_validacion(): void
    {
        $producto = $this->createEmprendedorConProducto(stock: 1);
        $pedido = $this->createPedido($producto, cantidad: 5);

        try {
            $this->service->confirmarPedido($pedido);
        } catch (RuntimeException) {
            // Esperado.
        }

        $producto->refresh();
        $pedido->refresh();

        $this->assertSame(1, $producto->stock);
        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
        $this->assertSame(0, Package::count());
    }
}
