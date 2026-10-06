<?php

namespace App\Livewire\Ally;

use App\Models\Ally;
use App\Models\Package;
use App\Services\DestinationReceptionService;
use App\Services\MisroutedPackageAlertService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.ally')]
class PackageReception extends Component
{
    public string $trackingNumber = '';

    public ?Package $package = null;

    public ?string $message = null;

    public ?string $error = null;

    public function search(): void
    {
        $this->package = null;
        $this->message = null;
        $this->error = null;

        $this->trackingNumber = trim(
            $this->trackingNumber
        );

        if ($this->trackingNumber === '') {
            $this->error = 'Introduce el número de guía.';
            return;
        }

        $ally = auth()->user()->resolveAlly();

        if (! $ally) {
            abort(
                403,
                'Tu usuario no tiene una agencia aliada asociada.'
            );
        }

        $package = Package::query()
            ->where(
                'tracking_number',
                $this->trackingNumber
            )
            ->with([
                'ally',
                'histories',
            ])
            ->first();

        if (! $package) {
            $this->error = 'Guía no encontrada.';
            return;
        }

        if (! $this->isAuthorizedPickupPoint(
            $package,
            $ally
        )) {
            $this->error = $this->reportIfMisrouted($package, $ally)
                ?? 'Esta guía no está asignada a tu agencia como punto de retiro.';

            return;
        }

        $this->package = $package;
    }

    /**
     * Un paquete en tránsito que llegó físicamente a una agencia que no
     * es su punto de retiro: se avisa al administrador
     * (MisroutedPackageAlertService) sin mostrarle a esta agencia los
     * datos de la guía. Devuelve el mensaje para la agencia, o null si
     * no aplica (p. ej. una guía en otro estado, tecleada por error).
     */
    protected function reportIfMisrouted(Package $package, Ally $ally): ?string
    {
        // Si esta agencia SÍ es su punto de retiro (pero no está activa o
        // verificada como destino) no es un error de envío, es de
        // configuración: no se alerta.
        if (
            $package->current_status !== Package::STATUS_EN_TRANSITO_NACIONAL
            || (int) $package->pickup_ally_id === (int) $ally->id
        ) {
            return null;
        }

        app(MisroutedPackageAlertService::class)->report(
            $package,
            'la agencia '.$ally->business_name,
            (int) auth()->id(),
            $package->pickupAlly?->business_name ?? $package->destinationWarehouse?->name,
        );

        return 'Esta guía va a otro punto de retiro: no la recibas. Ya avisamos a Venexpress para corregir el envío.';
    }

    public function receive(): void
    {
        $this->message = null;
        $this->error = null;

        $this->validate([
            'trackingNumber' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        try {
            $ally = auth()->user()->resolveAlly();

            if (! $ally) {
                abort(
                    403,
                    'Tu usuario no tiene una agencia aliada asociada.'
                );
            }

            $trackingNumber = trim(
                $this->trackingNumber
            );

            $package = Package::query()
                ->where(
                    'tracking_number',
                    $trackingNumber
                )
                ->firstOrFail();

            if (! $this->isAuthorizedPickupPoint(
                $package,
                $ally
            )) {
                throw new RuntimeException(
                    $this->reportIfMisrouted($package, $ally)
                    ?? 'La guía no está asignada a esta agencia como punto de retiro.'
                );
            }

            $this->package =
                app(DestinationReceptionService::class)
                    ->receive(
                        package: $package,
                        userId: (int) auth()->id(),
                        destinationLocation:
                            $ally->business_name,
                    );

            $this->message =
                'Recepción registrada. El paquete quedó LISTO_RETIRO.';
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    /**
     * Fase 5A — corrige la brecha de Fase 3: antes, cualquier Ally
     * activo cuya ciudad/estado coincidiera POR TEXTO con el destino
     * del paquete podía recibirlo, sin mirar si el cliente lo había
     * elegido realmente como punto de retiro (pickup_ally_id) ni si
     * estaba verificado como destino. Ahora solo puede recibirlo el
     * Ally que sea exactamente pickup_ally_id, esté activo y esté
     * verificado como destino (Ally::isVerifiedDestination(), Fase 1).
     *
     * Un paquete con requires_delivery = true nunca tiene
     * pickup_ally_id (Fase 3), así que con esto también queda
     * bloqueado que se reciba por error en una agencia un paquete que
     * en realidad requiere entrega a domicilio.
     */
    protected function isAuthorizedPickupPoint(
        Package $package,
        Ally $ally
    ): bool {
        if ($package->pickup_ally_id === null) {
            return false;
        }

        if ((int) $package->pickup_ally_id !== (int) $ally->id) {
            return false;
        }

        if ($ally->status !== Ally::STATUS_ACTIVE) {
            return false;
        }

        return $ally->isVerifiedDestination();
    }

    public function render()
    {
        return view(
            'livewire.ally.package-reception'
        );
    }
}
