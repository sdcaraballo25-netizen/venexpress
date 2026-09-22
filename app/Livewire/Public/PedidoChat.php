<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Incident;
use App\Models\MensajePedido;
use App\Models\Pedido;
use App\Models\Resena;
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

    public int $estrellas = 0;

    public string $comentario = '';

    public ?string $resenaMensaje = null;

    public function mount(string $token): void
    {
        $this->pedido = Pedido::where('chat_token', $token)
            ->with(['producto', 'emprendedor.user', 'package', 'resena'])
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

    /**
     * Reseña del producto para esta compra puntual. Una por pedido
     * (resenas.pedido_id es unique) y solo una vez que hay guía real
     * generada — mismo criterio que reportarProblema().
     */
    public function enviarResena(): void
    {
        $this->resenaMensaje = null;

        if ($this->pedido->status !== Pedido::STATUS_CONFIRMADO || ! $this->pedido->package_id) {
            return;
        }

        if ($this->pedido->resena) {
            return;
        }

        $this->validate([
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:1000'],
        ]);

        $resena = Resena::create([
            'pedido_id' => $this->pedido->id,
            'producto_id' => $this->pedido->producto_id,
            'estrellas' => $this->estrellas,
            'comentario' => $this->comentario !== '' ? $this->comentario : null,
        ]);

        $this->pedido->setRelation('resena', $resena);
        $this->resenaMensaje = '¡Gracias por tu reseña!';
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
