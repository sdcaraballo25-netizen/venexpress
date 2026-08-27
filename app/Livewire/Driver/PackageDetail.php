<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Services\PackageService;
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
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
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

    public function startDelivery(): void
{
    try {
        $driver = auth()->user()->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        $this->package = app(PackageService::class)->changeStatus(
            package: $this->package,
            newStatus: Package::STATUS_RECOLECTADO_VENEXPRESS,
            userId: auth()->id(),
            locationDescription: 'Recolección confirmada por el repartidor',
        );

        session()->flash('success', 'La entrega ha sido iniciada correctamente.');
    } catch (RuntimeException $e) {
        session()->flash('error', $e->getMessage());
    }
}

    public function render()
    {
        return view('livewire.driver.package-detail');
    }
}