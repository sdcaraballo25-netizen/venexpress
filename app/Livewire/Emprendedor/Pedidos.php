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
     * Confirma un pedido propio: genera la guía real (ver
     * PedidoService::confirmarPedido) y descuenta el stock. Solo
     * después de que el emprendedor confirmó, por fuera de la
     * plataforma, que el cliente ya le pagó.
     */
    public function confirmar(int $pedidoId, PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $pedido = Pedido::where('emprendedor_id', $this->emprendedor()->id)
            ->findOrFail($pedidoId);

        try {
            $package = $pedidoService->confirmarPedido($pedido, Auth::id());

            $this->successMessage = "Pedido confirmado. Guía generada: {$package->tracking_number}.";
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.emprendedor.pedidos', [
            'pedidos' => Pedido::query()
                ->where('emprendedor_id', $this->emprendedor()->id)
                ->with(['producto', 'package'])
                ->latest()
                ->paginate(15),
        ]);
    }
}
