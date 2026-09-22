<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a un Aliado o Repartidor que un Admin aprobó su solicitud
 * (Ally::STATUS_ACTIVE / Driver::STATUS_ACTIVE) y ya puede usar su
 * panel con normalidad.
 *
 * En cola: el Admin no necesita esperar a que salga el correo para
 * que su acción de aprobar termine. Hay un worker corriendo en
 * producción (ver supervisor-venexpress-worker.conf).
 */
class AccountApproved extends Notification implements ShouldQueue
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
            ->subject('Tu cuenta de VenExpress fue aprobada')
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Buenas noticias: tu solicitud como {$this->roleLabel} en VenExpress fue aprobada.")
            ->line('Ya puedes iniciar sesión y comenzar a usar tu panel con normalidad.')
            ->action('Iniciar sesión', route('login'));
    }
}
