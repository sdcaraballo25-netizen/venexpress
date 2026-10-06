<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso a los administradores: un paquete se escaneó en un destino que
 * no le corresponde (ver MisroutedPackageAlertService).
 */
class MisroutedPackageAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $packageId,
        protected string $scannedAt,
        protected ?string $expectedDestination = null,
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
            ->subject("Alerta: guía {$package->tracking_number} en destino equivocado — VenExpress")
            ->greeting('Hola,')
            ->line("La guía {$package->tracking_number} se escaneó en {$this->scannedAt}, que no es su destino.")
            ->line($this->expectedDestination
                ? "Su destino es {$this->expectedDestination} ({$package->destination_city}, {$package->destination_state})."
                : "Su destino es {$package->destination_city}, {$package->destination_state}.")
            ->line('Quedó una incidencia abierta para que se corrija el traslado.')
            ->action('Ver incidencias', route('admin.incidents'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'package_id' => $this->packageId,
            'scanned_at' => $this->scannedAt,
        ];
    }
}
