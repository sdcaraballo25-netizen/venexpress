<?php

namespace App\Livewire\Ally;

use App\Models\Pedido;
use App\Models\RateMatrix;
use App\Services\PedidoService;
use App\Services\TariffService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

/**
 * Punto de taquilla para generar la guía real de un pedido del
 * marketplace, una vez el emprendedor lo marcó como pagado
 * (Pedido::STATUS_PAGADO) y lleva el paquete físicamente a la agencia.
 *
 * A diferencia de Ally\PackageCreate (registro normal de una guía),
 * remitente/destinatario/destino ya vienen fijos del Pedido — lo único
 * que taquilla aporta es lo que solo se puede verificar con el
 * paquete en mano: peso real, tamaño, si es delicado y si lleva
 * seguro. Así se evita que la guía se genere dos veces (una al
 * "confirmar" y otra al registrar en taquilla) y que se use el peso
 * autodeclarado del catálogo sin verificar.
 */
#[Layout('layouts.ally')]
class EmprendedorPedidos extends Component
{
    public string $pedidoIdInput = '';

    public ?Pedido $pedido = null;

    public ?string $searchError = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    public ?int $createdPedidoId = null;

    public ?int $createdPackageId = null;

    public ?string $createdTrackingNumber = null;

    public ?float $createdTotalUsd = null;

    public ?float $createdTotalVes = null;

    // Datos que solo se conocen con el paquete físico en mano.
    public ?float $physical_weight_kg = null;

    public ?float $length_cm = null;

    public ?float $width_cm = null;

    public ?float $height_cm = null;

    public bool $is_fragile = false;

    public bool $has_insurance = false;

    public ?float $declared_value_usd = null;

    /**
     * @var array{
     *   billable_weight_kg: float, total_price_usd: float, total_price_ves: float,
     * }|null
     */
    public ?array $pricePreview = null;

    protected function ally()
    {
        $ally = Auth::user()->resolveAlly();

        if (! $ally) {
            abort(403, 'Tu usuario no tiene una agencia aliada asociada.');
        }

        return $ally;
    }

    public function buscar(): void
    {
        $this->reset([
            'pedido', 'searchError', 'successMessage', 'errorMessage',
            'createdPedidoId', 'createdPackageId', 'createdTrackingNumber', 'createdTotalUsd', 'createdTotalVes',
            'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
            'is_fragile', 'has_insurance', 'declared_value_usd', 'pricePreview',
        ]);

        $id = trim($this->pedidoIdInput);

        if ($id === '' || ! ctype_digit($id)) {
            $this->searchError = 'Ingresa un número de pedido válido.';

            return;
        }

        $pedido = Pedido::with(['items.producto', 'emprendedor.pickupAlly'])->find((int) $id);

        if (! $pedido) {
            $this->searchError = 'No existe ningún pedido con ese número.';

            return;
        }

        if ($pedido->emprendedor->pickupAlly?->id !== $this->ally()->id) {
            $this->searchError = 'Este pedido no pertenece a tu agencia.';

            return;
        }

        if ($pedido->status !== Pedido::STATUS_PAGADO) {
            $this->searchError = $pedido->status === Pedido::STATUS_CONFIRMADO
                ? 'Este pedido ya tiene una guía generada.'
                : 'Este pedido todavía no fue confirmado como pagado por el emprendedor.';

            return;
        }

        $this->pedido = $pedido;
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
            'is_fragile', 'has_insurance', 'declared_value_usd',
        ], true)) {
            $this->recalcularPrecio();
        }
    }

    protected function recalcularPrecio(): void
    {
        $this->pricePreview = null;

        if (! $this->pedido || ! $this->physical_weight_kg || $this->physical_weight_kg <= 0) {
            return;
        }

        $ally = $this->ally();
        $pickupAlly = $this->pedido->emprendedor->pickupAlly;

        try {
            $pricing = app(TariffService::class)->calculate(
                originCity: $pickupAlly->city,
                destinationCity: $this->pedido->destino_ciudad,
                physicalWeightKg: (float) $this->physical_weight_kg,
                lengthCm: $this->length_cm,
                widthCm: $this->width_cm,
                heightCm: $this->height_cm,
                isFragile: $this->is_fragile,
                hasInsurance: $this->has_insurance,
                declaredValueUsd: $this->declared_value_usd,
                originState: $pickupAlly->state,
                destinationState: $this->pedido->destino_estado,
                requiresDelivery: true,
                discountPercentage: (float) (RateMatrix::current()?->emprendedor_discount_percentage ?? 0.0),
            );

            $this->pricePreview = [
                'billable_weight_kg' => $pricing['billable_weight_kg'],
                'total_price_usd' => $pricing['total_price_usd'],
                'total_price_ves' => $pricing['total_price_ves'],
            ];
        } catch (\Throwable) {
            // No interrumpimos el llenado del formulario (ej. mientras
            // se están escribiendo las dimensiones una por una, con
            // solo largo y ancho todavía sin el alto): simplemente no
            // se muestra preview hasta que los datos sean válidos.
            $this->pricePreview = null;
        }
    }

    public function generarGuia(PedidoService $pedidoService): void
    {
        $this->successMessage = null;
        $this->errorMessage = null;

        if (! $this->pedido) {
            return;
        }

        $this->validate([
            'physical_weight_kg' => ['required', 'numeric', 'min:0.01'],
            // TariffService exige las 3 dimensiones juntas o ninguna
            // para calcular peso volumétrico: required_with evita que
            // llegue solo una o dos y reviente con un error de servidor.
            'length_cm' => ['nullable', 'numeric', 'min:0', 'required_with:width_cm,height_cm'],
            'width_cm' => ['nullable', 'numeric', 'min:0', 'required_with:length_cm,height_cm'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'required_with:length_cm,width_cm'],
            'is_fragile' => ['boolean'],
            'has_insurance' => ['boolean'],
            'declared_value_usd' => ['nullable', 'required_if:has_insurance,true', 'numeric', 'min:0.01'],
        ], [
            'length_cm.required_with' => 'Si indicas una dimensión, indica las 3 (largo, ancho y alto).',
            'width_cm.required_with' => 'Si indicas una dimensión, indica las 3 (largo, ancho y alto).',
            'height_cm.required_with' => 'Si indicas una dimensión, indica las 3 (largo, ancho y alto).',
        ]);

        try {
            $pedidoId = $this->pedido->id;

            $package = $pedidoService->registrarGuia($this->pedido, [
                'physical_weight_kg' => (float) $this->physical_weight_kg,
                'length_cm' => $this->length_cm,
                'width_cm' => $this->width_cm,
                'height_cm' => $this->height_cm,
                'is_fragile' => $this->is_fragile,
                'has_insurance' => $this->has_insurance,
                'declared_value_usd' => $this->declared_value_usd,
            ], Auth::id(), $this->ally());

            $this->createdPedidoId = $pedidoId;
            $this->createdPackageId = $package->id;
            $this->createdTrackingNumber = $package->tracking_number;
            $this->createdTotalUsd = (float) $package->total_price_usd;
            $this->createdTotalVes = (float) $package->total_price_ves;
            $this->successMessage = "Pedido #{$pedidoId} confirmado. Guía generada: {$package->tracking_number}.";
            $this->pedido = null;
            $this->reset([
                'physical_weight_kg', 'length_cm', 'width_cm', 'height_cm',
                'is_fragile', 'has_insurance', 'declared_value_usd', 'pricePreview', 'pedidoIdInput',
            ]);
        } catch (RuntimeException|\InvalidArgumentException $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.ally.emprendedor-pedidos');
    }
}
