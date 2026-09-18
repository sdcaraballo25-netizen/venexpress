<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Avisa a los administradores que la sincronización automática de la
 * tasa BCV (bcv:sync, cada 15 minutos) falló, para que se enteren el
 * mismo día en vez de descubrirlo cuando BcvRateService::getCurrentRate()
 * empiece a bloquear cotizaciones por tasa vencida (services.bcv_api.
 * max_age_hours) o, peor, cuando un cliente reclame un cobro mal
 * calculado.
 *
 * No implementa ShouldQueue: si la cola no tiene worker corriendo
 * (ver CheckProductionReadiness), un aviso justamente sobre "algo
 * está fallando en segundo plano" no debería depender de otro
 * proceso en segundo plano para llegar.
 */
class BcvRateSyncFailed extends Notification
{
    public function __construct(
        protected string $errorMessage,
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
        return (new MailMessage)
            ->subject('⚠ Falló la sincronización automática de la tasa BCV — VenExpress')
            ->error()
            ->line('El comando programado `bcv:sync` no pudo consultar o guardar la tasa oficial del BCV.')
            ->line('Error: '.$this->errorMessage)
            ->line(
                'Mientras esto no se resuelva, la tasa actual del sistema puede ir quedando '
                .'desactualizada. Si pasa de las 72 horas, el sistema dejará de generar '
                .'cotizaciones y guías nuevas hasta que se actualice manualmente.'
            )
            ->action('Ir al panel de Tasa BCV', route('admin.bcv-rates'))
            ->line('Puedes sincronizar o cargar la tasa manualmente desde ahí mientras se investiga la causa.');
    }
}
