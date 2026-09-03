<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Bitácora de auditoría: muestra los AuditLog generados por acciones
 * administrativas sensibles (creación/edición/borrado de usuarios,
 * cambios de estado, etc.). Solo lectura.
 */
#[Layout('layouts.admin')]
#[Title('Bitácora de Auditoría')]
class AuditLogViewer extends Component
{
    use WithPagination;

    public string $search = '';

    public string $actionFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::query()
            ->with('actor')
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('description', 'like', $term)
                        ->orWhereHas('actor', fn ($a) => $a->where('name', 'like', $term));
                });
            })
            ->when($this->actionFilter !== '', fn ($query) => $query->where('action', $this->actionFilter))
            ->latest()
            ->paginate(20);

        $actions = AuditLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('livewire.admin.audit-log-viewer', [
            'logs' => $logs,
            'actions' => $actions,
        ]);
    }
}
