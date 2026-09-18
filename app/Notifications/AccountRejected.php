<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a un Aliado o Repartidor que un Admin rechazó su solicitud
 * (Ally::STATUS_REJECTED / Driver::STATUS_REJECTED).
 *
 * No implementa ShouldQueue: si la cola no tiene worker corriendo
 * (QUEUE_CONNECTION=sync es el caso más común en este proyecto), un
 * envío en cola nunca llegaría a salir.
 */
class AccountRejected extends Notification
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
            ->subject('Tu solicitud de VenExpress no fue aprobada')
            ->greeting("Hola, {$notifiable->name}.")
            ->line("Tu solicitud como {$this->roleLabel} en VenExpress no fue aprobada.")
            ->line('Si crees que esto es un error o quieres más información, contacta a nuestro equipo de soporte.');
    }
}
