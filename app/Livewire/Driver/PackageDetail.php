<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use App\Services\PackageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.driver')]
class PackageDetail extends Component
{
    public int $packageId;

    public Package $package;

    public function mount(int $packageId): void
    {
        $user = Auth::user();

$driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $this->package = Package::query()
            ->where(
                'driver_id',
                $driver->id
            )
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
     * El repartidor inicia el proceso de entrega.
     */
    public function startDelivery(): void
    {
        try {

            $user = Auth::user();

$driver = $user?->driver;

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

            if (
                $this->package->current_status
                !== Package::STATUS_RECOLECTADO_VENEXPRESS
            ) {
                throw new RuntimeException(
                    'El paquete debe estar recolectado antes de iniciar la entrega.'
                );
            }

            $this->package =
                app(PackageService::class)->changeStatus(
                    package: $this->package,
                    newStatus:
                        Package::STATUS_EN_TRANSITO_NACIONAL,
                    userId:
                        Auth::id(),
                    locationDescription:
                        'Entrega iniciada por el repartidor',
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
     * El repartidor confirma que la entrega a domicilio fue realizada.
     */
    public function completeDelivery(): void
    {
        try {

            $user = Auth::user();

$driver = $user?->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            $this->package->refresh();

            $this->package =
                app(PackageService::class)->completeDelivery(
                    package: $this->package,
                    driver: $driver,
                    locationDescription:
                        'Entrega confirmada por el repartidor',
                );

            session()->flash(
                'success',
                'La entrega fue confirmada correctamente.'
            );

        } catch (RuntimeException $e) {

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * El repartidor registra el cobro en destino (COD).
     */
    public function collectCod(): void
    {
        try {

            $user = Auth::user();

            $driver = $user?->driver;

            if (! $driver) {
                abort(
                    403,
                    'Tu usuario no tiene un perfil de repartidor asociado.'
                );
            }

            $this->package->refresh();

            $this->package =
    app(PackageService::class)->collectCod(
        package: $this->package,
        userId: (int) Auth::id(),
        driver: $driver,
    );

            session()->flash(
                'success',
                'El cobro COD fue registrado correctamente.'
            );

        } catch (RuntimeException $e) {

            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function render()
    {
        return view(
            'livewire.driver.package-detail'
        );
    }
}
