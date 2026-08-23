<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Gestión de Aliados')]
class AlliesManager extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Aprobar un aliado.
     */
    public function approve(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        session()->flash('success', 'El aliado fue aprobado correctamente.');
    }

    /**
     * Rechazar un aliado.
     */
    public function reject(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_REJECTED,
        ]);

        session()->flash('success', 'El aliado fue rechazado.');
    }

    /**
     * Suspender un aliado.
     */
    public function suspend(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_SUSPENDED,
        ]);

        session()->flash('success', 'El aliado fue suspendido.');
    }

    /**
     * Reactivar un aliado suspendido.
     */
    public function activate(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        session()->flash('success', 'El aliado fue activado nuevamente.');
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
        $allies = Ally::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('business_name', 'like', '%' . $this->search . '%')
                        ->orWhere('rif', 'like', '%' . $this->search . '%')
                        ->orWhere('city', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.allies-manager', [
            'allies' => $allies,
        ]);
    }
}