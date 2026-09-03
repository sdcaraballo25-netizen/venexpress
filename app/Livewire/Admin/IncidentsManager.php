<?php

namespace App\Livewire\Admin;

use App\Models\Incident;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Incidencias')]
class IncidentsManager extends Component
{
    use WithPagination;

    public string $status = 'abierta';

    public string $search = '';

    public const STATUS_LABELS = [
        Incident::STATUS_OPEN => 'Abierta',
        Incident::STATUS_IN_PROGRESS => 'En proceso',
        Incident::STATUS_RESOLVED => 'Resuelta',
        Incident::STATUS_CLOSED => 'Cerrada',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updateStatus(int $id, string $status): void
    {
        if (! in_array($status, Incident::STATUSES, true)) {
            return;
        }

        $incident = Incident::findOrFail($id);

        $incident->update([
            'status' => $status,
            'resolved_at' => in_array(
                $status,
                [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED],
                true
            ) ? now() : null,
        ]);

        session()->flash('success', 'Incidencia actualizada.');
    }

    public function render()
    {
        $query = Incident::with(['package', 'ally', 'reportedByUser'])
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'package',
                        fn ($p) => $p->where('tracking_number', 'like', "%{$search}%")
                    );
            });
        }

        return view('livewire.admin.incidents-manager', [
            'incidents' => $query->paginate(15),
        ]);
    }
}
