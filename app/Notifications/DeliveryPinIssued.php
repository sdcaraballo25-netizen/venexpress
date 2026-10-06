<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa al destinatario que su envío salió a reparto y le da el PIN
 * de entrega (ver PackageService::sendOutForDelivery()).
 *
 * El PIN en claro solo existe aquí: en la base de datos se guarda su
 * hash. Por eso el trabajo en cola va cifrado (ShouldBeEncrypted) y
 * toArray() no lo incluye.
 */
class DeliveryPinIssued extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $packageId,
        protected string $pin,
    ) {}

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

        return (new MailMessage)
            ->subject("Guía {$package->tracking_number}: tu envío salió a reparto — VenExpress")
            ->greeting('¡Hola!')
            ->line("Tu envío con guía {$package->tracking_number} va en camino a la dirección de entrega.")
            ->line("Tu PIN de entrega es: **{$this->pin}**")
            ->line('Dáselo al repartidor solo cuando tengas el paquete en tus manos. Nadie de VenExpress te lo pedirá por teléfono ni por mensaje.')
            ->line('Si no tienes el PIN a mano, también puedes recibirlo mostrando tu cédula.')
            ->action('Rastrear mi envío', route('tracking.show', ['guia' => $package->tracking_number]))
            ->line('Gracias por confiar en VenExpress para tus envíos a nivel nacional.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'package_id' => $this->packageId,
        ];
    }
}
