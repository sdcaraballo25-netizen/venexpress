<?php

namespace Tests\Feature;

use App\Livewire\Ally\EmprendedorPedidos;
use App\Livewire\Emprendedor\PedidoShow;
use App\Livewire\Public\PedidoChat;
use App\Models\Emprendedor;
use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Notifications\PedidoCanceladoPorCliente;
use App\Notifications\PedidoEstadoActualizado;
use App\Services\PedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

/**
 * Cancelación de pedidos del marketplace: el comprador mientras está
 * PENDIENTE (desde el chat), el emprendedor mientras está PENDIENTE o
 * PAGADO (devolviendo el stock). Un pedido con guía no se cancela.
 */
class PedidoCancelacionTest extends TestCase
{
    use CreatesTestEmprendedores;
    use RefreshDatabase;

    private function createPedido(Emprendedor $emprendedor, string $status = Pedido::STATUS_PENDIENTE, int $stock = 5): array
    {
        $producto = Producto::create([
            'emprendedor_id' => $emprendedor->id,
            'nombre' => 'Producto de prueba',
            'precio_usd' => 15.00,
            'peso_kg' => 1.0,
            'stock' => $stock,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'emprendedor_id' => $emprendedor->id,
            'precio_total_usd' => 30.00,
            'cliente_nombre' => 'Comprador de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'cliente_email' => 'comprador@example.com',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'direccion_entrega' => 'Av. Bolívar',
            'status' => $status,
            'chat_token' => Str::random(40),
        ]);

        PedidoItem::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 2,
            'precio_unitario_usd' => 15.00,
            'subtotal_usd' => 30.00,
        ]);

        return [$pedido, $producto];
    }

    public function test_the_buyer_can_cancel_a_pending_pedido_from_the_chat(): void
    {
        Notification::fake();

        $emprendedor = $this->createEmprendedor();
        [$pedido, $producto] = $this->createPedido($emprendedor);

        Livewire::test(PedidoChat::class, ['token' => $pedido->chat_token])
            ->assertSee('Cancelar pedido')
            ->set('showCancelar', true)
            ->set('motivoCancelacion', 'Me equivoqué de talla')
            ->call('cancelarPedido')
            ->assertSet('cancelacionError', null)
            ->assertSee('Cancelaste este pedido');

        $pedido->refresh();

        $this->assertSame(Pedido::STATUS_CANCELADO, $pedido->status);
        $this->assertSame(Pedido::CANCELADO_POR_CLIENTE, $pedido->cancelado_por);
        $this->assertSame('Me equivoqué de talla', $pedido->motivo_cancelacion);
        $this->assertNotNull($pedido->cancelado_at);
        $this->assertSame(5, (int) $producto->fresh()->stock);

        $this->assertTrue(
            MensajePedido::where('pedido_id', $pedido->id)
                ->where('autor', MensajePedido::AUTOR_CLIENTE)
                ->where('texto', 'like', 'Canceló el pedido.%')
                ->exists()
        );

        Notification::assertSentOnDemand(
            PedidoCanceladoPorCliente::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $emprendedor->user->email
        );
    }

    public function test_the_buyer_cannot_cancel_once_the_payment_was_confirmed(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido] = $this->createPedido($emprendedor, Pedido::STATUS_PAGADO);

        Livewire::test(PedidoChat::class, ['token' => $pedido->chat_token])
            ->assertDontSee('Sí, cancelar pedido')
            ->call('cancelarPedido')
            ->assertSet('cancelacionError', 'El vendedor ya confirmó tu pago, así que no puedes cancelar el pedido desde aquí. Escríbele por este chat para acordarlo.');

        $this->assertSame(Pedido::STATUS_PAGADO, $pedido->fresh()->status);
    }

    public function test_the_emprendedor_can_cancel_a_paid_pedido_and_the_stock_is_restored(): void
    {
        Notification::fake();

        $emprendedor = $this->createEmprendedor();
        [$pedido, $producto] = $this->createPedido($emprendedor, Pedido::STATUS_PENDIENTE, stock: 5);

        app(PedidoService::class)->marcarComoPagado($pedido);
        $this->assertSame(3, (int) $producto->fresh()->stock);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->set('motivoCancelacion', 'Se me agotó el producto')
            ->call('cancelar')
            ->assertSet('errorMessage', null)
            ->assertSet('successMessage', 'Pedido cancelado. Le avisamos al comprador.');

        $pedido->refresh();

        $this->assertSame(Pedido::STATUS_CANCELADO, $pedido->status);
        $this->assertSame(Pedido::CANCELADO_POR_EMPRENDEDOR, $pedido->cancelado_por);
        $this->assertSame(5, (int) $producto->fresh()->stock);

        Notification::assertSentOnDemand(
            PedidoEstadoActualizado::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'comprador@example.com'
                && $notification->toArray($notifiable)['status'] === Pedido::STATUS_CANCELADO
        );
    }

    public function test_the_emprendedor_must_give_a_reason(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido] = $this->createPedido($emprendedor);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->set('motivoCancelacion', '')
            ->call('cancelar')
            ->assertHasErrors(['motivoCancelacion' => 'required']);

        $this->assertSame(Pedido::STATUS_PENDIENTE, $pedido->fresh()->status);
    }

    public function test_a_pedido_with_a_shipping_guide_cannot_be_cancelled(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido] = $this->createPedido($emprendedor, Pedido::STATUS_CONFIRMADO);

        Livewire::actingAs($emprendedor->user)
            ->test(PedidoShow::class, ['pedidoId' => $pedido->id])
            ->assertDontSee('¿Cancelar este pedido?')
            ->set('motivoCancelacion', 'Intento de cancelar')
            ->call('cancelar')
            ->assertSet('errorMessage', 'Este pedido ya tiene guía de envío y no se puede cancelar.');

        $this->assertSame(Pedido::STATUS_CONFIRMADO, $pedido->fresh()->status);
    }

    public function test_a_cancelled_pedido_cannot_be_cancelled_twice_nor_restore_stock_twice(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido, $producto] = $this->createPedido($emprendedor, Pedido::STATUS_PENDIENTE, stock: 5);

        $service = app(PedidoService::class);
        $service->marcarComoPagado($pedido);
        $service->cancelar($pedido, Pedido::CANCELADO_POR_EMPRENDEDOR, 'Sin stock');

        $this->expectExceptionMessage('Este pedido ya estaba cancelado.');

        try {
            $service->cancelar($pedido, Pedido::CANCELADO_POR_EMPRENDEDOR, 'Otra vez');
        } finally {
            $this->assertSame(5, (int) $producto->fresh()->stock);
        }
    }

    public function test_a_cancelled_pedido_cannot_be_marked_as_paid(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido, $producto] = $this->createPedido($emprendedor);

        $service = app(PedidoService::class);
        $service->cancelar($pedido, Pedido::CANCELADO_POR_CLIENTE);

        $this->expectExceptionMessage('Este pedido ya fue procesado.');

        try {
            $service->marcarComoPagado($pedido);
        } finally {
            $this->assertSame(5, (int) $producto->fresh()->stock);
        }
    }

    public function test_the_agency_cannot_generate_a_guide_for_a_cancelled_pedido(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido] = $this->createPedido($emprendedor, Pedido::STATUS_CANCELADO);

        Livewire::actingAs($emprendedor->pickupAlly->user)
            ->test(EmprendedorPedidos::class)
            ->set('pedidoIdInput', (string) $pedido->id)
            ->call('buscar')
            ->assertSet('searchError', 'Este pedido fue cancelado: no se le puede generar guía.');
    }

    public function test_the_cancellation_emails_render(): void
    {
        $emprendedor = $this->createEmprendedor();
        [$pedido] = $this->createPedido($emprendedor);

        app(PedidoService::class)->cancelar($pedido, Pedido::CANCELADO_POR_EMPRENDEDOR, 'Sin stock');

        $notifiable = Notification::route('mail', 'x@example.com');

        $buyerMail = (new PedidoEstadoActualizado($pedido->id, Pedido::STATUS_CANCELADO))->toMail($notifiable);
        $this->assertStringContainsString('cancelado', $buyerMail->subject);
        $this->assertContains('Motivo: Sin stock', $buyerMail->introLines);

        $sellerMail = (new PedidoCanceladoPorCliente($pedido->id))->toMail($notifiable);
        $this->assertStringContainsString('cancelado', $sellerMail->subject);
    }
}
