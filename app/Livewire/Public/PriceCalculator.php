<?php

namespace App\Livewire\Public;

use App\Models\Package;
use App\Services\TariffService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Calculadora pública de precio de envío.
 *
 * Vive fuera de cualquier middleware de auth: cualquier visitante
 * de la web puede simular cuánto costaría su envío antes de
 * acercarse a una agencia aliada. Usa el mismo TariffService que
 * PackageCreate para que el precio mostrado sea siempre real.
 */
#[Layout('layouts.public', ['title' => 'Calcula tu envío — Venexpress'])]
class PriceCalculator extends Component
{
    // Ruta
    public string $origin_state = '';
    public string $origin_city = '';
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
    public bool $requires_delivery = false;

    /**
     * Resultado del último cálculo exitoso.
     *
     * @var array{
     *   billable_weight_kg: float, distance_km: int,
     *   fragile_surcharge_usd: float, insurance_price_usd: float,
     *   delivery_fee_usd: float, total_price_usd: float,
     *   total_price_ves: float, bcv_rate_used: float,
     * }|null
     */
    public ?array $result = null;

    public ?string $errorMessage = null;

    /**
     * Campos que, al cambiar, disparan un recálculo automático.
     */
    protected array $pricingFields = [
        'origin_state', 'origin_city', 'destination_state', 'destination_city',
        'package_type', 'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
        'is_fragile', 'has_insurance', 'declared_value_usd', 'requires_delivery',
    ];

    public function updated(string $property): void
    {
        if ($property === 'origin_state') {
            $this->origin_city = '';
        }

        if ($property === 'destination_state') {
            $this->destination_city = '';
        }

        if (in_array($property, $this->pricingFields, true)) {
            $this->calculate();
        }
    }

    /**
     * Ciudades disponibles para el estado de origen seleccionado.
     */
    #[Computed]
    public function originCities(): array
    {
        return config('venezuela.states')[$this->origin_state] ?? [];
    }

    /**
     * Ciudades disponibles para el estado de destino seleccionado.
     */
    #[Computed]
    public function destinationCities(): array
    {
        return config('venezuela.states')[$this->destination_state] ?? [];
    }

    public function calculate(): void
    {
        $this->result = null;
        $this->errorMessage = null;

        if (
            $this->origin_state === '' || $this->origin_city === ''
            || $this->destination_state === '' || $this->destination_city === ''
        ) {
            return;
        }

        if ($this->package_type === Package::TYPE_PAQUETE && ! $this->physical_weight_kg) {
            return;
        }

        try {
            /** @var TariffService $tariffService */
            $tariffService = app(TariffService::class);

            $calculated = $tariffService->calculate(
                originCity: $this->origin_city,
                destinationCity: $this->destination_city,
                packageType: $this->package_type,
                physicalWeightKg: (float) ($this->physical_weight_kg ?? 0),
                lengthCm: $this->length_cm,
                widthCm: $this->width_cm,
                heightCm: $this->height_cm,
                isFragile: $this->is_fragile,
                hasInsurance: $this->has_insurance,
                declaredValueUsd: $this->declared_value_usd,
                originState: $this->origin_state,
                destinationState: $this->destination_state,
                requiresDelivery: $this->requires_delivery,
            );

            $this->result = $calculated;
        } catch (\Throwable $e) {
            $this->errorMessage = 'No pudimos calcular el precio para esa combinación. '
                . 'Verifica los datos o intenta nuevamente en unos segundos.';
        }
    }

    public function render()
    {
        return view('public.price-calculator');
    }
}
