<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use App\Services\PackageService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class Scanner extends Component
{
    public string $trackingNumber = '';

    public ?Package $package = null;

    public ?string $errorMessage = null;

   /**
 * Busca una guía manualmente.
 *
 * Si el paquete no tiene repartidor:
 * - verifica que esté disponible en RECIBIDO_AGENCIA
 * - asigna el paquete al repartidor activo que lo escaneó.
 */
    public function searchPackage(): void
    {
        $this->reset([
            'package',
            'errorMessage',
        ]);

        $this->trackingNumber = trim(
            $this->trackingNumber
        );

        if ($this->trackingNumber === '') {
            $this->errorMessage =
                'Introduce un número de guía.';

            return;
        }

        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        $driver = $user->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Buscar paquete
        |--------------------------------------------------------------------------
        */

        $package = Package::query()
            ->where(
                'tracking_number',
                $this->trackingNumber
            )
            ->first();

        if (! $package) {
            $this->errorMessage =
                "No existe una guía con número: "
                . "{$this->trackingNumber}";

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Si ya tiene repartidor
        |--------------------------------------------------------------------------
        */

        if ($package->driver_id !== null) {

            /*
             * Si pertenece a otro repartidor,
             * no permitimos acceder.
             */
            if (
                (int) $package->driver_id
                !== (int) $driver->id
            ) {
                $this->errorMessage =
                    'Esta guía está asignada a otro repartidor.';

                return;
            }

            /*
             * Si ya pertenece al repartidor actual,
             * simplemente mostramos el paquete.
             */
            $this->package = $package->fresh();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 3. El paquete todavía NO tiene repartidor
        |--------------------------------------------------------------------------
        */

        /*
         * Solo se puede asignar mediante escaneo/búsqueda
         * si todavía está en la agencia.
         */
        if (
            $package->current_status
            !== Package::STATUS_RECIBIDO_AGENCIA
        ) {
            $this->errorMessage =
                'Esta guía no está disponible para ser asignada. '
                . 'Su estado actual es: '
                . $package->statusLabel()
                . '.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | 6. Asignar paquete al repartidor
        |--------------------------------------------------------------------------
        */

        try {
            $packageService = app(
                PackageService::class
            );

            $package = $packageService->assignDriverOnScan(
                package: $package,
                driver: $driver,
            );

            /*
             * Recargamos para tener todos los datos actualizados.
             */
            $package->load([
                'ally',
            ]);

            $this->package = $package;

            $this->errorMessage = null;

        } catch (RuntimeException $e) {

            $this->errorMessage =
                $e->getMessage();

            return;
        }
    }

    /**
     * Recibe el número de guía desde el lector QR.
     */
    public function scan(
        string $trackingNumber
    ): void {
        $trackingNumber = trim(
            $trackingNumber
        );

        if ($trackingNumber === '') {
            return;
        }

        $this->trackingNumber =
            $trackingNumber;

        $this->searchPackage();
    }

    /**
     * Limpia la búsqueda actual.
     */
    public function clearSearch(): void
    {
        $this->reset([
            'trackingNumber',
            'package',
            'errorMessage',
        ]);
    }

    public function render()
    {
        return view(
            'livewire.driver.scanner'
        );
    }
}