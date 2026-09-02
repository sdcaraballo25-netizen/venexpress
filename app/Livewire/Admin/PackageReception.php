<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Services\HubReceptionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.admin')]
class PackageReception extends Component
{
    public string $trackingNumber = '';

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
            $this->errorMessage =
                "No existe una guía con número: {$this->trackingNumber}";
        }
    }

    public function receive(): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:100'],
            'destinationLocation' => ['required', 'string', 'max:255'],
        ], [
            'trackingNumber.required' => 'Introduce el número de guía.',
            'destinationLocation.required' => 'Indica el Hub donde fue recibido el paquete.',
        ]);

        $package = Package::query()
            ->where('tracking_number', trim($this->trackingNumber))
            ->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';
            return;
        }

        try {
            $this->package = app(HubReceptionService::class)->receive(
                package: $package,
                userId: (int) auth()->id(),
                destinationLocation: $this->destinationLocation,
            );

            $this->successMessage =
                'Recepción en Hub registrada correctamente. El paquete quedó EN_HUB.';

            $this->destinationLocation = '';
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
            'destinationLocation',
            'package',
            'successMessage',
            'errorMessage',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.package-reception');
    }
}
