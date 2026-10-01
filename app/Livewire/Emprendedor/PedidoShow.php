<?php

namespace App\Livewire\Emprendedor;

use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use RuntimeException;

#[Layout('layouts.emprendedor')]
#[Title('Pedido')]
class PedidoShow extends Component
{
    use WithFileUploads;

    public Pedido $pedido;

    public string $texto = '';

    /** @var TemporaryUploadedFile|null */
    public $archivo = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public bool $showCancelar = false;

    public string $motivoCancelacion = '';

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
            $data['archivo_path'] = $this->archivo->store('mensajes-pedido', 'documents');
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

    /**
     * El emprendedor puede cancelar mientras el pedido está PENDIENTE o
     * PAGADO (si estaba pagado se le devuelve el stock). El motivo es
     * obligatorio: se le muestra al comprador en el chat y por correo.
     */
    public function cancelar(PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        $this->validate([
            'motivoCancelacion' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'motivoCancelacion.required' => 'Indica el motivo: el comprador lo verá.',
            'motivoCancelacion.min' => 'Describe el motivo con un poco más de detalle.',
        ]);

        try {
            $pedidoService->cancelar($this->pedido, Pedido::CANCELADO_POR_EMPRENDEDOR, $this->motivoCancelacion);

            $this->successMessage = 'Pedido cancelado. Le avisamos al comprador.';
            $this->showCancelar = false;
            $this->motivoCancelacion = '';
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }

        $this->pedido->refresh();
    }

    public function render()
    {
        return view('livewire.emprendedor.pedido-show', [
            'mensajes' => $this->pedido->mensajes,
        ]);
    }
}
