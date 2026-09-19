<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

/**
 * Código de 6 dígitos que autoriza un cambio de contraseña desde el
 * perfil. No implementa ShouldQueue a propósito: el usuario está
 * esperando el código en pantalla para continuar, así que el envío
 * debe ser síncrono (igual criterio que WelcomeVerificationToken).
 */
class PasswordChangeCode extends Notification
{
    public function __construct(
        protected string $plainCode,
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
            ->subject('VenExpress — Código para cambiar tu contraseña')
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta.')
            ->line('Ingresa este código para confirmar el cambio:')
            ->line(new HtmlString(
                '<div style="text-align:center;margin:24px 0;">'
                . '<span style="display:inline-block;padding:12px 24px;'
                . 'font-size:28px;font-weight:700;letter-spacing:6px;'
                . 'background:#EFF6FF;color:#1E3A8A;border-radius:12px;">'
                . e($this->plainCode)
                . '</span></div>'
            ))
            ->line(
                'Este código vence en '
                . \App\Models\User::PASSWORD_CHANGE_CODE_TTL_MINUTES
                . ' minutos.'
            )
            ->line(
                'Si tú no solicitaste este cambio, ignora este correo: tu contraseña actual seguirá funcionando.'
            );
    }
}
