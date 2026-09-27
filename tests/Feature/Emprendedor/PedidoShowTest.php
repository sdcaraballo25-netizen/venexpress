<?php

namespace Tests\Feature\Emprendedor;

use App\Livewire\Emprendedor\PedidoShow;
use App\Models\BcvRate;
use App\Models\CityDistance;
use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\RateMatrix;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class PedidoShowTest extends TestCase
{
    use CreatesTestEmprendedores;
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

    private function createPedidoFor($emprendedor): Pedido
    {
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
            'chat_token' => 'token-'.uniqid(),
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'subtotal_usd' => 15.00,
        ]);

        return $pedido;
    }

    public function test_an_emprendedor_can_send_a_message_on_their_own_pedido(): void
    {
        $emprendedor = $this->createEmprendedor();
        $pedido = $this->createPedidoFor($emprendedor);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->set('texto', 'Ya tengo tu pedido listo')
            ->call('enviarMensaje')
            ->assertHasNoErrors();

        $mensaje = MensajePedido::where('pedido_id', $pedido->id)->first();

        $this->assertNotNull($mensaje);
        $this->assertSame(MensajePedido::AUTOR_EMPRENDEDOR, $mensaje->autor);
    }

    public function test_an_emprendedor_can_attach_a_file_to_a_message(): void
    {
        Storage::fake('public');

        $emprendedor = $this->createEmprendedor();
        $pedido = $this->createPedidoFor($emprendedor);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->set('texto', 'Aquí está tu guía')
            ->set('archivo', UploadedFile::fake()->create('guia.pdf', 100, 'application/pdf'))
            ->call('enviarMensaje')
            ->assertHasNoErrors();

        $mensaje = MensajePedido::where('pedido_id', $pedido->id)->first();

        $this->assertNotNull($mensaje);
        $this->assertNotNull($mensaje->archivo_path);
        $this->assertSame('guia.pdf', $mensaje->archivo_nombre);
        $this->assertFalse($mensaje->esImagen());

        Storage::disk('public')->assertExists($mensaje->archivo_path);
    }

    public function test_an_emprendedor_cannot_view_another_emprendedors_pedido(): void
    {
        $owner = $this->createEmprendedor();
        $intruder = $this->createEmprendedor();
        $pedido = $this->createPedidoFor($owner);

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($intruder->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id]);
    }

    public function test_confirming_from_the_show_page_marks_the_pedido_as_paid_without_a_package_yet(): void
    {
        $emprendedor = $this->createEmprendedor();
        $pedido = $this->createPedidoFor($emprendedor);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->call('confirmar')
            ->assertSet('errorMessage', null);

        $this->assertSame(Pedido::STATUS_PAGADO, $pedido->fresh()->status);
        $this->assertNull($pedido->fresh()->package_id);
    }
}
