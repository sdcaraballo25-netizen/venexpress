<?php

namespace App\Livewire\Driver;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Packages extends Component
{
    use WithPagination;

    public string $status = 'all';

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        if (! in_array($status, [
            'all',
            'pending',
            'in_progress',
            'delivered',
            'incidents',
        ], true)) {
            return;
        }

        $this->status = $status;

        $this->resetPage();
    }

    public function render()
    {
        $driver = auth()->user()->driver;

        if (! $driver) {
            abort(403, 'Tu usuario no tiene un perfil de repartidor asociado.');
        }

        $query = Package::query()
            ->where('driver_id', $driver->id)
            ->with('ally')
            ->withCount('incidents')
            ->orderByDesc('updated_at');

        if (trim($this->search) !== '') {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('destination_city', 'like', "%{$search}%");
            });
        }

        match ($this->status) {
            'pending' => $query->whereIn('current_status', [
                Package::STATUS_RECIBIDO_AGENCIA,
                Package::STATUS_RECOLECTADO_VENEXPRESS,
            ]),

            'in_progress' => $query->whereIn('current_status', [
                Package::STATUS_EN_HUB,
                Package::STATUS_EN_TRANSITO_NACIONAL,
                Package::STATUS_LISTO_RETIRO,
            ]),

            'delivered' => $query->where(
                'current_status',
                Package::STATUS_ENTREGADO
            ),

            'incidents' => $query->whereHas('incidents'),

            default => null,
        };

        return view('livewire.driver.packages', [
            'packages' => $query->paginate(10),
        ]);
    }
}