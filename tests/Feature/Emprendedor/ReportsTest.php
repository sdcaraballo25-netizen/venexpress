<?php

namespace Tests\Feature\Emprendedor;

use App\Exports\SimpleArrayExport;
use App\Livewire\Emprendedor\Reports;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

/**
 * Emprendedor\Reports es la versión de ventas de Admin\Reports /
 * Ally\Reports para el propio emprendedor: acotado a sus pedidos.
 * Cubre el aislamiento entre emprendedores y que solo los pedidos
 * CONFIRMADO cuentan como venta (ver Pedido::STATUS_CONFIRMADO). Un
 * Pedido es un carrito (ver PedidoItem): cada línea tiene su propio
 * producto/cantidad/subtotal.
 */
class ReportsTest extends TestCase
{
    use CreatesTestEmprendedores;
    use RefreshDatabase;

    private function createProducto(int $emprendedorId, array $overrides = []): Producto
    {
        return Producto::create(array_merge([
            'emprendedor_id' => $emprendedorId,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 10.00,
            'peso_kg' => 1.0,
            'stock' => 20,
            'activo' => true,
        ], $overrides));
    }

    private function createPedido(Producto $producto, array $overrides = [], int $cantidad = 1): Pedido
    {
        $precioTotal = $producto->precio_usd * $cantidad;

        $pedido = Pedido::create(array_merge([
            'emprendedor_id' => $producto->emprendedor_id,
            'precio_total_usd' => $precioTotal,
            'cliente_nombre' => 'Cliente de prueba',
            'cliente_id_doc' => 'V-'.random_int(10000000, 99999999),
            'cliente_telefono' => '0414-0000000',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_PENDIENTE,
        ], $overrides));

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario_usd' => $producto->precio_usd,
            'subtotal_usd' => $precioTotal,
        ]);

        return $pedido;
    }

    public function test_the_screen_renders_with_default_period(): void
    {
        $emprendedor = $this->createEmprendedor();

        Livewire::actingAs($emprendedor->user)
            ->test(Reports::class)
            ->assertOk()
            ->assertSee('Reportes');
    }

    public function test_only_counts_pedidos_from_this_emprendedor(): void
    {
        $emprendedor = $this->createEmprendedor();
        $otherEmprendedor = $this->createEmprendedor();

        $this->createPedido($this->createProducto($emprendedor->id));
        $this->createPedido($this->createProducto($otherEmprendedor->id));

        $component = Livewire::actingAs($emprendedor->user)
            ->test(Reports::class)
            ->set('dateRange', '30d');

        $this->assertSame(1, $component->viewData('registeredCount'));
    }

    public function test_sales_total_only_counts_confirmed_pedidos_in_the_period(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProducto($emprendedor->id, ['precio_usd' => 25.00]);

        $confirmed = $this->createPedido($producto, ['status' => Pedido::STATUS_CONFIRMADO]);
        $confirmed->forceFill(['created_at' => now()->subDays(2)])->save();

        // Pendiente: no debe sumar a las ventas aunque esté en el período.
        $this->createPedido($this->createProducto($emprendedor->id, ['precio_usd' => 999.00]), [
            'status' => Pedido::STATUS_PENDIENTE,
        ]);

        // Confirmado pero fuera del período elegido.
        $outOfRange = $this->createPedido($this->createProducto($emprendedor->id, ['precio_usd' => 999.00]), [
            'status' => Pedido::STATUS_CONFIRMADO,
        ]);
        $outOfRange->forceFill(['created_at' => now()->subDays(60)])->save();

        $component = Livewire::actingAs($emprendedor->user)
            ->test(Reports::class)
            ->set('dateRange', '7d');

        $this->assertSame(2, $component->viewData('registeredCount'));
        $this->assertSame(1, $component->viewData('confirmedCount'));
        $this->assertSame(25.0, (float) $component->viewData('salesTotal'));
    }

    public function test_top_productos_ranks_by_confirmed_units_sold(): void
    {
        $emprendedor = $this->createEmprendedor();

        $topProducto = $this->createProducto($emprendedor->id, ['nombre' => 'Producto Estrella']);
        $lowProducto = $this->createProducto($emprendedor->id, ['nombre' => 'Producto Secundario']);

        $this->createPedido($topProducto, ['status' => Pedido::STATUS_CONFIRMADO], cantidad: 5);
        $this->createPedido($lowProducto, ['status' => Pedido::STATUS_CONFIRMADO], cantidad: 1);

        Livewire::actingAs($emprendedor->user)
            ->test(Reports::class)
            ->set('dateRange', '30d')
            ->assertSeeInOrder(['Producto Estrella', 'Producto Secundario']);
    }

    public function test_export_excel_only_includes_this_emprendedors_pedidos(): void
    {
        Excel::fake();

        $emprendedor = $this->createEmprendedor();
        $otherEmprendedor = $this->createEmprendedor();

        $this->createPedido($this->createProducto($emprendedor->id, ['nombre' => 'Producto Propio']));
        $this->createPedido($this->createProducto($otherEmprendedor->id, ['nombre' => 'Producto Ajeno']));

        Livewire::actingAs($emprendedor->user)
            ->test(Reports::class)
            ->call('exportExcel')
            ->assertFileDownloaded();

        $filename = 'reporte-ventas-'.now()->subDays(29)->format('Y-m-d').'-a-'.now()->format('Y-m-d').'.xlsx';

        Excel::assertDownloaded($filename, function (SimpleArrayExport $export) {
            $productos = array_column(iterator_to_array($export->generator()), 3);

            self::assertTrue(collect($productos)->contains(fn ($resumen) => str_contains($resumen, 'Producto Propio')));
            self::assertFalse(collect($productos)->contains(fn ($resumen) => str_contains($resumen, 'Producto Ajeno')));

            return true;
        });
    }
}
