<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Driver;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Aprobación de Repartidores')]
class DriversApprovalManager extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Aprobar un repartidor.
     */
    public function approve(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;

        $driver->update([
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $this->logDriverAction(
            $driver,
            'driver.approved',
            "Aprobó al repartidor {$driver->user?->name}.",
            ['previous_status' => $previousStatus, 'new_status' => Driver::STATUS_ACTIVE]
        );

        session()->flash('success', 'El repartidor fue aprobado correctamente.');
    }

    /**
     * Rechazar un repartidor.
     */
    public function reject(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;

        $driver->update([
            'status' => Driver::STATUS_REJECTED,
        ]);

        $this->logDriverAction(
            $driver,
            'driver.rejected',
            "Rechazó al repartidor {$driver->user?->name}.",
            ['previous_status' => $previousStatus, 'new_status' => Driver::STATUS_REJECTED]
        );

        session()->flash('success', 'El repartidor fue rechazado.');
    }

    /**
     * Suspender un repartidor.
     */
    public function suspend(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;

        $driver->update([
            'status' => Driver::STATUS_SUSPENDED,
        ]);

        $this->logDriverAction(
            $driver,
            'driver.suspended',
            "Suspendió al repartidor {$driver->user?->name}.",
            ['previous_status' => $previousStatus, 'new_status' => Driver::STATUS_SUSPENDED]
        );

        session()->flash('success', 'El repartidor fue suspendido.');
    }

    /**
     * Reactivar un repartidor suspendido.
     */
    public function activate(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;

        $driver->update([
            'status' => Driver::STATUS_ACTIVE,
        ]);

        $this->logDriverAction(
            $driver,
            'driver.activated',
            "Reactivó al repartidor {$driver->user?->name}.",
            ['previous_status' => $previousStatus, 'new_status' => Driver::STATUS_ACTIVE]
        );

        session()->flash('success', 'El repartidor fue activado nuevamente.');
    }

    /**
     * Registra en la bitácora de auditoría una acción administrativa
     * sobre un repartidor (cambio de estado).
     */
    protected function logDriverAction(Driver $driver, string $action, string $description, array $metadata = []): void
    {
        AuditLog::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'target_type' => Driver::class,
            'target_id' => $driver->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
        ]);
    }

    /**
     * Reiniciar paginación cuando cambia la búsqueda.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $drivers = Driver::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                })->orWhere('vehicle_plate', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.drivers-approval-manager', [
            'drivers' => $drivers,
        ]);
    }
}
