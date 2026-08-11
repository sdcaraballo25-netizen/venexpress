<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use App\Services\PackageService;
use App\Services\TariffService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use RuntimeException;

class CreatePackage extends Component
{
    // Remitente
    public string $sender_name = '';
    public string $sender_id_doc = '';
    public string $sender_phone = '';

    // Destinatario
    public string $recipient_name = '';
    public string $recipient_id_doc = '';
    public string $recipient_phone = '';

    // Ruta
    public string $origin_city = '';
    public string $destination_city = '';

    // Paquete
    public string $package_type = Package::TYPE_PAQUETE;
    public ?float $physical_weight_kg = null;
    public ?float $length_cm = null;
    public ?float $width_cm = null;
    public ?float $height_cm = null;

    // Resultado tras guardar
    public ?string $createdTrackingNumber = null;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_id_doc' => ['required', 'string', 'max:30'],
            'sender_phone' => ['required', 'string', 'max:30'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_id_doc' => ['required', 'string', 'max:30'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'origin_city' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:255'],
            'package_type' => ['required', 'in:sobre,paquete'],
            'physical_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Cotización en vivo mientras el aliado llena el formulario.
     * Devuelve null si faltan datos o no hay tarifa/tasa configurada.
     *
     * @return array{volumetric_weight_kg: float, billable_weight_kg: float, total_price_usd: float, total_price_ves: float, bcv_rate_used: float}|null
     */
    #[Computed]
    public function pricingPreview(): ?array
    {
        if (! $this->origin_city || ! $this->destination_city || ! $this->physical_weight_kg) {
            return null;
        }

        try {
            return app(TariffService::class)->calculate(
                originCity: $this->origin_city,
                destinationCity: $this->destination_city,
                physicalWeightKg: (float) $this->physical_weight_kg,
                lengthCm: $this->length_cm ? (float) $this->length_cm : null,
                widthCm: $this->width_cm ? (float) $this->width_cm : null,
                heightCm: $this->height_cm ? (float) $this->height_cm : null,
            );
        } catch (RuntimeException $e) {
            $this->addError('pricing', $e->getMessage());

            return null;
        }
    }

    public function save(PackageService $packageService): void
    {
        $this->resetErrorBag();
        $data = $this->validate();

        $ally = auth()->user()->ally;

        if (! $ally) {
            $this->addError('ally', 'Tu usuario no tiene una taquilla aliada asociada.');

            return;
        }

        try {
            $package = $packageService->createPackage(
                data: [...$data, 'ally_id' => $ally->id],
                registeredByUserId: auth()->id(),
            );
        } catch (RuntimeException $e) {
            $this->addError('pricing', $e->getMessage());

            return;
        }

        $this->createdTrackingNumber = $package->tracking_number;

        $this->reset([
            'sender_name', 'sender_id_doc', 'sender_phone',
            'recipient_name', 'recipient_id_doc', 'recipient_phone',
            'origin_city', 'destination_city',
            'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
        ]);
        $this->package_type = Package::TYPE_PAQUETE;
    }

    public function render()
    {
        return view('livewire.ally.package-create');
    }
}
