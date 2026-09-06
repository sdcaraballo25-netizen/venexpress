<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifica al cliente recién registrado su código de verificación.
 *
 * Canal activo hoy: correo (mail). El proyecto todavía no tiene
 * proveedor de SMS/WhatsApp contratado (Twilio, Meta Cloud API,
 * etc.), así que esos canales quedan fuera de `via()` por ahora.
 * Cuando se contrate un proveedor, basta con:
 *
 *   1. Crear el canal correspondiente (p. ej. App\Notifications\Channels\SmsChannel)
 *      implementando `send(object $notifiable, Notification $notification)`.
 *   2. Agregar un método `toSms(object $notifiable): string` aquí con el
 *      texto corto del SMS/WhatsApp.
 *   3. Sumar el canal en `via()`, idealmente leyendo
 *      config('venexpress.account_verification_channels') para poder
 *      activarlo solo por .env sin tocar código.
 *
 * No implementa ShouldQueue a propósito: el usuario está esperando
 * el código en pantalla para poder continuar el registro, así que
 * el envío debe ser síncrono.
 */
class WelcomeVerificationToken extends Notification
{
    public function __construct(
        protected string $plainToken,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return array_intersect(
            config('venexpress.account_verification_channels', ['mail']),
            ['mail'],
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a VenExpress — Verifica tu cuenta')
            ->greeting("¡Hola, {$notifiable->name}!")
            ->line('Gracias por registrarte en VenExpress.')
            ->line('Para activar tu cuenta, ingresa este código de verificación:')
            ->line(new \Illuminate\Support\HtmlString(
                '<div style="text-align:center;margin:24px 0;">'
                . '<span style="display:inline-block;padding:12px 24px;'
                . 'font-size:28px;font-weight:700;letter-spacing:6px;'
                . 'background:#EFF6FF;color:#1E3A8A;border-radius:12px;">'
                . e($this->plainToken)
                . '</span></div>'
            ))
            ->line(
                'Este código vence en '
                . \App\Models\User::VERIFICATION_TOKEN_TTL_MINUTES
                . ' minutos.'
            )
            ->line(
                'Si tú no creaste esta cuenta, puedes ignorar este correo.'
            );
    }
}
