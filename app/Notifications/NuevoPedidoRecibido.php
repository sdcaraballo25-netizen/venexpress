<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa al emprendedor que le llegó un pedido nuevo. Antes no había
 * ninguna notificación: se enteraba solo si entraba a revisar
 * Emprendedor\Pedidos.
 *
 * En cola y guarda solo el id (no el modelo), mismo criterio que
 * PackageCreated: quien la dispara (Public\Marketplace) la envuelve en
 * try/catch para que un fallo de correo nunca revierta ni bloquee un
 * pedido ya guardado.
 */
class NuevoPedidoRecibido extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $pedidoId,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pedido = Pedido::with('items.producto')->findOrFail($this->pedidoId);

        $resumen = $pedido->items
            ->map(fn ($item) => "{$item->cantidad} × {$item->producto?->nombre}")
            ->implode(', ');

        return (new MailMessage)
            ->subject("Nuevo pedido #{$pedido->id} — Venexpress")
            ->greeting('¡Tienes un pedido nuevo!')
            ->line("Pedido #{$pedido->id}: {$resumen}.")
            ->line("Total: \${$pedido->precio_total_usd} — a coordinar directamente con el comprador.")
            ->line("Comprador: {$pedido->cliente_nombre} ({$pedido->cliente_telefono})")
            ->action('Ver pedido', route('emprendedor.pedidos.show', $pedido->id))
            ->line('Confírmalo como pagado en cuanto el comprador te transfiera.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedidoId,
        ];
    }
}
