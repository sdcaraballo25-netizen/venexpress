<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Models\Route;
use App\Services\DeliveryAssignmentService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.admin')]
class DriverAssignment extends Component
{
    public string $trackingNumber = '';
    public ?Package $package = null;
    public ?int $routeId = null;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    public function search(): void
    {
        $this->package = null;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->errorMessage = 'Introduce el número de guía.';
            return;
        }

        $this->package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with(['driver.user', 'ally', 'histories'])
            ->first();

        if (! $this->package) {
            $this->errorMessage = 'Guía no encontrada.';
        }
    }

    public function assign(): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:50'],
            'routeId' => ['required', 'integer'],
        ]);

        try {
            $package = Package::where('tracking_number', trim($this->trackingNumber))->firstOrFail();
            $route = Route::findOrFail($this->routeId);

            $this->package = app(DeliveryAssignmentService::class)->assign(
                $package,
                $route,
                (int) auth()->id(),
            );

            $this->successMessage = 'Paquete asignado correctamente al repartidor.';
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        $routes = Route::query()
            ->with('driver.user')
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->whereNotNull('driver_id')
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.driver-assignment', compact('routes'));
    }
}
