<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use App\Services\LogisticsScanService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class Scanner extends Component
{
    public string $trackingNumber = '';

    public ?Package $package = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public bool $securityWarning = false;

    public ?string $securityMessage = null;

    public function searchPackage(): void
    {
        $this->reset([
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
        ]);

        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->errorMessage = 'Introduce un número de guía.';
            return;
        }

        $driver = auth()->user()?->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        $package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with(['ally', 'driver', 'histories'])
            ->first();

        if (! $package) {
            $this->errorMessage =
                "No existe una guía con número: {$this->trackingNumber}";
            return;
        }

        $this->checkSecurity($package);

        try {
            $package = app(LogisticsScanService::class)->scanCollection(
                package: $package,
                driver: $driver,
                userId: (int) auth()->id(),
            );

            $this->package = $package;
            $this->successMessage =
                'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.';
        } catch (RuntimeException $e) {
            /*
             * Si ya pertenece al repartidor y no está en estado
             * RECIBIDO_AGENCIA, se puede consultar sin repetir el scan.
             */
            if (
                (int) $package->driver_id === (int) $driver->id
                && $package->current_status !== Package::STATUS_RECIBIDO_AGENCIA
            ) {
                $this->package = $package;
                $this->errorMessage = $e->getMessage();
                return;
            }

            $this->errorMessage = $e->getMessage();
            $this->package = $package;
        }
    }

    public function scan(string $trackingNumber): void
    {
        $this->trackingNumber = trim($trackingNumber);

        if ($this->trackingNumber === '') {
            return;
        }

        $this->searchPackage();
    }

    protected function checkSecurity(Package $package): void
    {
        if (! $package->security_hash) {
            $this->securityWarning = false;
            $this->securityMessage = null;
            return;
        }

        if ($package->verifySecurityHash()) {
            $this->securityWarning = false;
            $this->securityMessage = null;
            return;
        }

        $this->securityWarning = true;
        $this->securityMessage =
            'Los datos de esta guía no coinciden con su código de seguridad original. '
            . 'Verifica manualmente antes de continuar.';
    }

    public function clearSearch(): void
    {
        $this->reset([
            'trackingNumber',
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
        ]);
    }

    public function render()
    {
        return view('livewire.driver.scanner');
    }
}
