<?php

namespace App\Livewire\Almacen;

use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Warehouse;
use App\Services\DeliveryAssignmentService;
use App\Services\DestinationReceptionService;
use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

/**
 * Dashboard de personal de almacén:
 *
 * - Lista las paradas de rutas HUB Distribución que tienen a este
 *   almacén como destino, separadas en pendientes y ya recibidas.
 * - Permite escanear la llegada de un paquete, lo que lo recibe
 *   físicamente en el almacén (EN_TRANSITO_NACIONAL -> LISTO_RETIRO, o
 *   PENDIENTE_ENTREGA si es a domicilio; mismo mecanismo que
 *   Ally\PackageReception usa para agencias, vía
 *   DestinationReceptionService).
 * - Permite despachar un paquete ya recibido: a un cliente que lo
 *   retira en persona (LISTO_RETIRO, mismo flujo que
 *   Ally\PackagePickup), o a un repartidor con una ruta de reparto en
 *   curso desde este almacén hacia esa ciudad (PENDIENTE_ENTREGA, mismo
 *   flujo que Admin\DriverAssignment, vía DeliveryAssignmentService).
 */
#[Layout('layouts.almacen')]
#[Title('Almacén')]
class Dashboard extends Component
{
    /**
     * Ya recibidos en el almacén y listos para salir: retiro en persona
     * (LISTO_RETIRO) o entrega a domicilio (PENDIENTE_ENTREGA).
     */
    private const DISPATCHABLE_STATUSES = [
        Package::STATUS_LISTO_RETIRO,
        Package::STATUS_PENDIENTE_ENTREGA,
    ];

    public string $trackingNumber = '';

    public ?string $scanSuccess = null;

    public ?string $scanError = null;

    // Despacho.
    public string $dispatchTrackingNumber = '';

    public ?Package $dispatchPackage = null;

    public string $recipientIdDoc = '';

    public ?string $dispatchSuccess = null;

    public ?string $dispatchError = null;

    protected function warehouse(): ?Warehouse
    {
        return Auth::user()->warehouse;
    }

    /**
     * Mismo criterio de coincidencia por texto que Ally\PackageReception::
     * belongsToDestinationAgency() usa para agencias — limitación ya
     * existente (no hay FK directo entre Package y su destino).
     */
    protected function belongsToWarehouse(Package $package, Warehouse $warehouse): bool
    {
        $packageCity = mb_strtolower(trim((string) $package->destination_city));
        $packageState = mb_strtolower(trim((string) $package->destination_state));
        $warehouseCity = mb_strtolower(trim((string) $warehouse->city));
        $warehouseState = mb_strtolower(trim((string) $warehouse->state));

        if ($packageCity === '' || $warehouseCity === '') {
            return false;
        }

        return $packageCity === $warehouseCity && $packageState === $warehouseState;
    }

    /**
     * Entrada única para el lector QR de la cámara: decide si la
     * guía escaneada corresponde a una llegada (todavía no recibida
     * en este almacén) o a un despacho (ya LISTO_RETIRO), y dispara
     * la acción correspondiente. Mismo patrón que Driver\Scanner::
     * scan(), llamado desde JS vía $wire.scanGuide(...).
     */
    public function scanGuide(string $code, DestinationReceptionService $receptionService): void
    {
        $code = trim($code);

        if ($code === '') {
            return;
        }

        $package = Package::where('tracking_number', $code)->first();

        if (! $package) {
            $this->scanSuccess = null;
            $this->scanError = "No se encontró ningún paquete con la guía {$code}.";

            return;
        }

        if (in_array($package->current_status, self::DISPATCHABLE_STATUSES, true)) {
            $this->dispatchTrackingNumber = $code;
            $this->searchDispatch();

            return;
        }

        $this->trackingNumber = $code;
        $this->scanArrival($receptionService);
    }

    public function scanArrival(DestinationReceptionService $receptionService): void
    {
        $this->scanSuccess = null;
        $this->scanError = null;

        $trackingNumber = trim($this->trackingNumber);

        if ($trackingNumber === '') {
            return;
        }

        $warehouse = $this->warehouse();

        if (! $warehouse) {
            $this->scanError = 'Tu usuario no tiene un almacén asignado.';

            return;
        }

        $package = Package::where('tracking_number', $trackingNumber)->first();

        if (! $package) {
            $this->scanError = 'No se encontró ningún paquete con esa guía.';

            return;
        }

        if (! $this->belongsToWarehouse($package, $warehouse)) {
            $this->scanError = 'Este paquete no tiene como destino este almacén.';

            return;
        }

        $stop = RouteStop::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('status', RouteStop::STATUS_PENDING)
            ->whereHas('route', function ($query) {
                $query->where('route_type', Route::TYPE_HUB_DISTRIBUTION)
                    ->where('status', Route::STATUS_IN_PROGRESS);
            })
            ->latest('id')
            ->first();

        try {
            // Una sola transacción: si la recepción falla (p. ej. el
            // paquete no está EN_TRANSITO_NACIONAL), no queda nada a
            // medias — antes driver_id se borraba ANTES de validar y
            // fuera de cualquier transacción.
            DB::transaction(function () use ($receptionService, $package, $warehouse, $stop) {
                $received = $receptionService->receive(
                    package: $package,
                    userId: (int) Auth::id(),
                    destinationLocation: 'Almacén '.$warehouse->name,
                    routeStopId: $stop?->id,
                );

                // Recibido: libera la custodia del driver de HUB que lo
                // trajo (sin esto, DeliveryAssignmentService::assign()
                // rechazaría asignarlo a otro repartidor) y deja
                // constancia del HUB donde quedó físicamente.
                $received->forceFill([
                    'driver_id' => null,
                    'current_warehouse_id' => $warehouse->id,
                ])->save();

                if ($stop && $stop->status === RouteStop::STATUS_PENDING) {
                    $stop->update(['status' => RouteStop::STATUS_VISITED, 'visited_at' => now()]);
                }
            });

            $this->scanSuccess = "Guía {$trackingNumber} recibida en {$warehouse->name}. "
                .($package->requires_delivery
                    ? 'Quedó pendiente de entrega a domicilio: asígnala a un repartidor.'
                    : 'Ya está lista para entregar.');
            $this->trackingNumber = '';
        } catch (RuntimeException $e) {
            $this->scanError = $e->getMessage();
        }
    }

    /**
     * Busca un paquete ya recibido (LISTO_RETIRO o PENDIENTE_ENTREGA)
     * para despacharlo desde este almacén (a cliente o a repartidor).
     */
    public function searchDispatch(): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;
        $this->dispatchPackage = null;
        $this->recipientIdDoc = '';

        $trackingNumber = trim($this->dispatchTrackingNumber);

        if ($trackingNumber === '') {
            return;
        }

        $warehouse = $this->warehouse();

        if (! $warehouse) {
            $this->dispatchError = 'Tu usuario no tiene un almacén asignado.';

            return;
        }

        $package = Package::where('tracking_number', $trackingNumber)->first();

        if (! $package) {
            $this->dispatchError = 'No se encontró ningún paquete con esa guía.';

            return;
        }

        if (! in_array($package->current_status, self::DISPATCHABLE_STATUSES, true)) {
            $this->dispatchError = 'Esta guía todavía no está lista para despacho. Estado actual: '.$package->statusLabel().'.';

            return;
        }

        if (! $this->belongsToWarehouse($package, $warehouse)) {
            $this->dispatchError = 'Este paquete no tiene como destino este almacén.';

            return;
        }

        $this->dispatchPackage = $package;
    }

    /**
     * Entrega en persona a quien retira el paquete en el almacén.
     * Mismo flujo que Ally\PackagePickup::deliver().
     */
    public function deliverToClient(PackageService $packageService): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;

        $this->validate(['recipientIdDoc' => ['required', 'string', 'max:50']]);

        try {
            $warehouse = $this->warehouse();

            if (! $warehouse) {
                throw new RuntimeException('Tu usuario no tiene un almacén asignado.');
            }

            $package = Package::where('tracking_number', trim($this->dispatchTrackingNumber))
                ->where('current_status', Package::STATUS_LISTO_RETIRO)
                ->firstOrFail();

            if (! $this->belongsToWarehouse($package, $warehouse)) {
                throw new RuntimeException('Este paquete no tiene como destino este almacén.');
            }

            if ($package->requires_delivery) {
                throw new RuntimeException('Este envío requiere entrega a domicilio; no puede retirarse en el almacén.');
            }

            if (trim($package->recipient_id_doc) !== trim($this->recipientIdDoc)) {
                throw new RuntimeException('El documento del receptor no coincide.');
            }

            // completeAgencyPickup() (no changeStatus()) también registra
            // el cobro COD y delivery_completed_at, igual que en
            // Ally\PackagePickup.
            $this->dispatchPackage = $packageService->completeAgencyPickup(
                $package,
                (int) Auth::id(),
                $this->recipientIdDoc,
                'Retiro confirmado en almacén '.($warehouse?->name ?? ''),
                'Almacén '.($warehouse?->name ?? ''),
            );

            $this->dispatchSuccess = 'Retiro confirmado. El paquete quedó ENTREGADO.';
            $this->recipientIdDoc = '';
        } catch (RuntimeException $e) {
            $this->dispatchError = $e->getMessage();
        }
    }

    /**
     * Rutas de reparto en curso hacia la ciudad del paquete encontrado
     * que salen de este almacén — la misma regla de zona que aplica
     * DeliveryAssignmentService::assign().
     */
    #[Computed]
    public function availableDeliveryRoutes()
    {
        $warehouse = $this->warehouse();

        if (! $this->dispatchPackage || ! $warehouse) {
            return collect();
        }

        $assignmentService = app(DeliveryAssignmentService::class);

        return Route::query()
            ->with('driver.user')
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->whereNotNull('driver_id')
            ->where('route_type', Route::TYPE_DELIVERY)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim((string) $this->dispatchPackage->destination_city))])
            ->orderBy('name')
            ->get()
            ->filter(fn (Route $route) => $assignmentService->routeWarehouseId($route) === (int) $warehouse->id)
            ->values();
    }

    public function assignToDriver(int $routeId, DeliveryAssignmentService $assignmentService): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;

        try {
            $warehouse = $this->warehouse();

            if (! $warehouse) {
                throw new RuntimeException('Tu usuario no tiene un almacén asignado.');
            }

            $package = Package::where('tracking_number', trim($this->dispatchTrackingNumber))
                ->where('current_status', Package::STATUS_PENDIENTE_ENTREGA)
                ->firstOrFail();

            if (! $this->belongsToWarehouse($package, $warehouse)) {
                throw new RuntimeException('Este paquete no tiene como destino este almacén.');
            }

            $route = Route::findOrFail($routeId);

            $this->dispatchPackage = $assignmentService->assign($package, $route, (int) Auth::id());

            $this->dispatchSuccess = 'Paquete asignado: salió a reparto con el repartidor.';
        } catch (RuntimeException $e) {
            $this->dispatchError = $e->getMessage();
        }
    }

    public function render()
    {
        $warehouse = $this->warehouse();

        $stops = $warehouse
            ? RouteStop::query()
                ->where('warehouse_id', $warehouse->id)
                ->whereHas('route', fn ($query) => $query->where('route_type', Route::TYPE_HUB_DISTRIBUTION))
                ->with('route.driver')
                ->latest('id')
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.almacen.dashboard', [
            'warehouse' => $warehouse,
            'pendingStops' => $stops->where('status', RouteStop::STATUS_PENDING),
            'visitedStops' => $stops->where('status', RouteStop::STATUS_VISITED),
        ]);
    }
}
