<?php

namespace Tests\Feature\Public;

use App\Livewire\Public\PedidoChat;
use App\Models\Incident;
use App\Models\MensajePedido;
use App\Models\Package;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\Feature\Concerns\CreatesTestPackages;
use Tests\TestCase;

class PedidoChatTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;
    use CreatesTestPackages;

    private function createPedidoWithToken(string $token): Pedido
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

        return Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $emprendedor->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'chat_token' => $token,
        ]);
    }

    public function test_a_guest_can_open_the_chat_with_a_valid_token(): void
    {
        $pedido = $this->createPedidoWithToken('token-valido-123');

        $this->get(route('public.marketplace.pedido', 'token-valido-123'))
            ->assertOk()
            ->assertSee($pedido->producto->nombre);
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

    public function test_a_client_can_report_a_defective_product_on_a_confirmed_pedido(): void
    {
        $emprendedor = $this->createEmprendedor();
        $pickupAlly = $emprendedor->pickupAlly;

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $package = $this->createPackage($pickupAlly, [
            'current_status' => Package::STATUS_ENTREGADO,
        ]);

        $pedido = Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $emprendedor->id,
            'package_id' => $package->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_CONFIRMADO,
            'chat_token' => 'token-garantia-1',
        ]);

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

        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => 5,
            'activo' => true,
        ]);

        $package = $this->createPackage($emprendedor->pickupAlly);

        $pedido = Pedido::create([
            'producto_id' => $producto->id,
            'emprendedor_id' => $emprendedor->id,
            'package_id' => $package->id,
            'cantidad' => 1,
            'precio_unitario_usd' => 15.00,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'status' => Pedido::STATUS_CONFIRMADO,
            'chat_token' => 'token-garantia-2',
        ]);

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
}
