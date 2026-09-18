<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a un Aliado o Repartidor que un Admin aprobó su solicitud
 * (Ally::STATUS_ACTIVE / Driver::STATUS_ACTIVE) y ya puede usar su
 * panel con normalidad.
 *
 * No implementa ShouldQueue: si la cola no tiene worker corriendo
 * (QUEUE_CONNECTION=sync es el caso más común en este proyecto), un
 * envío en cola nunca llegaría a salir.
 */
class AccountApproved extends Notification
{
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
