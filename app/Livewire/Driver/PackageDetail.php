<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use App\Services\PackageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class PackageDetail extends Component
{
    public int $packageId;

    public Package $package;

    public function mount(int $packageId): void
    {
        $driver = auth()->user()->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $this->package = Package::query()
            ->where('driver_id', $driver->id)
            ->with([
                'ally',
                'driver',
                'histories',
                'incidents',
            ])
            ->withCount('incidents')
            ->findOrFail($packageId);
    }

    /**
     * El repartidor confirma que comienza a trabajar con el paquete.
     *
     * El paquete debe haber sido previamente recolectado.
     */
    public function startDelivery(): void
    {
        try {
            $driver = auth()->user()->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            $this->package->refresh();

            if (
                (int) $this->package->driver_id
                !== (int) $driver->id
            ) {
                throw new RuntimeException(
                    'Este paquete no está asignado a tu ruta.'
                );
            }

            if ($this->package->current_status !== Package::STATUS_LISTO_RETIRO) {
                throw new RuntimeException(
                    'El paquete debe estar listo para iniciar la entrega.'
                );
            }

            $this->package = app(
                PackageService::class
            )->changeStatus(
                package: $this->package,
                newStatus:
                    Package::STATUS_EN_TRANSITO_NACIONAL,
                userId: auth()->id(),
                locationDescription:
                    'Entrega iniciada por el repartidor',
                eventType:
                    \App\Models\PackageHistory::EVENT_REPARTO,
                originLocation:
                    'Ruta de recolección',
                destinationLocation:
                    'Dirección de entrega',
            );

            session()->flash(
                'success',
                'La entrega ha sido iniciada correctamente.'
            );
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * El repartidor confirma que realizó la entrega.
     *
     * El cliente debe haber aceptado previamente la entrega.
     */
    public function completeDelivery(): void
    {
        try {
            $driver = auth()->user()->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            $this->package = app(
                PackageService::class
            )->completeDelivery(
                package: $this->package,
                driver: $driver,
                locationDescription:
                    'Entrega completada por el repartidor',
            );

            session()->flash(
                'success',
                'Entrega confirmada correctamente.'
            );
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function collectCod(): void
    {
        try {
            $driver = auth()->user()->driver;
            if (! $driver) abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
            if ((int) $this->package->driver_id !== (int) $driver->id) throw new RuntimeException('Este paquete no está asignado a tu cuenta.');
            $this->package = app(PackageService::class)->collectCod($this->package, (int) auth()->id());
            session()->flash('success', 'Cobro COD registrado correctamente.');
        } catch (RuntimeException $e) { session()->flash('error', $e->getMessage()); }
    }

    public function render()
    {
        return view(
            'livewire.driver.package-detail'
        );
    }
}
