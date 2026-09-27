<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\PedidoChat;
use App\Models\Incident;
use App\Models\MensajePedido;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class PedidoChatTest extends TestCase
{
    use CreatesTestEmprendedores;
    use CreatesTestPackages;
    use RefreshDatabase;

    private function createProductoDePrueba($emprendedor): Producto
    {
        return Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);
    }

    private function attachItem(Pedido $pedido, Producto $producto): PedidoItem
    {
        return PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'subtotal_usd' => 15.00,
        ]);
    }

    private function createPedidoWithToken(string $token): Pedido
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProductoDePrueba($emprendedor);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'chat_token' => $token,
        ]);

        $this->attachItem($pedido, $producto);

        return $pedido;
    }

    public function test_a_guest_can_open_the_chat_with_a_valid_token(): void
    {
        $pedido = $this->createPedidoWithToken('token-valido-123');

        $this->get(route('public.marketplace.pedido', 'token-valido-123'))
            ->assertOk()
            ->assertSee($pedido->items->first()->producto->nombre);
    }

    public function test_an_invalid_token_returns_a_404(): void
    {
        $this->get(route('public.marketplace.pedido', 'token-que-no-existe'))
            ->assertNotFound();
    }

    public function test_a_guest_can_send_a_message_as_cliente(): void
    {
        $pedido = $this->createPedidoWithToken('token-chat-456');

        Livewire::test(PedidoChat::class, ['token' => 'token-chat-456'])
            ->set('texto', 'Hola, ¿ya viste mi pedido?')
            ->call('enviarMensaje')
            ->assertHasNoErrors();

        $mensaje = MensajePedido::where('pedido_id', $pedido->id)->first();

        $this->assertNotNull($mensaje);
        $this->assertSame(MensajePedido::AUTOR_CLIENTE, $mensaje->autor);
        $this->assertSame('Hola, ¿ya viste mi pedido?', $mensaje->texto);
    }

    public function test_sending_an_empty_message_is_rejected(): void
    {
        $this->createPedidoWithToken('token-chat-789');

        Livewire::test(PedidoChat::class, ['token' => 'token-chat-789'])
            ->set('texto', '')
            ->call('enviarMensaje')
            ->assertHasErrors(['texto']);
    }

    public function test_a_guest_can_attach_a_payment_receipt_without_any_text(): void
    {
        Storage::fake('public');

        $pedido = $this->createPedidoWithToken('token-chat-adjunto');

        Livewire::test(PedidoChat::class, ['token' => 'token-chat-adjunto'])
            ->set('texto', '')
            ->set('archivo', UploadedFile::fake()->image('comprobante.jpg'))
            ->call('enviarMensaje')
            ->assertHasNoErrors();

        $mensaje = MensajePedido::where('pedido_id', $pedido->id)->first();

        $this->assertNotNull($mensaje);
        $this->assertSame('', $mensaje->texto);
        $this->assertNotNull($mensaje->archivo_path);
        $this->assertSame('comprobante.jpg', $mensaje->archivo_nombre);
        $this->assertTrue($mensaje->esImagen());

        Storage::disk('public')->assertExists($mensaje->archivo_path);
    }

    public function test_sending_neither_text_nor_an_attachment_is_rejected(): void
    {
        $this->createPedidoWithToken('token-chat-vacio');

        Livewire::test(PedidoChat::class, ['token' => 'token-chat-vacio'])
            ->set('texto', '')
            ->set('archivo', null)
            ->call('enviarMensaje')
            ->assertHasErrors(['texto']);

        $this->assertSame(0, MensajePedido::count());
    }

    public function test_a_client_can_report_a_defective_product_on_a_confirmed_pedido(): void
    {
        $emprendedor = $this->createEmprendedor();
        $pickupAlly = $emprendedor->pickupAlly;
        $producto = $this->createProductoDePrueba($emprendedor);

        $package = $this->createPackage($pickupAlly, [
            'current_status' => Package::STATUS_ENTREGADO,
        ]);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'package_id' => $package->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_CONFIRMADO,
            'chat_token' => 'token-garantia-1',
        ]);
        $this->attachItem($pedido, $producto);

        Livewire::test(PedidoChat::class, ['token' => 'token-garantia-1'])
            ->set('problemaDescripcion', 'El producto llegó roto por dentro de la caja.')
            ->call('reportarProblema')
            ->assertHasNoErrors();

        $incident = Incident::where('package_id', $package->id)->first();

        $this->assertNotNull($incident);
        $this->assertSame($pickupAlly->id, $incident->ally_id);
        $this->assertSame(Incident::STATUS_OPEN, $incident->status);
        $this->assertNull($incident->reported_by_user_id);

        $this->assertDatabaseHas('mensajes_pedido', [
            'pedido_id' => $pedido->id,
            'autor' => MensajePedido::AUTOR_CLIENTE,
        ]);
    }

    public function test_reporting_a_problem_requires_a_minimum_description(): void
    {
        $emprendedor = $this->createEmprendedor();
        $producto = $this->createProductoDePrueba($emprendedor);
        $package = $this->createPackage($emprendedor->pickupAlly);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'package_id' => $package->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_CONFIRMADO,
            'chat_token' => 'token-garantia-2',
        ]);
        $this->attachItem($pedido, $producto);

        Livewire::test(PedidoChat::class, ['token' => 'token-garantia-2'])
            ->set('problemaDescripcion', 'roto')
            ->call('reportarProblema')
            ->assertHasErrors(['problemaDescripcion']);

        $this->assertSame(0, Incident::where('package_id', $package->id)->count());
    }

    public function test_cannot_report_a_problem_on_a_pedido_that_is_not_confirmed_yet(): void
    {
        $pedido = $this->createPedidoWithToken('token-pendiente-1');

        Livewire::test(PedidoChat::class, ['token' => 'token-pendiente-1'])
            ->set('problemaDescripcion', 'El producto llegó roto por dentro de la caja.')
            ->call('reportarProblema');

        $this->assertSame(0, Incident::count());
    }

    private function createConfirmedPedidoWithToken(string $token): Pedido
    {
        $emprendedor = $this->createEmprendedor();
        $pickupAlly = $emprendedor->pickupAlly;
        $producto = $this->createProductoDePrueba($emprendedor);

        $package = $this->createPackage($pickupAlly, [
            'current_status' => Package::STATUS_ENTREGADO,
        ]);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'package_id' => $package->id,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_CONFIRMADO,
            'chat_token' => $token,
        ]);
        $this->attachItem($pedido, $producto);

        return $pedido;
    }

    public function test_a_client_can_leave_a_star_rating_and_review_on_a_confirmed_pedido(): void
    {
        $pedido = $this->createConfirmedPedidoWithToken('token-resena-1');

        Livewire::test(PedidoChat::class, ['token' => 'token-resena-1'])
            ->set('estrellas', 4)
            ->set('comentario', 'Muy buen producto, llegó a tiempo.')
            ->call('enviarResena')
            ->assertHasNoErrors();

        $resena = Resena::where('pedido_id', $pedido->id)->first();

        $this->assertNotNull($resena);
        $this->assertSame(4, $resena->estrellas);
        $this->assertSame('Muy buen producto, llegó a tiempo.', $resena->comentario);
        $this->assertSame($pedido->items->first()->producto_id, $resena->producto_id);
    }

    public function test_a_review_requires_at_least_one_star(): void
    {
        $this->createConfirmedPedidoWithToken('token-resena-2');

        Livewire::test(PedidoChat::class, ['token' => 'token-resena-2'])
            ->set('comentario', 'Sin estrellas seleccionadas.')
            ->call('enviarResena')
            ->assertHasErrors(['estrellas']);

        $this->assertSame(0, Resena::count());
    }

    public function test_the_comment_is_optional_on_a_review(): void
    {
        $this->createConfirmedPedidoWithToken('token-resena-3');

        Livewire::test(PedidoChat::class, ['token' => 'token-resena-3'])
            ->set('estrellas', 5)
            ->call('enviarResena')
            ->assertHasNoErrors();

        $this->assertSame(1, Resena::count());
        $this->assertNull(Resena::first()->comentario);
    }

    public function test_cannot_leave_a_second_review_for_the_same_pedido(): void
    {
        $pedido = $this->createConfirmedPedidoWithToken('token-resena-4');

        Resena::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $pedido->items->first()->producto_id,
            'estrellas' => 3,
            'comentario' => 'Primera reseña.',
        ]);

        Livewire::test(PedidoChat::class, ['token' => 'token-resena-4'])
            ->set('estrellas', 1)
            ->call('enviarResena');

        $this->assertSame(1, Resena::count());
        $this->assertSame(3, Resena::first()->estrellas);
    }

    public function test_cannot_review_a_pedido_that_is_not_confirmed_yet(): void
    {
        $this->createPedidoWithToken('token-resena-5');

        Livewire::test(PedidoChat::class, ['token' => 'token-resena-5'])
            ->set('estrellas', 5)
            ->call('enviarResena');

        $this->assertSame(0, Resena::count());
    }
}
