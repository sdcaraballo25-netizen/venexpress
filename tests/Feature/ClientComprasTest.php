<?php

namespace Tests\Feature;

use App\Livewire\Client\Compras;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Feature\Concerns\CreatesTestEmprendedores;
use Tests\TestCase;

class ClientComprasTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestEmprendedores;

    private function createClient(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_CLIENTE,
            'status' => User::STATUS_ACTIVE,
            'account_verified_at' => now(),
        ]);
    }

    private function createPedidoFor(?int $userId): Pedido
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
            'user_id' => $userId,
            'precio_total_usd' => 15.00,
            'cliente_nombre' => 'Cliente de Prueba',
            'cliente_id_doc' => 'V-87654321',
            'cliente_telefono' => '0424-7654321',
            'destino_ciudad' => 'Valencia',
            'destino_estado' => 'Carabobo',
            'direccion_entrega' => 'Av. Bolívar, casa 1',
            'chat_token' => 'token-' . uniqid(),
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

    public function test_a_client_sees_only_their_own_pedidos(): void
    {
        $client = $this->createClient();
        $ownPedido = $this->createPedidoFor($client->id);
        $otherClient = $this->createClient();
        $this->createPedidoFor($otherClient->id);

        Livewire::actingAs($client)
            ->test(Compras::class)
            ->assertSee($ownPedido->items->first()->producto->nombre)
            ->assertViewHas('pedidos', fn ($pedidos) => $pedidos->count() === 1);
    }

    public function test_a_client_with_no_pedidos_sees_an_empty_state(): void
    {
        $client = $this->createClient();

        $this->actingAs($client)
            ->get(route('cliente.compras'))
            ->assertOk()
            ->assertSee('Todavía no has hecho compras');
    }
}
