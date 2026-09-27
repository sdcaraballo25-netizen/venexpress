<?php

namespace App\Livewire\Emprendedor;

use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.emprendedor')]
#[Title('Pedidos')]
class Pedidos extends Component
{
    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    protected function emprendedor()
    {
        return Auth::user()->emprendedor;
    }

    /**
     * Marca un pedido propio como pagado (ver
     * PedidoService::marcarComoPagado) y descuenta el stock. Solo
     * después de que el emprendedor confirmó, por fuera de la
     * plataforma, que el cliente ya le pagó. La guía real se genera
     * después, en taquilla, cuando lleve el paquete a su agencia
     * aliada (Ally\EmprendedorPedidos).
     */
    public function confirmar(int $pedidoId, PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $pedido = Pedido::where('emprendedor_id', $this->emprendedor()->id)
            ->findOrFail($pedidoId);

        try {
            $pedidoService->marcarComoPagado($pedido);

            $this->successMessage = 'Pedido marcado como pagado. Lleva el paquete a tu agencia aliada para generar la guía.';
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.emprendedor.pedidos', [
            'pedidos' => Pedido::query()
                ->where('emprendedor_id', $this->emprendedor()->id)
                ->with(['items.producto', 'package'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
