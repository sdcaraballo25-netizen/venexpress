<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa por correo al destinatario que el estado de un envío cambió.
 *
 * En cola: con muchas guías cambiando de estado a la vez (escaneos en
 * el HUB, entregas de repartidores), esperar la respuesta del
 * servidor de correo en cada una haría lento justo el flujo operativo
 * más repetido del sistema. Hay un worker corriendo en producción
 * (ver supervisor-venexpress-worker.conf).
 *
 * Guarda solo packageId (no el modelo Package completo), igual
 * convención que Jobs\GeocodePackageDeliveryAddress: evita depender de
 * SerializesModels y siempre lee el estado más reciente de la guía en
 * el momento en que el worker realmente procesa el correo.
 *
 * Quien llama a esta notificación (App\Services\PackageService) la
 * envuelve en try/catch: un fallo al encolar nunca debe revertir ni
 * bloquear una operación de guía ya guardada en base de datos.
 */
class PackageStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $packageId,
        protected string $status,
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

        $statusLabel = Package::STATUS_LABELS[$this->status]
            ?? $this->status;

        $trackingUrl = route('tracking.show', [
            'guia' => $package->tracking_number,
        ]);

        return (new MailMessage)
            ->subject(
                "Guía {$package->tracking_number}: {$statusLabel} — VenExpress"
            )
            ->greeting('¡Hola!')
            ->line(
                "Tu envío con guía {$package->tracking_number} "
                . "cambió de estado a: \"{$statusLabel}\"."
            )
            ->line(
                "Origen: {$package->origin_city} → "
                . "Destino: {$package->destination_city}"
            )
            ->action('Rastrear mi envío', $trackingUrl)
            ->line(
                'Gracias por confiar en VenExpress para tus envíos '
                . 'a nivel nacional.'
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'package_id' => $this->packageId,
            'status' => $this->status,
        ];
    }
}
