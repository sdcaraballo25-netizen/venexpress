<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso al emprendedor de que el comprador canceló un pedido todavía
 * pendiente de pago (ver PedidoService::cancelar()).
 */
class PedidoCanceladoPorCliente extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $pedidoId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pedido = Pedido::findOrFail($this->pedidoId);

        $mail = (new MailMessage)
            ->subject("Pedido #{$pedido->id} cancelado — Venexpress")
            ->greeting('Un comprador canceló su pedido')
            ->line("{$pedido->cliente_nombre} canceló el pedido #{$pedido->id} antes de confirmar el pago.");

        if ($pedido->motivo_cancelacion) {
            $mail->line("Motivo: {$pedido->motivo_cancelacion}");
        }

        return $mail
            ->line('No tienes que hacer nada más: el stock de tus productos no se había descontado.')
            ->action('Ver pedido', route('emprendedor.pedidos.show', $pedido->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedidoId,
        ];
    }
}
