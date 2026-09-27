<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Emprendedor;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Mismo patrón que Admin\DriversApprovalManager, aplicado a
 * Emprendedores (módulo de marketplace).
 */
#[Layout('layouts.admin')]
#[Title('Aprobación de Emprendedores')]
class EmprendedoresApprovalManager extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Id del emprendedor cuyo modal de detalle (datos + documentos de
     * verificación) está abierto. Antes de Fase 3 esta pantalla no
     * mostraba nada más que business_name/document_id.
     */
    public ?int $viewingEmprendedorId = null;

    public bool $showDetailsModal = false;

    /**
     * Id del emprendedor cuyo modal de rechazo está abierto (motivo
     * obligatorio, ver reject()).
     */
    public ?int $rejectingEmprendedorId = null;

    public string $rejectionReason = '';

    public function viewDetails(int $emprendedorId): void
    {
        $this->viewingEmprendedorId = $emprendedorId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->viewingEmprendedorId = null;
    }

    /**
     * Aprobar un emprendedor: marca la verificación de identidad como
     * VERIFICADO y activa la cuenta operativa en el mismo paso.
     */
    public function approve(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);
        $previousStatus = $emprendedor->status;
        $previousVerification = $emprendedor->verification_status;

        $emprendedor->update([
            'status' => Emprendedor::STATUS_ACTIVE,
            'verification_status' => Emprendedor::VERIFICATION_VERIFIED,
            'verification_rejection_reason' => null,
            'verification_reviewed_at' => now(),
        ]);

        $this->logAction(
            $emprendedor,
            'emprendedor.approved',
            "Aprobó al emprendedor {$emprendedor->business_name}.",
            [
                'previous_status' => $previousStatus,
                'new_status' => Emprendedor::STATUS_ACTIVE,
                'previous_verification_status' => $previousVerification,
                'new_verification_status' => Emprendedor::VERIFICATION_VERIFIED,
            ]
        );

        $emprendedor->user?->notify(new AccountApproved('Emprendedor'));

        session()->flash('success', 'El emprendedor fue aprobado correctamente.');
    }

    /**
     * Abre el modal donde el Admin escribe el motivo obligatorio de
     * rechazo.
     */
    public function openReject(int $emprendedorId): void
    {
        $this->rejectingEmprendedorId = $emprendedorId;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    public function cancelReject(): void
    {
        $this->rejectingEmprendedorId = null;
        $this->rejectionReason = '';
        $this->resetErrorBag('rejectionReason');
    }

    /**
     * Rechazar un emprendedor: solo mueve verification_status, no
     * toca status. El motivo es obligatorio.
     */
    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [], ['rejectionReason' => 'motivo de rechazo']);

        $emprendedor = Emprendedor::findOrFail($this->rejectingEmprendedorId);
        $previousVerification = $emprendedor->verification_status;

        $emprendedor->update([
            'verification_status' => Emprendedor::VERIFICATION_REJECTED,
            'verification_rejection_reason' => $this->rejectionReason,
            'verification_reviewed_at' => now(),
        ]);

        $emprendedor->user?->tokens()->delete();

        $this->logAction(
            $emprendedor,
            'emprendedor.rejected',
            "Rechazó al emprendedor {$emprendedor->business_name}.",
            [
                'previous_verification_status' => $previousVerification,
                'new_verification_status' => Emprendedor::VERIFICATION_REJECTED,
                'reason' => $this->rejectionReason,
            ]
        );

        $emprendedor->user?->notify(new AccountRejected('Emprendedor', $this->rejectionReason));

        $this->cancelReject();

        session()->flash('success', 'El emprendedor fue rechazado.');
    }

    /**
     * Suspender un emprendedor: solo afecta el estado operativo.
     */
    public function suspend(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);
        $previousStatus = $emprendedor->status;

        $emprendedor->update([
            'status' => Emprendedor::STATUS_SUSPENDED,
        ]);

        $emprendedor->user?->tokens()->delete();

        $this->logAction(
            $emprendedor,
            'emprendedor.suspended',
            "Suspendió al emprendedor {$emprendedor->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Emprendedor::STATUS_SUSPENDED]
        );

        session()->flash('success', 'El emprendedor fue suspendido.');
    }

    /**
     * Reactivar un emprendedor suspendido. No se puede activar una
     * cuenta que no esté VERIFICADA — validación de backend, no solo
     * de la vista.
     */
    public function activate(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);

        if ($emprendedor->verification_status !== Emprendedor::VERIFICATION_VERIFIED) {
            session()->flash('error', 'No se puede activar: el emprendedor todavía no está verificado.');

            return;
        }

        $previousStatus = $emprendedor->status;

        $emprendedor->update([
            'status' => Emprendedor::STATUS_ACTIVE,
        ]);

        $this->logAction(
            $emprendedor,
            'emprendedor.activated',
            "Reactivó al emprendedor {$emprendedor->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Emprendedor::STATUS_ACTIVE]
        );

        session()->flash('success', 'El emprendedor fue activado nuevamente.');
    }

    protected function logAction(Emprendedor $emprendedor, string $action, string $description, array $metadata = []): void
    {
        AuditLog::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'target_type' => Emprendedor::class,
            'target_id' => $emprendedor->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $emprendedores = Emprendedor::query()
            ->with(['user', 'pickupAlly'])
            ->when($this->search !== '', function ($query) {
                $query->where('business_name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.emprendedores-approval-manager', [
            'emprendedores' => $emprendedores,
            'viewingEmprendedor' => $this->viewingEmprendedorId
                ? Emprendedor::with(['user', 'pickupAlly'])->find($this->viewingEmprendedorId)
                : null,
        ]);
    }
}
