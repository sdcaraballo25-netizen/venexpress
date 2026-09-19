<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa por correo, al registrarse una guía, tanto al remitente como
 * al destinatario — cada uno con un mensaje distinto ($role) aunque
 * comparten los mismos datos de la guía.
 *
 * En cola, igual criterio que PackageStatusUpdated: una taquilla
 * registrando pedidos seguidos no debería esperar dos viajes de
 * correo (remitente + destinatario) en cada uno. Quien llama a esto
 * (PackageService) lo envuelve en try/catch para que un fallo al
 * encolar nunca revierta ni bloquee una guía ya guardada.
 *
 * Guarda solo packageId (no el modelo Package completo), igual
 * convención que Jobs\GeocodePackageDeliveryAddress: evita depender de
 * SerializesModels y siempre lee el estado más reciente de la guía en
 * el momento en que el worker realmente procesa el correo, no el que
 * tenía cuando se encoló.
 */
class PackageCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public const ROLE_SENDER = 'sender';

    public const ROLE_RECIPIENT = 'recipient';

    public function __construct(
        protected int $packageId,
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
        $package = Package::findOrFail($this->packageId);

        $trackingUrl = route('tracking.show', [
            'guia' => $package->tracking_number,
        ]);

        $mail = (new MailMessage)
            ->subject("Guía {$package->tracking_number} registrada — VenExpress")
            ->greeting('¡Hola!');

        if ($this->role === self::ROLE_SENDER) {
            $mail->line(
                "Tu paquete fue enviado bajo la guía {$package->tracking_number}, "
                . "dirigido a {$package->recipient_name}."
            );
        } else {
            $mail->line(
                "{$package->sender_name} te ha enviado un paquete bajo la guía "
                . "{$package->tracking_number}."
            );
        }

        $mail
            ->line("Origen: {$package->origin_city} → Destino: {$package->destination_city}")
            ->line("Remitente: {$package->sender_name}")
            ->line("Destinatario: {$package->recipient_name}")
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
            'package_id' => $this->packageId,
            'role' => $this->role,
        ];
    }
}
