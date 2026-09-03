<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Services\PackageDispatchService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.admin')]
class PackageDispatch extends Component
{
    public string $trackingNumber = '';

    public string $originLocation = '';

    public string $destinationLocation = '';

    public ?Package $package = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function search(): void
    {
        $this->reset(['package', 'successMessage', 'errorMessage']);

        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->errorMessage = 'Introduce el número de guía.';
            return;
        }

        $this->package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with(['ally', 'driver', 'histories'])
            ->first();

        if (! $this->package) {
            $this->errorMessage = 'La guía no existe.';
        }
    }

    public function dispatchPackage(): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:100'],
            'originLocation' => ['required', 'string', 'max:255'],
            'destinationLocation' => ['required', 'string', 'max:255'],
        ], [
            'trackingNumber.required' => 'Introduce el número de guía.',
            'originLocation.required' => 'Indica el Hub de origen.',
            'destinationLocation.required' => 'Indica el destino.',
        ]);

        $package = Package::query()
            ->where('tracking_number', trim($this->trackingNumber))
            ->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';
            return;
        }

        try {
            $this->package = app(PackageDispatchService::class)->dispatch(
                package: $package,
                userId: (int) auth()->id(),
                originLocation: $this->originLocation,
                destinationLocation: $this->destinationLocation,
            );

            $this->successMessage =
                'Despacho registrado. El paquete quedó en tránsito nacional.';
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
            $this->package = $package->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        }
    }

    public function clear(): void
    {
        $this->reset([
            'trackingNumber',
            'originLocation',
            'destinationLocation',
            'package',
            'successMessage',
            'errorMessage',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.package-dispatch');
    }
}
