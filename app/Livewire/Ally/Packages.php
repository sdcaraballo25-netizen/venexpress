<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.ally')]
class Packages extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ally = $user->resolveAlly();

        if (! $ally) {
            abort(
                403,
                'Tu usuario no tiene una agencia aliada asociada.'
            );
        }

        $query = Package::query()
            ->where('ally_id', $ally->id)
            ->when(
                $this->search !== '',
                function ($query) {
                    $search = trim($this->search);

                    $query->where(function ($query) use ($search) {
                        $query
                            ->where('tracking_number', 'like', "%{$search}%")
                            ->orWhere('sender_name', 'like', "%{$search}%")
                            ->orWhere('sender_id_doc', 'like', "%{$search}%")
                            ->orWhere('recipient_name', 'like', "%{$search}%")
                            ->orWhere('recipient_id_doc', 'like', "%{$search}%");
                    });
                }
            )
            ->when(
                $this->status !== '',
                fn ($query) => $query->where(
                    'current_status',
                    $this->status
                )
            )
            ->latest('created_at');

        $packages = $query->paginate(15);

        return view(
            'livewire.ally.packages',
            [
                'packages' => $packages,
            ]
        );
    }

    public function statusLabel(?string $status): string
    {
        return match ($status) {
            Package::STATUS_RECIBIDO_AGENCIA => 'Recibido en agencia',
            Package::STATUS_RECOLECTADO_VENEXPRESS => 'Recolectado',
            Package::STATUS_EN_HUB => 'En hub',
            Package::STATUS_EN_TRANSITO_NACIONAL => 'En tránsito nacional',
            Package::STATUS_LISTO_RETIRO => 'Listo para retiro',
            Package::STATUS_ENTREGADO => 'Entregado',
            default => $status ?: 'Sin estado',
        };
    }
}

