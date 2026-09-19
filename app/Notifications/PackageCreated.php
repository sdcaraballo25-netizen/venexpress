<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa por correo, al registrarse una guía, tanto al remitente como
 * al destinatario — cada uno con un mensaje distinto ($role) aunque
 * comparten los mismos datos de la guía.
 *
 * Se envía de forma síncrona (no implementa ShouldQueue), igual
 * criterio que PackageStatusUpdated: quien llama a esto
 * (PackageService) lo envuelve en try/catch para que un fallo de
 * correo nunca revierta ni bloquee una guía ya guardada.
 */
class PackageCreated extends Notification
{
    public const ROLE_SENDER = 'sender';

    public const ROLE_RECIPIENT = 'recipient';

    public function __construct(
        protected Package $package,
        protected string $role,
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
        $trackingUrl = route('tracking.show', [
            'guia' => $this->package->tracking_number,
        ]);

        $mail = (new MailMessage)
            ->subject("Guía {$this->package->tracking_number} registrada — VenExpress")
            ->greeting('¡Hola!');

        if ($this->role === self::ROLE_SENDER) {
            $mail->line(
                "Tu paquete fue enviado bajo la guía {$this->package->tracking_number}, "
                . "dirigido a {$this->package->recipient_name}."
            );
        } else {
            $mail->line(
                "{$this->package->sender_name} te ha enviado un paquete bajo la guía "
                . "{$this->package->tracking_number}."
            );
        }

        $mail
            ->line("Origen: {$this->package->origin_city} → Destino: {$this->package->destination_city}")
            ->line("Remitente: {$this->package->sender_name}")
            ->line("Destinatario: {$this->package->recipient_name}")
            ->action('Rastrear mi envío', $trackingUrl)
            ->line('Gracias por confiar en VenExpress para tus envíos a nivel nacional.');

        return $mail;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'package_id' => $this->package->id,
            'tracking_number' => $this->package->tracking_number,
            'role' => $this->role,
        ];
    }
}
