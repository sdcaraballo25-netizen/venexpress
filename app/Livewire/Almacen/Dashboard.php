<?php

namespace App\Livewire\Almacen;

use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Warehouse;
use App\Services\DeliveryAssignmentService;
use App\Services\DestinationReceptionService;
use App\Services\PackageService;
use App\Models\PackageHistory;
use Illuminate\Support\Facades\Auth;
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
 *   físicamente en el almacén (EN_TRANSITO_NACIONAL -> LISTO_RETIRO,
 *   mismo mecanismo que Ally\PackageReception usa para agencias, vía
 *   DestinationReceptionService).
 * - Permite despachar un paquete ya recibido (LISTO_RETIRO): a un
 *   cliente que lo retira en persona (mismo flujo que
 *   Ally\PackagePickup), o a un repartidor con una ruta de reparto en
 *   curso hacia esa ciudad (mismo flujo que Admin\DriverAssignment,
 *   vía DeliveryAssignmentService).
 */
#[Layout('layouts.almacen')]
#[Title('Almacén')]
class Dashboard extends Component
{
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

        if ($package->current_status === Package::STATUS_LISTO_RETIRO) {
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
            // Libera la custodia del driver de HUB que lo trajo: sin
            // esto, DeliveryAssignmentService::assign() más adelante
            // rechazaría asignarlo a un repartidor de reparto distinto
            // (cree que "ya está asignado a otro repartidor").
            $package->update(['driver_id' => null]);

            $receptionService->receive(
                package: $package,
                userId: (int) Auth::id(),
                destinationLocation: 'Almacén '.$warehouse->name,
                routeStopId: $stop?->id,
            );

            if ($stop && $stop->status === RouteStop::STATUS_PENDING) {
                $stop->update(['status' => RouteStop::STATUS_VISITED, 'visited_at' => now()]);
            }

            $this->scanSuccess = "Guía {$trackingNumber} recibida en {$warehouse->name}. Ya está lista para entregar.";
            $this->trackingNumber = '';
        } catch (RuntimeException $e) {
            $this->scanError = $e->getMessage();
        }
    }

    /**
     * Busca un paquete LISTO_RETIRO para despacharlo desde este
     * almacén (a cliente o a repartidor).
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

        if ($package->current_status !== Package::STATUS_LISTO_RETIRO) {
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

            $this->dispatchPackage = $packageService->changeStatus(
                $package,
                Package::STATUS_ENTREGADO,
                (int) Auth::id(),
                'Retiro confirmado en almacén '.($warehouse?->name ?? ''),
                null,
                PackageHistory::EVENT_ENTREGA,
                'Almacén '.($warehouse?->name ?? ''),
                'Destinatario',
            );

            $this->dispatchSuccess = 'Retiro confirmado. El paquete quedó ENTREGADO.';
            $this->recipientIdDoc = '';
        } catch (RuntimeException $e) {
            $this->dispatchError = $e->getMessage();
        }
    }

    /**
     * Rutas de reparto en curso hacia la ciudad del paquete
     * encontrado — mismo criterio que Admin\DriverAssignment.
     */
    #[Computed]
    public function availableDeliveryRoutes()
    {
        if (! $this->dispatchPackage) {
            return collect();
        }

        return Route::query()
            ->with('driver.user')
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->whereNotNull('driver_id')
            ->where('route_type', Route::TYPE_DELIVERY)
            ->whereRaw('LOWER(city) = ?', [mb_strtolower(trim((string) $this->dispatchPackage->destination_city))])
            ->orderBy('name')
            ->get();
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
                ->where('current_status', Package::STATUS_LISTO_RETIRO)
                ->firstOrFail();

            if (! $this->belongsToWarehouse($package, $warehouse)) {
                throw new RuntimeException('Este paquete no tiene como destino este almacén.');
            }

            $route = Route::findOrFail($routeId);

            $this->dispatchPackage = $assignmentService->assign($package, $route, (int) Auth::id());

            $this->dispatchSuccess = 'Paquete asignado correctamente al repartidor.';
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
