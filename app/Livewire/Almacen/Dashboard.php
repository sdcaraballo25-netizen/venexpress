<?php

namespace App\Livewire\Almacen;

use App\Exceptions\MisroutedPackageException;
use App\Livewire\Concerns\HandlesThirdPartyPickup;
use App\Models\AuditLog;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Warehouse;
use App\Services\DeliveryAssignmentService;
use App\Services\HubReceptionService;
use App\Services\MisroutedPackageAlertService;
use App\Services\PackageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

/**
 * Dashboard de personal de almacén:
 *
 * - Lista las paradas de rutas HUB Distribución que tienen a este
 *   almacén como destino, separadas en pendientes y ya recibidas.
 * - Permite escanear la llegada de un paquete, lo que lo recibe
 *   físicamente en el almacén con HubReceptionService, igual que la
 *   recepción en HUB de Admin (Admin\PackageReception): queda EN_HUB y,
 *   si este almacén es su destino, se libera solo (LISTO_RETIRO,
 *   PENDIENTE_ENTREGA o despacho a la agencia de retiro). Si llega a un
 *   almacén que no es su destino se rechaza y se avisa al administrador
 *   (MisroutedPackageAlertService).
 * - Permite despachar un paquete ya recibido: a un cliente (o a un
 *   tercero autorizado) que lo retira en persona (LISTO_RETIRO, mismo
 *   flujo que Ally\PackagePickup), o a un repartidor con una ruta de
 *   reparto en curso desde este almacén hacia esa ciudad
 *   (PENDIENTE_ENTREGA, vía DeliveryAssignmentService).
 * - Tras una entrega a domicilio fallida (ENTREGA_FALLIDA), al recibir
 *   el paquete de vuelta decide un nuevo intento o la devolución al
 *   remitente.
 */
#[Layout('layouts.almacen')]
#[Title('Almacén')]
class Dashboard extends Component
{
    use HandlesThirdPartyPickup;
    use WithFileUploads;

    /**
     * Ya recibidos en el almacén y listos para salir: retiro en persona
     * (LISTO_RETIRO), entrega a domicilio (PENDIENTE_ENTREGA) o de vuelta
     * de una entrega fallida, pendiente de decidir (ENTREGA_FALLIDA).
     */
    private const DISPATCHABLE_STATUSES = [
        Package::STATUS_LISTO_RETIRO,
        Package::STATUS_PENDIENTE_ENTREGA,
        Package::STATUS_ENTREGA_FALLIDA,
    ];

    public string $trackingNumber = '';

    public ?string $scanSuccess = null;

    public ?string $scanError = null;

    // Despacho.
    public string $dispatchTrackingNumber = '';

    public ?Package $dispatchPackage = null;

    public string $recipientIdDoc = '';

    /** Motivo al devolver al remitente una entrega fallida. */
    public string $returnReason = '';

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
     * El paquete está físicamente en este almacén (current_warehouse_id,
     * fijado al recibirlo) o, para guías anteriores a ese dato, va a su
     * misma ciudad.
     */
    protected function isHandledByWarehouse(Package $package, Warehouse $warehouse): bool
    {
        return (int) $package->current_warehouse_id === (int) $warehouse->id
            || $this->belongsToWarehouse($package, $warehouse);
    }

    /**
     * Entrada única para el lector QR de la cámara: decide si la
     * guía escaneada corresponde a una llegada (todavía no recibida
     * en este almacén) o a un despacho (ya LISTO_RETIRO), y dispara
     * la acción correspondiente. Mismo patrón que Driver\Scanner::
     * scan(), llamado desde JS vía $wire.scanGuide(...).
     */
    public function scanGuide(string $code, HubReceptionService $receptionService): void
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

    public function scanArrival(HubReceptionService $receptionService): void
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
            // Una sola transacción: si la recepción falla, no queda
            // nada a medias (ni la parada marcada como visitada).
            $received = DB::transaction(function () use ($receptionService, $package, $warehouse, $stop) {
                $userId = (int) Auth::id();

                $received = match ($package->current_status) {
                    Package::STATUS_RECOLECTADO_VENEXPRESS => $receptionService->receiveAtWarehouse($package, $userId, $warehouse),
                    Package::STATUS_EN_TRANSITO_NACIONAL => $receptionService->receiveTransferAtWarehouse($package, $userId, $warehouse),
                    default => throw new RuntimeException(
                        'Esta guía no está en un estado que permita recibirla en el almacén. Estado actual: '
                        .$package->statusLabel().'.'
                    ),
                };

                if ($stop && $stop->status === RouteStop::STATUS_PENDING) {
                    $stop->update(['status' => RouteStop::STATUS_VISITED, 'visited_at' => now()]);
                }

                return $received;
            });

            $this->scanSuccess = $this->receptionMessage($received, $warehouse);
            $this->trackingNumber = '';
        } catch (MisroutedPackageException $e) {
            $expected = Warehouse::find($e->expectedWarehouseId);

            app(MisroutedPackageAlertService::class)->report(
                $package,
                'el almacén '.$warehouse->name,
                (int) Auth::id(),
                $expected?->name,
            );

            $this->scanError = 'Este paquete no tiene como destino este almacén'
                .($expected ? " (va a {$expected->name})" : '')
                .'. No lo recibas: se avisó al administrador para corregir el envío.';
        } catch (RuntimeException $e) {
            $this->scanError = $e->getMessage();
        }
    }

    protected function receptionMessage(Package $received, Warehouse $warehouse): string
    {
        $prefix = "Guía {$received->tracking_number} recibida en {$warehouse->name}. ";

        return $prefix.match ($received->current_status) {
            Package::STATUS_LISTO_RETIRO => 'Ya está lista para retiro.',
            Package::STATUS_PENDIENTE_ENTREGA => 'Quedó pendiente de entrega a domicilio: asígnala a un repartidor.',
            Package::STATUS_EN_TRANSITO_NACIONAL => 'Salió despachada hacia su agencia de retiro.',
            default => (int) $received->destination_warehouse_id === (int) $warehouse->id
                ? 'Quedó en el almacén: revisa con el administrador su liberación.'
                : 'Debe seguir hacia '.($received->destinationWarehouse?->name ?? 'su almacén destino').'.',
        };
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
        $this->returnReason = '';
        $this->resetThirdParty();

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

        if (! $this->isHandledByWarehouse($package, $warehouse)) {
            $this->dispatchError = 'Este paquete no tiene como destino este almacén.';

            return;
        }

        $this->dispatchPackage = $package;
    }

    /**
     * Entrega en persona a quien retira el paquete en el almacén: el
     * destinatario (su cédula debe coincidir) o un tercero autorizado
     * por él (HandlesThirdPartyPickup). Mismo flujo que
     * Ally\PackagePickup::deliver().
     */
    public function deliverToClient(PackageService $packageService): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;

        $this->validate(array_merge(
            ['recipientIdDoc' => ['required', 'string', 'max:50']],
            $this->thirdPartyRules(),
        ), $this->thirdPartyMessages());

        $photos = [null, null];

        try {
            $warehouse = $this->warehouse();

            if (! $warehouse) {
                throw new RuntimeException('Tu usuario no tiene un almacén asignado.');
            }

            $package = Package::where('tracking_number', trim($this->dispatchTrackingNumber))
                ->where('current_status', Package::STATUS_LISTO_RETIRO)
                ->firstOrFail();

            if (! $this->isHandledByWarehouse($package, $warehouse)) {
                throw new RuntimeException('Este paquete no tiene como destino este almacén.');
            }

            if ($package->requires_delivery) {
                throw new RuntimeException('Este envío requiere entrega a domicilio; no puede retirarse en el almacén.');
            }

            $photos = $this->storeThirdPartyPhotos();

            // completeAgencyPickup() (no changeStatus()) también registra
            // el cobro COD y delivery_completed_at, y valida la cédula del
            // destinatario (o los datos del tercero autorizado).
            $this->dispatchPackage = $packageService->completeAgencyPickup(
                $package,
                (int) Auth::id(),
                $this->recipientIdDoc,
                'Retiro confirmado en almacén '.$warehouse->name,
                'Almacén '.$warehouse->name,
                receivedByThirdParty: $this->byThirdParty,
                receiverName: $this->thirdPartyName,
                thirdPartyIdPhotoPath: $photos[0],
                recipientIdCopyPath: $photos[1],
            );

            $this->dispatchSuccess = $this->byThirdParty
                ? 'Retiro confirmado por un tercero autorizado. El paquete quedó ENTREGADO.'
                : 'Retiro confirmado. El paquete quedó ENTREGADO.';
            $this->recipientIdDoc = '';
            $this->resetThirdParty();
        } catch (RuntimeException $e) {
            $this->deleteThirdPartyPhotos($photos);
            $this->dispatchError = $e->getMessage();
        }
    }

    /**
     * Entrega fallida de vuelta en el almacén: sale de nuevo a reparto
     * (queda PENDIENTE_ENTREGA para asignarla a un repartidor).
     */
    public function retryDelivery(PackageService $packageService): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;

        try {
            [$package, $warehouse] = $this->failedPackageForWarehouse();

            $this->dispatchPackage = $packageService->scheduleDeliveryRetry($package, (int) Auth::id(), $warehouse);

            $this->dispatchSuccess = 'Nuevo intento programado: quedó pendiente de entrega. Asígnala a un repartidor.';
        } catch (RuntimeException $e) {
            $this->dispatchError = $e->getMessage();
        }
    }

    /**
     * Entrega fallida de vuelta en el almacén: se devuelve al remitente
     * (PackageService::startReturn(), igual que desde Admin).
     */
    public function returnToSender(PackageService $packageService): void
    {
        $this->dispatchSuccess = null;
        $this->dispatchError = null;

        $this->validate([
            'returnReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'returnReason.required' => 'Indica el motivo de la devolución.',
            'returnReason.min' => 'Describe el motivo con un poco más de detalle.',
        ]);

        try {
            [$package, $warehouse] = $this->failedPackageForWarehouse();

            $returned = DB::transaction(function () use ($packageService, $package, $warehouse) {
                $returned = $packageService->startReturn($package, (int) Auth::id(), $this->returnReason);

                // Queda constancia de dónde está físicamente mientras
                // operaciones coordina el traslado a la agencia de origen.
                $returned->forceFill(['current_warehouse_id' => $warehouse->id])->save();

                AuditLog::create([
                    'actor_user_id' => Auth::id(),
                    'action' => 'package.return_started',
                    'target_type' => Package::class,
                    'target_id' => $returned->id,
                    'description' => "Inició la devolución al remitente de la guía {$returned->tracking_number} desde el almacén {$warehouse->name}.",
                    'metadata' => [
                        'tracking_number' => $returned->tracking_number,
                        'reason' => $returned->return_reason,
                        'warehouse_id' => $warehouse->id,
                    ],
                    'ip_address' => request()->ip(),
                ]);

                return $returned;
            });

            $this->dispatchPackage = $returned;
            $this->returnReason = '';
            $this->dispatchSuccess = 'Devolución iniciada: la agencia de origen la entregará al remitente cuando regrese.';
        } catch (RuntimeException $e) {
            $this->dispatchError = $e->getMessage();
        }
    }

    /**
     * @return array{0: Package, 1: Warehouse}
     */
    protected function failedPackageForWarehouse(): array
    {
        $warehouse = $this->warehouse();

        if (! $warehouse) {
            throw new RuntimeException('Tu usuario no tiene un almacén asignado.');
        }

        $package = Package::where('tracking_number', trim($this->dispatchTrackingNumber))->first();

        if (! $package || ! $package->isDeliveryFailed()) {
            throw new RuntimeException('Esta guía no es una entrega fallida pendiente de decidir.');
        }

        if (! $this->isHandledByWarehouse($package, $warehouse)) {
            throw new RuntimeException('Este paquete no corresponde a este almacén.');
        }

        return [$package, $warehouse];
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

            if (! $this->isHandledByWarehouse($package, $warehouse)) {
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
