<?php

namespace App\Livewire\Emprendedor;

use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.emprendedor')]
#[Title('Pedido')]
class PedidoShow extends Component
{
    public Pedido $pedido;

    public string $texto = '';

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(int $pedidoId): void
    {
        $this->pedido = Pedido::where('emprendedor_id', Auth::user()->emprendedor->id)
            ->with(['producto', 'package'])
            ->findOrFail($pedidoId);
    }

    public function enviarMensaje(): void
    {
        $this->validate([
            'texto' => ['required', 'string', 'max:2000'],
        ]);

        MensajePedido::create([
            'pedido_id' => $this->pedido->id,
            'autor' => MensajePedido::AUTOR_EMPRENDEDOR,
            'texto' => $this->texto,
        ]);

        $this->texto = '';
    }

    public function confirmar(PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        try {
            $package = $pedidoService->confirmarPedido($this->pedido, Auth::id());

            $this->pedido->refresh();

            $this->successMessage = "Pedido confirmado. Guía generada: {$package->tracking_number}.";
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.emprendedor.pedido-show', [
            'mensajes' => $this->pedido->mensajes,
        ]);
    }
}
