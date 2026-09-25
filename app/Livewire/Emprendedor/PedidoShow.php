<?php

namespace App\Livewire\Emprendedor;

use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('layouts.emprendedor')]
#[Title('Pedido')]
class PedidoShow extends Component
{
    use WithFileUploads;

    public Pedido $pedido;

    public string $texto = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $archivo = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public function mount(int $pedidoId): void
    {
        $this->pedido = Pedido::where('emprendedor_id', Auth::user()->emprendedor->id)
            ->with(['items.producto', 'package', 'resena'])
            ->findOrFail($pedidoId);
    }

    public function enviarMensaje(): void
    {
        if (trim($this->texto) === '' && ! $this->archivo) {
            $this->addError('texto', 'Escribe un mensaje o adjunta un archivo.');

            return;
        }

        $this->validate([
            'texto' => ['nullable', 'string', 'max:2000'],
            'archivo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        $data = [
            'pedido_id' => $this->pedido->id,
            'autor' => MensajePedido::AUTOR_EMPRENDEDOR,
            'texto' => trim($this->texto),
        ];

        if ($this->archivo) {
            $data['archivo_path'] = $this->archivo->store('mensajes-pedido', 'public');
            $data['archivo_nombre'] = $this->archivo->getClientOriginalName();
        }

        MensajePedido::create($data);

        $this->reset(['texto', 'archivo']);
    }

    public function confirmar(PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        try {
            $pedidoService->marcarComoPagado($this->pedido);

            $this->pedido->refresh();

            $this->successMessage = 'Pedido marcado como pagado. Lleva el paquete a tu agencia aliada para generar la guía.';
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
