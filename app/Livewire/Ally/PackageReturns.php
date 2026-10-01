<?php

namespace App\Livewire\Ally;

use App\Models\Ally;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Devolución al remitente (lado agencia de ORIGEN): lista las guías de
 * esta agencia que un admin puso en devolución y permite entregarlas
 * al remitente verificando su cédula. Ver
 * PackageService::completeReturn() y Admin\PackageReturns.
 */
#[Layout('layouts.ally')]
class PackageReturns extends Component
{
    use WithPagination;

    public ?int $selectedPackageId = null;

    public string $senderIdDoc = '';

    public ?string $message = null;

    public ?string $error = null;

    protected function ally(): Ally
    {
        $ally = Auth::user()->resolveAlly();

        if (! $ally) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }

        return $ally;
    }

    /**
     * Solo guías de esta agencia que están en devolución: una petición
     * manipulada con el id de otra guía simplemente no encuentra nada.
     */
    protected function pendingReturn(int $packageId): ?Package
    {
        return Package::query()
            ->where('ally_id', $this->ally()->id)
            ->where('current_status', Package::STATUS_EN_DEVOLUCION)
            ->find($packageId);
    }

    public function select(int $packageId): void
    {
        $this->reset(['message', 'error', 'senderIdDoc']);
        $this->resetValidation();

        $this->selectedPackageId = $this->pendingReturn($packageId)?->id;

        if (! $this->selectedPackageId) {
            $this->error = 'Esa guía ya no está pendiente de devolución.';
        }
    }

    public function cancelSelection(): void
    {
        $this->reset(['selectedPackageId', 'senderIdDoc']);
        $this->resetValidation();
    }

    public function handBack(PackageService $packageService): void
    {
        $this->reset(['message', 'error']);

        $this->validate([
            'senderIdDoc' => ['required', 'string', 'max:50'],
        ], [
            'senderIdDoc.required' => 'Indica la cédula del remitente.',
        ]);

        $package = $this->selectedPackageId ? $this->pendingReturn($this->selectedPackageId) : null;

        if (! $package) {
            $this->error = 'Esa guía ya no está pendiente de devolución.';
            $this->cancelSelection();

            return;
        }

        try {
            $returned = $packageService->completeReturn($package, (int) Auth::id(), $this->senderIdDoc, $this->ally());
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $this->message = "Guía {$returned->tracking_number} devuelta al remitente.";
        $this->cancelSelection();
    }

    public function render()
    {
        $ally = $this->ally();

        return view('livewire.ally.package-returns', [
            'pending' => Package::query()
                ->where('ally_id', $ally->id)
                ->where('current_status', Package::STATUS_EN_DEVOLUCION)
                ->orderBy('return_requested_at')
                ->paginate(15),
            'selected' => $this->selectedPackageId ? $this->pendingReturn($this->selectedPackageId) : null,
        ]);
    }
}
