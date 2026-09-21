<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Incident;
use App\Models\MensajePedido;
use App\Models\Pedido;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Chat del cliente con el emprendedor sobre un pedido puntual. Acceso
 * por token (sin cuenta), igual que el rastreo público por número de
 * guía: el cliente del marketplace nunca necesitó registrarse.
 */
class PedidoChat extends Component
{
    use ResolvesLayoutForViewer;

    public Pedido $pedido;

    public string $texto = '';

    public bool $showReportarProblema = false;

    public string $problemaDescripcion = '';

    public ?string $garantiaMensaje = null;

    public function mount(string $token): void
    {
        $this->pedido = Pedido::where('chat_token', $token)
            ->with(['producto', 'emprendedor.user', 'package'])
            ->firstOrFail();
    }

    public function enviarMensaje(): void
    {
        $this->validate([
            'texto' => ['required', 'string', 'max:2000'],
        ]);

        MensajePedido::create([
            'pedido_id' => $this->pedido->id,
            'autor' => MensajePedido::AUTOR_CLIENTE,
            'texto' => $this->texto,
        ]);

        $this->texto = '';
    }

    /**
     * Reporta un producto defectuoso/dañado. Solo aplica a pedidos ya
     * confirmados (con guía real generada): sin producto entregado, no
     * hay nada que reclamar todavía.
     *
     * Reutiliza el mismo sistema de Incidencias que ya usan Aliados y
     * Clientes — el admin lo revisa desde Admin\IncidentsManager, sin
     * necesitar una pantalla nueva.
     */
    public function reportarProblema(): void
    {
        $this->garantiaMensaje = null;

        if ($this->pedido->status !== Pedido::STATUS_CONFIRMADO || ! $this->pedido->package_id) {
            return;
        }

        $this->validate([
            'problemaDescripcion' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        Incident::create([
            'ally_id' => $this->pedido->emprendedor->pickup_ally_id,
            'package_id' => $this->pedido->package_id,
            'reported_by_user_id' => null,
            'type' => 'MARKETPLACE_PRODUCTO_DEFECTUOSO',
            'description' => $this->problemaDescripcion,
            'status' => Incident::STATUS_OPEN,
        ]);

        // Queda también en el chat para que el emprendedor lo vea de
        // una vez, sin tener que revisar el panel de incidencias.
        MensajePedido::create([
            'pedido_id' => $this->pedido->id,
            'autor' => MensajePedido::AUTOR_CLIENTE,
            'texto' => "Reportó un problema con el producto: {$this->problemaDescripcion}",
        ]);

        $this->problemaDescripcion = '';
        $this->showReportarProblema = false;
        $this->garantiaMensaje = 'Reporte enviado. El equipo de Venexpress lo revisará — recuerda que un producto '
            . 'defectuoso solo da derecho a cambio, no a devolución de dinero (ese acuerdo es directamente con el emprendedor).';
    }

    public function render()
    {
        return view('public.pedido-chat', [
            'mensajes' => $this->pedido->mensajes,
        ])->layout(
            $this->resolveLayoutForViewer(),
            ['title' => 'Tu pedido — Venexpress']
        );
    }
}
