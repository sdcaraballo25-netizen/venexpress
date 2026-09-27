<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa al cliente que su pedido del marketplace cambió de estado
 * (PAGADO: el emprendedor confirmó que recibió el pago; CONFIRMADO:
 * ya hay guía de envío real). Antes no había ninguna notificación: el
 * cliente solo se enteraba si volvía a abrir el enlace del chat
 * (Pedido::chat_token).
 *
 * Se envía solo si hay un correo disponible (Pedido::cliente_email o
 * el email de la cuenta si el cliente tenía sesión iniciada al pedir)
 * — quien la dispara (PedidoService) decide eso antes de despachar.
 */
class PedidoEstadoActualizado extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $pedidoId,
        protected string $status,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pedido = Pedido::with(['items.producto', 'emprendedor', 'package'])->findOrFail($this->pedidoId);

        $chatUrl = route('public.marketplace.pedido', $pedido->chat_token);

        $mail = (new MailMessage)
            ->subject("Tu pedido #{$pedido->id} — Venexpress")
            ->greeting('¡Hola!');

        if ($this->status === Pedido::STATUS_PAGADO) {
            $mail->line(
                "{$pedido->emprendedor->business_name} confirmó que recibió tu pago del pedido #{$pedido->id}. "
                .'Pronto llevará el paquete a la agencia para generar la guía de envío.'
            );
        } else {
            $mail->line("¡Tu pedido #{$pedido->id} ya tiene guía de envío!");

            if ($pedido->package) {
                $trackingUrl = route('tracking.show', ['guia' => $pedido->package->tracking_number]);

                $mail->line("Número de guía: {$pedido->package->tracking_number} — {$trackingUrl}");
            }
        }

        return $mail
            ->line('Puedes ver el detalle y seguir chateando con el vendedor en cualquier momento.')
            ->action('Ver mi pedido', $chatUrl);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pedido_id' => $this->pedidoId,
            'status' => $this->status,
        ];
    }
}
