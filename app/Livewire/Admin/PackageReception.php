<?php

namespace App\Livewire\Admin;

use App\Models\Package;
use App\Models\Warehouse;
use App\Services\HubReceptionService;
use App\Services\LogisticsResolutionResult;
use App\Services\LogisticsResolutionService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.admin')]
class PackageReception extends Component
{
    public string $trackingNumber = '';

    public ?int $warehouseId = null;

    public ?Package $package = null;

    public ?string $successMessage = null;

    public ?string $errorMessage = null;

    /**
     * Almacenes activos, para el selector de "en qué HUB se recibe
     * este paquete" (Fase 5A). Reemplaza el campo de texto libre que
     * había antes — hace falta el Warehouse real, no un texto, para
     * poder fijar current_warehouse_id.
     *
     * @var array<int, Warehouse>
     */
    public array $warehouses = [];

    public function mount(): void
    {
        $this->loadWarehouses();
    }

    protected function loadWarehouses(): void
    {
        $this->warehouses = Warehouse::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->all();
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
            ->with(['ally', 'driver', 'histories'])
            ->first();

        if (! $this->package) {
            $this->errorMessage =
                "No existe una guía con número: {$this->trackingNumber}";
        }
    }

    /**
     * Recepción/verificación interna en HUB. Reutiliza la misma
     * pantalla para los dos casos que existen hoy, según en qué
     * estado esté el paquete encontrado:
     *
     * - RECOLECTADO_VENEXPRESS -> EN_HUB: recepción de origen, desde
     *   un Aliado (Fase 5A, HubReceptionService::receiveAtWarehouse()).
     * - EN_TRANSITO_NACIONAL -> EN_HUB: recepción de una transferencia
     *   directa entre HUBs (Fase 5B-1,
     *   HubReceptionService::receiveTransferAtWarehouse()).
     *
     * Cualquier otro estado no es válido para esta pantalla.
     */
    public function receive(): void
    {
        $this->reset(['successMessage', 'errorMessage']);

        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:100'],
            'warehouseId' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where('is_active', true),
            ],
        ], [
            'trackingNumber.required' => 'Introduce el número de guía.',
            'warehouseId.required' => 'Selecciona el almacén donde se recibe el paquete.',
            'warehouseId.exists' => 'Selecciona un almacén activo.',
        ]);

        $package = Package::query()
            ->where('tracking_number', trim($this->trackingNumber))
            ->first();

        if (! $package) {
            $this->errorMessage = 'La guía no existe.';
            return;
        }

        $warehouse = Warehouse::find($this->warehouseId);

        if (! $warehouse) {
            $this->errorMessage = 'Selecciona un almacén activo.';
            return;
        }

        try {
            $received = match ($package->current_status) {
                Package::STATUS_RECOLECTADO_VENEXPRESS => app(HubReceptionService::class)->receiveAtWarehouse(
                    package: $package,
                    userId: (int) auth()->id(),
                    warehouse: $warehouse,
                ),

                Package::STATUS_EN_TRANSITO_NACIONAL => app(HubReceptionService::class)->receiveTransferAtWarehouse(
                    package: $package,
                    userId: (int) auth()->id(),
                    warehouse: $warehouse,
                ),

                default => throw new RuntimeException(
                    'Esta guía no está en un estado que permita recepción en HUB. Estado actual: '
                    .$package->statusLabel().'.'
                ),
            };

            $this->package = $received;

            $this->successMessage = $this->outcomeMessage($received, $warehouse);

            $this->warehouseId = null;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
            $this->package = $package->fresh([
                'ally',
                'driver',
                'histories',
            ]);
        }
    }

    /**
     * Mensaje que ve Admin según el resultado de la resolución
     * (re-resuelto aquí solo para presentación: es una lectura pura,
     * ya auditada sin efectos secundarios — no repite ninguna
     * decisión de negocio, esa ya la tomó y persistió el servicio).
     */
    protected function outcomeMessage(Package $received, Warehouse $warehouse): string
    {
        $resolution = app(LogisticsResolutionService::class)->resolveForPackage($received);

        return match ($resolution->status) {
            LogisticsResolutionResult::STATUS_RESOLVED => $resolution->warehouseId === $warehouse->id
                ? 'Recepción registrada. Este almacén es el destino final de este paquete.'
                : 'Recepción registrada. Este paquete debe redistribuirse hacia otro almacén '
                    .'(pendiente de programar en una fase posterior).',

            LogisticsResolutionResult::STATUS_NO_COVERAGE =>
                '⚠️ Recepción registrada, pero no hay ningún almacén con cobertura activa para el '
                .'destino de este paquete. Requiere revisión manual — configura la cobertura en Almacenes.',

            LogisticsResolutionResult::STATUS_AMBIGUOUS =>
                '⚠️ Recepción registrada, pero hay más de un almacén con cobertura activa para el '
                .'destino de este paquete. Corrige la cobertura antes de continuar.',

            LogisticsResolutionResult::STATUS_INVALID =>
                '⚠️ Recepción registrada, pero el estado/ciudad de destino de este paquete no es '
                .'válido en el catálogo. Requiere revisión manual.',

            default => 'Recepción en Hub registrada correctamente. El paquete quedó EN_HUB.',
        };
    }

    public function clear(): void
    {
        $this->reset([
            'trackingNumber',
            'warehouseId',
            'package',
            'successMessage',
            'errorMessage',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.package-reception');
    }
}
