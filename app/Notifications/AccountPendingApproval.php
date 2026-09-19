<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Correo de bienvenida para un Aliado o Repartidor recién registrado.
 *
 * A diferencia de WelcomeVerificationToken (Cliente), esta cuenta no
 * necesita un código para activarse: el usuario queda logueado de
 * inmediato y puede entrar a su panel. Lo que sí bloquea su uso real
 * es la aprobación manual de un Admin (Ally::STATUS_PENDING /
 * Driver::STATUS_PENDING, exigida por el middleware
 * 'account.approved'), así que este correo solo confirma que la
 * solicitud se recibió y está en revisión.
 *
 * En cola: el registro no necesita esperar a que salga este correo
 * para terminar. Hay un worker corriendo en producción (ver
 * supervisor-venexpress-worker.conf).
 */
class AccountPendingApproval extends Notification implements ShouldQueue
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
            ->subject('Bienvenido a VenExpress — Solicitud recibida')
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line("Gracias por registrarte en VenExpress como {$this->roleLabel}.")
            ->line('Tu solicitud fue recibida y está pendiente de revisión por un administrador.')
            ->line('Te avisaremos por correo en cuanto tu cuenta sea aprobada.');
    }
}
