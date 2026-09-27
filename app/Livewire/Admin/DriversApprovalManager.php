<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Driver;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
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
     * Id del repartidor cuyo modal de rechazo está abierto (motivo
     * obligatorio, ver reject()).
     */
    public ?int $rejectingDriverId = null;

    public string $rejectionReason = '';

    /**
     * Id del repartidor cuyo modal de detalle (datos + documentos de
     * verificación) está abierto.
     */
    public ?int $viewingDriverId = null;

    public bool $showDetailsModal = false;

    public function viewDetails(int $driverId): void
    {
        $this->viewingDriverId = $driverId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->viewingDriverId = null;
    }

    /**
     * Aprobar un repartidor: marca la verificación de identidad como
     * VERIFICADO y activa la cuenta operativa en el mismo paso (así
     * es como se aprueba hoy — ver Fase 2/3 del sistema de
     * verificaciones).
     */
    public function approve(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;
        $previousVerification = $driver->verification_status;

        $driver->update([
            'status' => Driver::STATUS_ACTIVE,
            'verification_status' => Driver::VERIFICATION_VERIFIED,
            'verification_rejection_reason' => null,
            'verification_reviewed_at' => now(),
        ]);

        $this->logDriverAction(
            $driver,
            'driver.approved',
            "Aprobó al repartidor {$driver->user?->name}.",
            [
                'previous_status' => $previousStatus,
                'new_status' => Driver::STATUS_ACTIVE,
                'previous_verification_status' => $previousVerification,
                'new_verification_status' => Driver::VERIFICATION_VERIFIED,
            ]
        );

        $driver->user?->notify(new AccountApproved('Repartidor'));

        session()->flash('success', 'El repartidor fue aprobado correctamente.');
    }

    /**
     * Abre el modal donde el Admin escribe el motivo obligatorio de
     * rechazo.
     */
    public function openReject(int $driverId): void
    {
        $this->rejectingDriverId = $driverId;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    public function cancelReject(): void
    {
        $this->rejectingDriverId = null;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    /**
     * Rechazar un repartidor: solo mueve verification_status, no
     * toca status (ver EnsureAccountIsApproved / Driver::canOperate()).
     * El motivo es obligatorio.
     */
    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], ['rejectionReason' => 'motivo de rechazo']);

        $driver = Driver::findOrFail($this->rejectingDriverId);
        $previousVerification = $driver->verification_status;

        $driver->update([
            'verification_status' => Driver::VERIFICATION_REJECTED,
            'verification_rejection_reason' => $this->rejectionReason,
            'verification_reviewed_at' => now(),
        ]);

        $driver->user?->tokens()->delete();

        $this->logDriverAction(
            $driver,
            'driver.rejected',
            "Rechazó al repartidor {$driver->user?->name}.",
            [
                'previous_verification_status' => $previousVerification,
                'new_verification_status' => Driver::VERIFICATION_REJECTED,
                'reason' => $this->rejectionReason,
            ]
        );

        $driver->user?->notify(new AccountRejected('Repartidor', $this->rejectionReason));

        $this->cancelReject();

        session()->flash('success', 'El repartidor fue rechazado.');
    }

    /**
     * Suspender un repartidor: solo afecta el estado operativo, la
     * verificación de identidad ya realizada no se pierde.
     */
    public function suspend(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);
        $previousStatus = $driver->status;

        $driver->update([
            'status' => Driver::STATUS_SUSPENDED,
        ]);

        $driver->user?->tokens()->delete();

        $this->logDriverAction(
            $driver,
            'driver.suspended',
            "Suspendió al repartidor {$driver->user?->name}.",
            ['previous_status' => $previousStatus, 'new_status' => Driver::STATUS_SUSPENDED]
        );

        session()->flash('success', 'El repartidor fue suspendido.');
    }

    /**
     * Reactivar un repartidor suspendido. No se puede activar una
     * cuenta que no esté VERIFICADA — esta validación es de backend,
     * no solo de la vista (el botón "Activar" hoy solo aparece para
     * SUSPENDIDO, que solo se alcanza habiendo estado VERIFICADO
     * antes, pero se guarda igual por seguridad ante llamadas
     * directas).
     */
    public function activate(int $driverId): void
    {
        $driver = Driver::findOrFail($driverId);

        if ($driver->verification_status !== Driver::VERIFICATION_VERIFIED) {
            session()->flash('error', 'No se puede activar: el repartidor todavía no está verificado.');

            return;
        }

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
            'viewingDriver' => $this->viewingDriverId
                ? Driver::with('user')->find($this->viewingDriverId)
                : null,
        ]);
    }
}
