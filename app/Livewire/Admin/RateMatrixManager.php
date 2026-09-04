<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Package;
use App\Models\RateMatrix;
use App\Services\TariffService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Dashboard de Tarifas:
 * - Muestra los precios vigentes (base, por kg, por km, sobre, frágil, seguro).
 * - Permite editarlos, exigiendo la contraseña del admin en cada guardado
 *   (regla nativa `current_password`, sin depender de sesión ni timeout).
 * - Incluye un simulador de venta (usa TariffService::calculate() en modo
 *   lectura, no persiste nada) para verificar cómo queda un precio antes
 *   de aplicar un cambio de tarifa.
 */
#[Layout('layouts.admin')]
#[Title('Tarifas')]
class RateMatrixManager extends Component
{
    // ---- Tarifa vigente / formulario de edición ----

    public ?int $rateMatrixId = null;

    public string $base_price_usd = '0';

    public string $price_per_kg_usd = '';

    public string $price_per_km_usd = '';

    public string $envelope_price_usd = '';

    public string $fragile_surcharge_usd = '';

    public string $insurance_percentage = '';
    public string $delivery_price_usd = '0';

    public bool $editing = false;

    /**
     * Se pide en cada guardado, nunca se recuerda entre ediciones.
     */
    public string $confirm_password = '';

    // ---- Simulador de venta ----

    public string $sim_package_type = Package::TYPE_PAQUETE;

    public string $sim_origin_city = '';

    public string $sim_destination_city = '';

    public string $sim_physical_weight_kg = '';

    public string $sim_length_cm = '';

    public string $sim_width_cm = '';

    public string $sim_height_cm = '';

    public bool $sim_is_fragile = false;

    public bool $sim_has_insurance = false;

    public string $sim_declared_value_usd = '';

    public ?array $simulationResult = null;

    public ?string $simulationError = null;

    protected function rules(): array
    {
        return [
            'base_price_usd' => ['required', 'numeric', 'min:0'],
            'price_per_kg_usd' => ['required', 'numeric', 'min:0'],
            'price_per_km_usd' => ['required', 'numeric', 'min:0'],
            'envelope_price_usd' => ['required', 'numeric', 'min:0'],
            'fragile_surcharge_usd' => ['required', 'numeric', 'min:0'],
            'insurance_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'delivery_price_usd' => ['required', 'numeric', 'min:0'],
            // Regla nativa de Laravel: valida contra el hash del usuario autenticado.
            'confirm_password' => ['required', 'string', 'current_password'],
        ];
    }

    protected $messages = [
        'base_price_usd.required' => 'Ingresa el precio base.',
        'price_per_kg_usd.required' => 'Ingresa el precio por kg.',
        'price_per_km_usd.required' => 'Ingresa el precio por km.',
        'envelope_price_usd.required' => 'Ingresa el precio del sobre.',
        'fragile_surcharge_usd.required' => 'Ingresa el recargo por frágil.',
        'insurance_percentage.required' => 'Ingresa el porcentaje del seguro.',
        'delivery_price_usd.required' => 'Ingresa el precio del delivery.',
        'insurance_percentage.max' => 'El porcentaje del seguro no puede superar 100.',
        'confirm_password.required' => 'Debes ingresar tu contraseña para guardar los cambios.',
        'confirm_password.current_password' => 'La contraseña ingresada es incorrecta.',
    ];

    public function mount(): void
    {
        $current = RateMatrix::current();

        if ($current) {
            $this->rateMatrixId = $current->id;
            $this->base_price_usd = (string) $current->base_price_usd;
            $this->price_per_kg_usd = (string) $current->price_per_kg_usd;
            $this->price_per_km_usd = (string) $current->price_per_km_usd;
            $this->envelope_price_usd = (string) $current->envelope_price_usd;
            $this->fragile_surcharge_usd = (string) $current->fragile_surcharge_usd;
            $this->insurance_percentage = (string) $current->insurance_percentage;
            $this->delivery_price_usd = (string) $current->delivery_price_usd;
        }
    }

    public function startEditing(): void
    {
        $this->editing = true;
        $this->confirm_password = '';
        $this->resetErrorBag();
    }

    public function cancelEditing(): void
    {
        $this->mount();
        $this->editing = false;
        $this->confirm_password = '';
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'base_price_usd' => $this->base_price_usd,
            'price_per_kg_usd' => $this->price_per_kg_usd,
            'price_per_km_usd' => $this->price_per_km_usd,
            'envelope_price_usd' => $this->envelope_price_usd,
            'fragile_surcharge_usd' => $this->fragile_surcharge_usd,
            'insurance_percentage' => $this->insurance_percentage,
            'delivery_price_usd' => $this->delivery_price_usd,
        ];

        $previous = $this->rateMatrixId
            ? RateMatrix::find($this->rateMatrixId)
            : null;

        /*
         * Las tarifas se versionan: cada guardado crea una fila nueva
         * en vez de sobrescribir la vigente. RateMatrix::current()
         * sigue devolviendo automáticamente la más reciente (por
         * updated_at), pero así conservamos el historial completo de
         * qué tarifa estuvo vigente en cada periodo, en vez de perder
         * los valores anteriores en cada edición.
         */
        $rateMatrix = RateMatrix::create($data);
        $this->rateMatrixId = $rateMatrix->id;

        AuditLog::create([
            'actor_user_id' => auth()->id(),
            'action' => 'rate_matrix.updated',
            'target_type' => RateMatrix::class,
            'target_id' => $rateMatrix->id,
            'description' => 'Actualizó las tarifas de la plataforma.',
            'metadata' => [
                'previous' => $previous?->only([
                    'base_price_usd',
                    'price_per_kg_usd',
                    'price_per_km_usd',
                    'envelope_price_usd',
                    'fragile_surcharge_usd',
                    'insurance_percentage',
                    'delivery_price_usd',
                ]),
                'new' => $data,
            ],
            'ip_address' => request()?->ip(),
        ]);

        $this->confirm_password = '';
        $this->editing = false;
        session()->flash('success', 'Tarifas actualizadas correctamente. Se guardó como una nueva versión de tarifa.');
    }

    /**
     * Simula una venta con TariffService::calculate() sin persistir nada.
     * Útil para que el admin verifique cómo queda un precio antes (o
     * después) de guardar un cambio de tarifa.
     */
    public function simulate(TariffService $tariffService): void
    {
        $this->simulationResult = null;
        $this->simulationError = null;

        $this->validate([
            'sim_package_type' => ['required', 'in:' . implode(',', Package::TYPES)],
            'sim_origin_city' => ['required', 'string'],
            'sim_destination_city' => ['required', 'string', 'different:sim_origin_city'],
            'sim_physical_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'sim_length_cm' => ['nullable', 'numeric', 'min:0'],
            'sim_width_cm' => ['nullable', 'numeric', 'min:0'],
            'sim_height_cm' => ['nullable', 'numeric', 'min:0'],
            'sim_declared_value_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $this->simulationResult = $tariffService->calculate(
                originCity: $this->sim_origin_city,
                destinationCity: $this->sim_destination_city,
                packageType: $this->sim_package_type,
                physicalWeightKg: (float) ($this->sim_physical_weight_kg ?: 0),
                lengthCm: $this->sim_length_cm !== '' ? (float) $this->sim_length_cm : null,
                widthCm: $this->sim_width_cm !== '' ? (float) $this->sim_width_cm : null,
                heightCm: $this->sim_height_cm !== '' ? (float) $this->sim_height_cm : null,
                isFragile: $this->sim_is_fragile,
                hasInsurance: $this->sim_has_insurance,
                declaredValueUsd: $this->sim_declared_value_usd !== '' ? (float) $this->sim_declared_value_usd : null,
            );
        } catch (RuntimeException $e) {
            $this->simulationError = $e->getMessage();
        }
    }

    public function resetSimulation(): void
    {
        $this->reset([
            'sim_package_type',
            'sim_origin_city',
            'sim_destination_city',
            'sim_physical_weight_kg',
            'sim_length_cm',
            'sim_width_cm',
            'sim_height_cm',
            'sim_is_fragile',
            'sim_has_insurance',
            'sim_declared_value_usd',
            'simulationResult',
            'simulationError',
        ]);
        $this->sim_package_type = Package::TYPE_PAQUETE;
    }

    public function render()
    {
        return view('livewire.admin.rate-matrix-manager');
    }
}
