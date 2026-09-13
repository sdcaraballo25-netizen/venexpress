<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.ally')]
class PackageDetail extends Component
{
    public Package $package;

    public function mount(int $packageId): void
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

        $this->package = Package::query()
            ->where('ally_id', $ally->id)
            ->with([
                'driver.user',
                'histories' => fn ($query) => $query->latest('created_at'),
            ])
            ->findOrFail($packageId);
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

    public function render()
    {
        return view('livewire.ally.package-detail');
    }
}
