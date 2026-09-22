<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a un Aliado o Repartidor que un Admin rechazó su solicitud
 * (Ally::STATUS_REJECTED / Driver::STATUS_REJECTED).
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
        return (new MailMessage)
            ->subject('Tu solicitud de VenExpress no fue aprobada')
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Tu solicitud como {$this->roleLabel} en VenExpress no fue aprobada.")
            ->line('Si crees que esto es un error o quieres más información, contacta a nuestro equipo de soporte.');
    }
}
