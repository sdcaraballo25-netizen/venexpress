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

    public function approve(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);
        $previousStatus = $emprendedor->status;

        $emprendedor->update([
            'status' => Emprendedor::STATUS_ACTIVE,
        ]);

        $this->logAction(
            $emprendedor,
            'emprendedor.approved',
            "Aprobó al emprendedor {$emprendedor->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Emprendedor::STATUS_ACTIVE]
        );

        $emprendedor->user?->notify(new AccountApproved('Emprendedor'));

        session()->flash('success', 'El emprendedor fue aprobado correctamente.');
    }

    public function reject(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);
        $previousStatus = $emprendedor->status;

        $emprendedor->update([
            'status' => Emprendedor::STATUS_REJECTED,
        ]);

        $emprendedor->user?->tokens()->delete();

        $this->logAction(
            $emprendedor,
            'emprendedor.rejected',
            "Rechazó al emprendedor {$emprendedor->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Emprendedor::STATUS_REJECTED]
        );

        $emprendedor->user?->notify(new AccountRejected('Emprendedor'));

        session()->flash('success', 'El emprendedor fue rechazado.');
    }

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

    public function activate(int $emprendedorId): void
    {
        $emprendedor = Emprendedor::findOrFail($emprendedorId);
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
        ]);
    }
}
