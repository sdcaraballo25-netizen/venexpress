<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * Devolución al remitente (lado Admin): busca una guía que no se pudo
 * entregar e inicia su devolución con un motivo. La cierra la agencia
 * de origen desde Ally\PackageReturns. Ver PackageService::startReturn().
 */
#[Layout('layouts.admin')]
class PackageReturns extends Component
{
    use WithPagination;

    /**
     * ?guia=... permite llegar desde Incidencias con la guía ya
     * buscada.
     */
    #[Url(as: 'guia')]
    public string $trackingNumber = '';

    public string $returnReason = '';

    public ?Package $package = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        if (trim($this->trackingNumber) !== '') {
            $this->search();
        }
    }

    public function search(): void
    {
        $this->reset(['package', 'successMessage', 'errorMessage']);

        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->errorMessage = 'Introduce el número de guía.';

            return;
        }

        $this->package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with('ally')
            ->first();

        if (! $this->package) {
            $this->errorMessage = 'La guía no existe.';
        }
    }

    public function startReturn(PackageService $packageService): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'returnReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'returnReason.required' => 'Indica el motivo de la devolución.',
            'returnReason.min' => 'Describe el motivo con un poco más de detalle.',
        ]);

        $package = Package::query()
            ->where('tracking_number', trim($this->trackingNumber))
            ->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';

            return;
        }

        try {
            $returned = $packageService->startReturn($package, (int) Auth::id(), $this->returnReason);
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        AuditLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'package.return_started',
            'target_type' => Package::class,
            'target_id' => $returned->id,
            'description' => "Inició la devolución al remitente de la guía {$returned->tracking_number}.",
            'metadata' => [
                'tracking_number' => $returned->tracking_number,
                'reason' => $returned->return_reason,
            ],
            'ip_address' => request()->ip(),
        ]);

        $this->package = $returned->load('ally');
        $this->returnReason = '';
        $this->successMessage = "Devolución iniciada. La agencia de origen ({$returned->ally?->business_name}) "
            .'la entregará al remitente cuando el paquete regrese.';
    }

    public function render()
    {
        return view('livewire.admin.package-returns', [
            'inReturn' => Package::query()
                ->where('current_status', Package::STATUS_EN_DEVOLUCION)
                ->with('ally')
                ->orderBy('return_requested_at')
                ->paginate(15, pageName: 'en_devolucion'),
            'recentlyReturned' => Package::query()
                ->where('current_status', Package::STATUS_DEVUELTO)
                ->with('ally')
                ->latest('returned_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
