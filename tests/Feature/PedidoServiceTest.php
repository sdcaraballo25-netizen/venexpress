<?php

namespace Tests\Feature;

use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\Emprendedor;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\RateMatrix;
use App\Models\User;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

/**
 * Etapa 1 del módulo Emprendedores: confirmar un pedido queda en dos
 * pasos, no uno.
 *
 *   1. marcarComoPagado(): el emprendedor confirma que ya le pagaron
 *      (fuera de la plataforma) y reserva el stock, pero no genera
 *      guía todavía.
 *   2. registrarGuia(): taquilla, con el paquete físico en mano, mide
 *      peso/tamaño reales e indica frágil/seguro; recién ahí se genera
 *      la guía (Package), reutilizando PackageService en vez de
 *      duplicar el cálculo de tarifa. Cubre también el descuento
 *      global para emprendedores y las validaciones de stock/estado.
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

    private function createEmprendedorConProducto(int $stock = 10): array
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

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 2.0,
            'stock' => $stock,
            'activo' => true,
        ]);

        return [$producto, $pickupAlly];
    }

    private function createPedido(Producto $producto, int $cantidad = 1): Pedido
    {
        $pedido = Pedido::create([
            'emprendedor_id' => $producto->emprendedor_id,
            'precio_total_usd' => $producto->precio_usd * $cantidad,
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
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $producto->precio_usd,
            'subtotal_usd' => $producto->precio_usd * $cantidad,
        ]);

        return $pedido;
    }

    public function test_marcar_como_pagado_reserva_stock_sin_generar_guia(): void
    {
        [$producto] = $this->createEmprendedorConProducto(stock: 10);
        $pedido = $this->createPedido($producto, cantidad: 2);

        $resultado = $this->service->marcarComoPagado($pedido);

        $this->assertSame(Pedido::STATUS_PAGADO, $resultado->status);
        $this->assertNull($resultado->package_id);
        $this->assertSame(0, Package::count());

        $producto->refresh();
        $this->assertSame(8, $producto->stock);
    }

    public function test_marcar_como_pagado_falla_si_no_hay_stock_suficiente(): void
    {
        [$producto] = $this->createEmprendedorConProducto(stock: 1);
        $pedido = $this->createPedido($producto, cantidad: 5);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No hay stock suficiente');

        $this->service->marcarComoPagado($pedido);
    }

    public function test_marcar_como_pagado_falla_si_ya_fue_procesado(): void
    {
        [$producto] = $this->createEmprendedorConProducto();
        $pedido = $this->createPedido($producto);

        $this->service->marcarComoPagado($pedido);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ya fue procesado');

        $this->service->marcarComoPagado($pedido->fresh());
    }

    public function test_marcar_como_pagado_falla_si_el_emprendedor_no_tiene_agencia_de_retiro(): void
    {
        [$producto] = $this->createEmprendedorConProducto();
        $producto->emprendedor()->update(['pickup_ally_id' => null]);
        $pedido = $this->createPedido($producto);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('agencia aliada de retiro');

        $this->service->marcarComoPagado($pedido);
    }

    public function test_no_deja_stock_negativo_ni_marca_pagado_cuando_falla_la_validacion(): void
    {
        [$producto] = $this->createEmprendedorConProducto(stock: 1);
        $pedido = $this->createPedido($producto, cantidad: 5);

        try {
            $this->service->marcarComoPagado($pedido);
        } catch (RuntimeException) {
            // Esperado.
        }

        $producto->refresh();
        $pedido->refresh();

        $this->assertSame(1, $producto->stock);
        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->status);
        $this->assertSame(0, Package::count());
    }

    public function test_registrar_guia_genera_una_guia_real_con_el_peso_verificado_y_el_descuento_aplicado(): void
    {
        [$producto, $pickupAlly] = $this->createEmprendedorConProducto(stock: 10);
        $pedido = $this->createPedido($producto, cantidad: 2);
        $this->service->marcarComoPagado($pedido);

        $package = $this->service->registrarGuia($pedido->fresh(), [
            // El peso real verificado en taquilla (5kg) es distinto al
            // autodeclarado del catálogo (2kg * 2 = 4kg): debe usarse
            // este, no el del producto.
            'physical_weight_kg' => 5.0,
            'is_fragile' => true,
        ], null, $pickupAlly);

        $this->assertInstanceOf(Package::class, $package);

        // base(2.00) + peso(5 * 0.50=2.50) + distancia(150*0.01=1.50)
        // + delivery(4.00) + frágil(3.00) = 13.00; con 20% descuento: 10.40
        $this->assertSame(10.40, (float) $package->total_price_usd);
        $this->assertTrue((bool) $package->requires_delivery);
        $this->assertTrue((bool) $package->is_fragile);
        $this->assertSame('Av. Bolívar, casa 1', $package->delivery_address);
        $this->assertSame('Tienda de Prueba', $package->sender_name);
        $this->assertSame('Cliente de Prueba', $package->recipient_name);
        $this->assertSame('Caracas', $package->origin_city);
        $this->assertSame('Valencia', $package->destination_city);

        $pedido->refresh();
        $this->assertSame(Pedido::STATUS_CONFIRMADO, $pedido->status);
        $this->assertSame($package->id, $pedido->package_id);
    }

    public function test_registrar_guia_falla_si_el_pedido_no_esta_pagado_todavia(): void
    {
        [$producto, $pickupAlly] = $this->createEmprendedorConProducto();
        $pedido = $this->createPedido($producto);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no está listo para generar guía');

        $this->service->registrarGuia($pedido, ['physical_weight_kg' => 3.0], null, $pickupAlly);
    }

    public function test_registrar_guia_falla_si_el_pedido_ya_tiene_guia(): void
    {
        [$producto, $pickupAlly] = $this->createEmprendedorConProducto();
        $pedido = $this->createPedido($producto);
        $this->service->marcarComoPagado($pedido);
        $this->service->registrarGuia($pedido->fresh(), ['physical_weight_kg' => 3.0], null, $pickupAlly);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no está listo para generar guía');

        $this->service->registrarGuia($pedido->fresh(), ['physical_weight_kg' => 3.0], null, $pickupAlly);
    }

    public function test_registrar_guia_falla_si_la_agencia_no_es_la_del_emprendedor(): void
    {
        [$producto] = $this->createEmprendedorConProducto();
        $pedido = $this->createPedido($producto);
        $this->service->marcarComoPagado($pedido);

        $otraAgencia = $this->createAlly(['city' => 'Valencia', 'state' => 'Carabobo']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no pertenece a tu agencia');

        $this->service->registrarGuia($pedido->fresh(), ['physical_weight_kg' => 3.0], null, $otraAgencia);
    }
}
