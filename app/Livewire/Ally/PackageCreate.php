<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use App\Services\PackageService;
use App\Services\TariffService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Validation\Rule;

#[Layout('layouts.ally')]
class PackageCreate extends Component
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
    public string $origin_state = '';
    public string $destination_state = '';
    public string $destination_city = '';

    // Paquete
    public string $package_type = Package::TYPE_PAQUETE;
    public ?float $physical_weight_kg = null;
    public ?float $length_cm = null;
    public ?float $width_cm = null;
    public ?float $height_cm = null;
    public bool $is_fragile = false;
    public bool $has_insurance = false;
    public ?float $declared_value_usd = null;

    // Delivery
    public bool $requires_delivery = false;
    public string $delivery_address = '';
    public string $delivery_sector = '';
    public string $delivery_reference = '';

    // Cobro
    public string $payment_method = '';
    public bool $is_cod = false;
    public ?float $cod_amount_usd = null;

    // Resultado tras registrar
    public ?string $createdTrackingNumber = null;
    public ?float $createdTotalUsd = null;
    public ?float $createdTotalVes = null;
    public ?float $createdDistanceKm = null;
    public ?float $createdDeliveryFeeUsd = null;

    /**
     * Vista previa de tarifa, recalculada en vivo mientras se llena
     * el formulario. Null si aún no hay datos suficientes o si la
     * combinación de datos no es válida (ej. ciudad no encontrada).
     *
     * @var array{
     *   billable_weight_kg: float, volumetric_weight_kg: float,
     *   fragile_surcharge_usd: float, insurance_price_usd: float,
     *   total_price_usd: float, total_price_ves: float,
     * }|null
     */
    public ?array $pricePreview = null;

    public ?string $pricePreviewError = null;

    public function mount(): void
    {
        $ally = auth()->user()->resolveAlly();

        if (! $ally) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }

        // La guía siempre se origina en la ciudad de la agencia.
        $this->origin_city = $ally->city;
        $this->origin_state = (string) ($ally->state ?? '');
    }

    protected function rules(): array
    {
        return [
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_id_doc' => ['required', 'string', 'max:30'],
            'sender_phone' => ['required', 'string', 'max:30'],

            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_id_doc' => ['required', 'string', 'max:30'],
            'recipient_phone' => ['required', 'string', 'max:30'],

            'destination_state' => ['required', 'string', Rule::in(array_keys(config('venezuela.states', [])))],
            'destination_city' => [
                'required',
                'string',
                Rule::in($this->citiesForSelectedState()),
            ],

            'requires_delivery' => ['boolean'],
            'delivery_address' => ['nullable', 'string', 'max:1000', 'required_if:requires_delivery,true'],
            'delivery_sector' => ['nullable', 'string', 'max:255', 'required_if:requires_delivery,true'],
            'delivery_reference' => ['nullable', 'string', 'max:1000'],

            'package_type' => ['required', 'in:' . implode(',', Package::TYPES)],
            'physical_weight_kg' => ['required', 'numeric', 'min:0.01'],
            'length_cm' => ['nullable', 'numeric', 'min:0'],
            'width_cm' => ['nullable', 'numeric', 'min:0'],
            'height_cm' => ['nullable', 'numeric', 'min:0'],

            'is_fragile' => ['boolean'],
            'has_insurance' => ['boolean'],
            'declared_value_usd' => ['nullable', 'required_if:has_insurance,true', 'numeric', 'min:0.01'],

            'payment_method' => [$this->is_cod ? 'nullable' : 'required', 'in:efectivo_usd,efectivo_ves,pago_movil,transferencia,zelle'],

            'is_cod' => ['boolean'],
            'cod_amount_usd' => ['nullable', 'required_if:is_cod,true', 'numeric', 'min:0.01'],
        ];
    }

    protected function messages(): array
    {
        return [
            'declared_value_usd.required_if' => 'Indica el valor declarado para asegurar el envío.',
            'cod_amount_usd.required_if' => 'Completa ciudad destino y peso para calcular el monto a cobrar.',
            'destination_state.required' => 'Selecciona el estado destino.',
            'destination_city.required' => 'Selecciona la ciudad destino.',
            'delivery_address.required_if' => 'Indica la dirección exacta de entrega.',
            'delivery_sector.required_if' => 'Indica el sector o urbanización.',
        ];
    }

    /**
     * Livewire llama esto cada vez que cambia una propiedad pública
     * con wire:model.live. Recalculamos la tarifa en vivo.
     */
    public function updated(string $property): void
    {
        if ($property === 'destination_state') {
            $this->destination_city = '';
        }

        if ($property === 'requires_delivery' && ! $this->requires_delivery) {
            $this->delivery_address = '';
            $this->delivery_sector = '';
            $this->delivery_reference = '';
        }

        if ($property === 'is_cod') {
            // Si es cobro contra entrega, no se define método de pago en taquilla.
            $this->payment_method = $this->is_cod ? '' : $this->payment_method;
        }
    }

    public function calculatePrice(TariffService $tariffService): void
    {
        $this->validateOnly('destination_state');
        $this->validateOnly('destination_city');
        $this->validateOnly('physical_weight_kg');

        $this->refreshPricePreview($tariffService);
    }

    protected function refreshPricePreview(TariffService $tariffService): void
    {
        $this->pricePreviewError = null;
        $this->pricePreview = null;

        if (empty($this->destination_city) || empty($this->physical_weight_kg)) {
            $this->cod_amount_usd = null;

            return;
        }

        try {
            $pricing = $tariffService->calculate(
                originCity: $this->origin_city,
                destinationCity: $this->destination_city,
                packageType: $this->package_type,
                physicalWeightKg: $this->physical_weight_kg,
                lengthCm: $this->length_cm,
                widthCm: $this->width_cm,
                heightCm: $this->height_cm,
                isFragile: $this->is_fragile,
                hasInsurance: $this->has_insurance,
                declaredValueUsd: $this->declared_value_usd,
                originState: $this->origin_state ?: null,
                destinationState: $this->destination_state ?: null,
                requiresDelivery: $this->requires_delivery,
            );

            $this->pricePreview = $pricing;

            // El monto a cobrar en destino (COD) es el mismo total
            // calculado; no se escribe a mano.
            if ($this->is_cod) {
                $this->cod_amount_usd = $pricing['total_price_usd'];
            }
        } catch (\Throwable $e) {
            // No interrumpimos el llenado del formulario; solo no
            // mostramos preview hasta que los datos sean válidos.
            $this->pricePreviewError = $e->getMessage();
            $this->cod_amount_usd = null;
        }
    }

    public function save(PackageService $packageService): void
    {
        $data = $this->validate();

        $ally = auth()->user()->resolveAlly();

        $package = $packageService->createPackage([
            ...$data,
            'ally_id' => $ally->id,
            'origin_city' => $this->origin_city,
            'origin_state' => $this->origin_state ?: null,
        ], auth()->id());

        $this->createdTrackingNumber = $package->tracking_number;
        $this->createdTotalUsd = (float) $package->total_price_usd;
        $this->createdTotalVes = (float) $package->total_price_ves;
        $this->createdDistanceKm = isset($package->distance_km) ? (float) $package->distance_km : ($this->pricePreview['distance_km'] ?? null);
        $this->createdDeliveryFeeUsd = isset($package->delivery_fee_usd) ? (float) $package->delivery_fee_usd : ($this->pricePreview['delivery_fee_usd'] ?? 0);

        $this->dispatch('package-created', trackingNumber: $package->tracking_number);

        $this->resetForm();
    }

    public function registerAnother(): void
    {
        $this->createdTrackingNumber = null;
        $this->createdTotalUsd = null;
        $this->createdTotalVes = null;
        $this->createdDistanceKm = null;
        $this->createdDeliveryFeeUsd = null;
    }

    protected function resetForm(): void
    {
        $ally = auth()->user()->resolveAlly();

        $this->reset([
            'sender_name', 'sender_id_doc', 'sender_phone',
            'recipient_name', 'recipient_id_doc', 'recipient_phone',
            'destination_state', 'destination_city',
            'requires_delivery', 'delivery_address', 'delivery_sector', 'delivery_reference',
            'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
            'is_fragile', 'has_insurance', 'declared_value_usd',
            'payment_method', 'is_cod', 'cod_amount_usd',
        ]);

        $this->package_type = Package::TYPE_PAQUETE;
        $this->origin_city = $ally->city;
        $this->origin_state = (string) ($ally->state ?? '');
        $this->pricePreview = null;
        $this->pricePreviewError = null;
    }

    protected function citiesForSelectedState(): array
    {
        return config('venezuela.states')[$this->destination_state] ?? [];
    }

    public function render()
    {
        return view('livewire.ally.package-create');
    }
}
