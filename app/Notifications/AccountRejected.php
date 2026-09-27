<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a un Aliado, Repartidor o Emprendedor que un Admin rechazó su
 * verificación (verification_status = RECHAZADO — ver
 * Ally/Driver/Emprendedor::VERIFICATION_REJECTED). El estado
 * operativo (status) ya no se mueve al rechazar desde Fase 3; esta
 * notificación es sobre la verificación de identidad, no sobre la
 * cuenta operativa en sí.
 *
 * En cola: el Admin no necesita esperar a que salga el correo para
 * que su acción de rechazar termine. Hay un worker corriendo en
 * producción (ver supervisor-venexpress-worker.conf).
 */
class AccountRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $roleLabel,
        protected ?string $reason = null,
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
        $message = (new MailMessage)
            ->subject('Tu solicitud de VenExpress no fue aprobada')
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Tu solicitud como {$this->roleLabel} en VenExpress no fue aprobada.");

        if ($this->reason) {
            $message->line("Motivo: {$this->reason}");
        }

        return $message
            ->line('Puedes corregir tu información y documentos, y volver a enviarlos para revisión desde tu panel.')
            ->line('Si crees que esto es un error o quieres más información, contacta a nuestro equipo de soporte.');
    }
}
