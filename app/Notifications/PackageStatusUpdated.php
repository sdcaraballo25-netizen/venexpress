<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa por correo al destinatario que el estado de un envío cambió.
 *
 * Se envía de forma síncrona (no implementa ShouldQueue) para que
 * funcione de inmediato sin necesitar `php artisan queue:work`
 * corriendo en segundo plano. Si el proyecto crece y el volumen de
 * guías lo justifica, esta clase puede pasar a colas agregando
 * `implements ShouldQueue` — pero entonces sí hace falta un worker
 * activo o los correos se quedan encolados sin enviarse nunca.
 *
 * Quien llama a esta notificación (App\Services\PackageService) la
 * envuelve en try/catch: un fallo de correo nunca debe revertir ni
 * bloquear una operación de guía ya guardada en base de datos.
 */
class PackageStatusUpdated extends Notification
{
    public function __construct(
        protected Package $package,
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
        $statusLabel = Package::STATUS_LABELS[$this->status]
            ?? $this->status;

        $trackingUrl = route('tracking.show', [
            'guia' => $this->package->tracking_number,
        ]);

        return (new MailMessage)
            ->subject(
                "Guía {$this->package->tracking_number}: {$statusLabel} — VenExpress"
            )
            ->greeting('¡Hola!')
            ->line(
                "Tu envío con guía {$this->package->tracking_number} "
                . "cambió de estado a: \"{$statusLabel}\"."
            )
            ->line(
                "Origen: {$this->package->origin_city} → "
                . "Destino: {$this->package->destination_city}"
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
            'package_id' => $this->package->id,
            'tracking_number' => $this->package->tracking_number,
            'status' => $this->status,
        ];
    }
}
