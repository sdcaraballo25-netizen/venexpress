<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\Package;
use App\Services\PackageModalityService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;

/**
 * Admin cambia la modalidad de destino final de una guía (domicilio
 * <-> retiro en almacén o agencia) antes de que salga a reparto. Ver
 * PackageModalityService.
 */
#[Layout('layouts.admin')]
#[Title('Cambiar modalidad')]
class PackageModality extends Component
{
    #[Url(as: 'guia')]
    public string $trackingNumber = '';

    public ?Package $package = null;

    public string $modality = '';

    public string $deliveryAddress = '';

    public string $deliverySector = '';

    public string $deliveryReference = '';

    public ?int $pickupAllyId = null;

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

        $package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with(['pickupAlly', 'currentWarehouse'])
            ->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';

            return;
        }

        $this->loadForm($package);
    }

    protected function loadForm(Package $package): void
    {
        $this->package = $package;
        $this->modality = PackageModalityService::modalityOf($package);
        $this->deliveryAddress = (string) $package->delivery_address;
        $this->deliverySector = (string) $package->delivery_sector;
        $this->deliveryReference = (string) $package->delivery_reference;
        $this->pickupAllyId = $package->pickup_ally_id;
    }

    public function save(PackageModalityService $service): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'modality' => ['required', 'in:'.implode(',', [
                PackageModalityService::MODALITY_DELIVERY,
                PackageModalityService::MODALITY_HUB,
                PackageModalityService::MODALITY_ALLY,
            ])],
            'deliveryAddress' => [$this->modality === PackageModalityService::MODALITY_DELIVERY ? 'required' : 'nullable', 'string', 'max:500'],
            'deliverySector' => ['nullable', 'string', 'max:150'],
            'deliveryReference' => ['nullable', 'string', 'max:500'],
            'pickupAllyId' => [$this->modality === PackageModalityService::MODALITY_ALLY ? 'required' : 'nullable', 'integer'],
        ], [
            'deliveryAddress.required' => 'Indica la dirección de entrega.',
            'pickupAllyId.required' => 'Elige la agencia de retiro.',
        ]);

        $package = Package::query()->where('tracking_number', trim($this->trackingNumber))->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';

            return;
        }

        try {
            $changed = $service->change($package, [
                'modality' => $this->modality,
                'delivery_address' => $this->deliveryAddress,
                'delivery_sector' => $this->deliverySector,
                'delivery_reference' => $this->deliveryReference,
                'pickup_ally_id' => $this->pickupAllyId,
            ], (int) Auth::id());
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        $this->loadForm($changed->load(['pickupAlly', 'currentWarehouse']));
        $this->successMessage = 'Modalidad actualizada: '.$service->label($changed)
            .'. Estado actual: '.$changed->statusLabel().'.';
    }

    public function render(PackageModalityService $service)
    {
        $pickupAllies = $this->package && $this->modality === PackageModalityService::MODALITY_ALLY
            ? Ally::query()
                ->verifiedDestinations()
                ->where('state', $this->package->destination_state)
                ->orderBy('business_name')
                ->get(['id', 'business_name', 'city'])
            : collect();

        return view('livewire.admin.package-modality', [
            'pickupAllies' => $pickupAllies,
            'blockedReason' => $this->package ? $service->blockedReason($this->package) : null,
            'currentLabel' => $this->package ? $service->label($this->package) : null,
        ]);
    }
}
