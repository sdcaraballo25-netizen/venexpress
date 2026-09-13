<?php

namespace App\Livewire\Driver;

use App\Livewire\Driver\Support\HubDistributionPhase;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Services\LogisticsScanService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.driver')]
class Scanner extends Component
{
    public string $trackingNumber = '';

    public ?Package $package = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    public bool $securityWarning = false;

    public ?string $securityMessage = null;

    /**
     * Última acción de escaneo completada con éxito: 'collection',
     * 'hub_departure' o 'hub_arrival'. Solo se usa para presentar la
     * confirmación correcta en la interfaz (qué operación se acaba de
     * registrar); no participa en ninguna decisión de negocio, que
     * sigue resuelta exclusivamente por LogisticsScanService.
     */
    public ?string $lastAction = null;

    /**
     * Paquetes procesados con éxito durante esta sesión de escaneo,
     * para que el repartidor vea su avance sin salir de la pantalla.
     */
    public int $processedCount = 0;

    /**
     * Identifica la operación actual (p.ej. 'collection:{allyId}',
     * 'hub_departure', 'hub_arrival') para saber cuándo el driver pasó
     * a una operación distinta y así reiniciar el contador "X de Y" de
     * esa operación en particular, sin afectar a $processedCount.
     */
    public string $currentOperationKey = '';

    /**
     * Paquetes procesados con éxito dentro de la operación actual
     * ($currentOperationKey). Se reinicia automáticamente cuando la
     * operación cambia (por ejemplo, de recolección en un aliado a
     * recolección en el siguiente, o de salida de HUB a recepción en
     * almacén).
     */
    public int $operationProcessedCount = 0;

    public function searchPackage(): void
    {
        $this->reset([
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
            'lastAction',
        ]);

        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->errorMessage = 'Introduce un número de guía.';

            return;
        }

        $user = Auth::user();

        $driver = $user?->driver;

        if (! $driver) {
            abort(
                403,
                'Tu usuario no tiene un perfil de repartidor asociado.'
            );
        }

        $package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with([
                'ally',
                'driver',
                'histories',
            ])
            ->first();

        if (! $package) {
            $this->errorMessage =
                "No existe una guía con número: {$this->trackingNumber}";

            return;
        }

        $this->checkSecurity($package);

        $activeRoute = $this->activeRoute($driver);

        if (! $activeRoute) {
            $this->errorMessage =
                'No tienes una ruta en curso. Inicia una ruta antes de escanear paquetes.';
            $this->package = $package;

            return;
        }

        $service = app(LogisticsScanService::class);

        try {
            $package = match ($activeRoute->route_type) {
                Route::TYPE_HUB_TRANSFER => $this->scanForCollection(
                    $service,
                    $package,
                    $driver,
                    (int) $user->id,
                ),
                Route::TYPE_HUB_DISTRIBUTION => $this->scanForDistribution(
                    $service,
                    $package,
                    $driver,
                    (int) $user->id,
                ),
                default => throw new RuntimeException(
                    "Tipo de ruta no soportado para escaneo: {$activeRoute->route_type}."
                ),
            };

            $this->package = $package;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
            $this->package = $package;
        }
    }

    /**
     * Ruta hub_transfer: recolección en agencia -> HUB. Reutiliza
     * LogisticsScanService::scanCollection() tal cual, sin duplicar
     * ninguna de sus validaciones.
     */
    protected function scanForCollection(
        LogisticsScanService $service,
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        $package = $service->scanCollection(
            package: $package,
            driver: $driver,
            userId: $userId,
        );

        $this->lastAction = 'collection';
        $this->processedCount++;
        $this->trackOperation('collection:'.$package->ally_id);

        $this->successMessage =
            'Salida registrada correctamente. El paquete quedó recolectado por Venexpress.';

        return $package;
    }

    /**
     * Ruta hub_distribution: HUB -> almacén propio de Venexpress
     * destino. El mismo botón "Escanear" cubre las dos acciones del
     * driver de distribución, decidido por el estado actual del
     * paquete. Reutiliza LogisticsScanService::scanHubDeparture()/
     * scanHubArrival() tal cual, sin duplicar su lógica.
     */
    protected function scanForDistribution(
        LogisticsScanService $service,
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        if ($package->current_status === Package::STATUS_EN_HUB) {
            $package = $service->scanHubDeparture(
                package: $package,
                driver: $driver,
                userId: $userId,
            );

            $this->lastAction = 'hub_departure';
            $this->processedCount++;
            $this->trackOperation(HubDistributionPhase::DEPARTURE);

            $this->successMessage =
                'Salida de HUB registrada correctamente. El paquete quedó en tránsito nacional.';

            return $package;
        }

        if ($package->current_status === Package::STATUS_EN_TRANSITO_NACIONAL) {
            $package = $service->scanHubArrival(
                package: $package,
                driver: $driver,
                userId: $userId,
            );

            $this->lastAction = 'hub_arrival';
            $this->processedCount++;
            $this->trackOperation(HubDistributionPhase::ARRIVAL);

            $this->successMessage =
                'Llegada al almacén destino registrada correctamente.';

            return $package;
        }

        throw new RuntimeException(
            'Este paquete no está en un estado válido para tu ruta de '
            .'distribución. Estado actual: '.$package->statusLabel().'.'
        );
    }

    /**
     * Registra un escaneo exitoso bajo una clave de operación
     * (aliado+parada para recolección, o la fase de distribución).
     * Si la clave cambia respecto al último escaneo, reinicia el
     * contador "X de Y" para no arrastrar el progreso de una operación
     * distinta a la actual.
     */
    protected function trackOperation(string $key): void
    {
        if ($key !== $this->currentOperationKey) {
            $this->currentOperationKey = $key;
            $this->operationProcessedCount = 0;
        }

        $this->operationProcessedCount++;
    }

    /**
     * Ruta en curso del repartidor, sin importar su tipo. Mismo
     * criterio que ya usan Dashboard.php, DriverRouteController y
     * LogisticsScanService: la más reciente en IN_PROGRESS.
     */
    protected function activeRoute(Driver $driver): ?Route
    {
        return Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_IN_PROGRESS)
            ->with(['stops.ally', 'stops.warehouse'])
            ->latest('started_at')
            ->first();
    }

    public function scan(string $trackingNumber): void
    {
        $this->trackingNumber = trim($trackingNumber);

        if ($this->trackingNumber === '') {
            return;
        }

        $this->searchPackage();
    }

    protected function checkSecurity(Package $package): void
    {
        if (! $package->security_hash) {
            $this->securityWarning = false;
            $this->securityMessage = null;

            return;
        }

        if ($package->verifySecurityHash()) {
            $this->securityWarning = false;
            $this->securityMessage = null;

            return;
        }

        $this->securityWarning = true;

        $this->securityMessage =
            'Los datos de esta guía no coinciden con su código de seguridad original. '
            .'Verifica manualmente antes de continuar.';
    }

    public function clearSearch(): void
    {
        $this->reset([
            'trackingNumber',
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
            'lastAction',
        ]);
    }

    public function render()
    {
        $driver = Auth::user()?->driver;

        $activeRoute = $driver ? $this->activeRoute($driver) : null;

        $routeType = $activeRoute?->route_type;

        $operation = null;
        $operationTitle = null;
        $operationInstructions = null;
        $contextStop = null;

        if ($routeType === Route::TYPE_HUB_TRANSFER) {
            $operation = 'collection';
            $operationTitle = 'RECOLECCIÓN EN ALIADO';
            $operationInstructions = 'Escanea las guías que estás recogiendo de este aliado.';
            $contextStop = $this->contextStop($activeRoute);
        } elseif ($routeType === Route::TYPE_HUB_DISTRIBUTION) {
            $operation = in_array($this->lastAction, ['hub_departure', 'hub_arrival'], true)
                ? $this->lastAction
                : HubDistributionPhase::resolve($driver);

            if ($operation === 'hub_arrival') {
                $operationTitle = 'RECEPCIÓN EN ALMACÉN';
                $operationInstructions = 'Escanea los paquetes que estás transfiriendo a este almacén.';
            } else {
                $operationTitle = 'SALIDA DESDE HUB';
                $operationInstructions = 'Escanea los paquetes que salen del HUB hacia este almacén.';
            }

            $contextStop = $this->contextStop($activeRoute);
        }

        // El evento de despacho (scanHubDeparture) no fija route_stop_id
        // porque un mismo camión puede llevar paquetes para varias
        // paradas; el de llegada (scanHubArrival) sí, así que ahí
        // podemos mostrar el almacén exacto ya registrado en el
        // historial, sin adivinar ni tocar LogisticsScanService.
        $arrivalWarehouse = null;

        if ($this->lastAction === 'hub_arrival' && $this->package) {
            $arrivalWarehouse = $this->package->histories
                ->sortByDesc('id')
                ->first()
                ?->routeStop
                ?->warehouse;
        }

        // Cuántos paquetes faltan justo ahora para la operación
        // vigente, para mostrar "X de Y procesados" y decidir si ya
        // "Operación completada" o si "Continúa escaneando". Es un
        // dato de presentación: no decide nada, solo refleja el mismo
        // estado que LogisticsScanService ya validó en cada scan.
        $pendingCount = null;

        if ($operation === 'collection' && $contextStop?->ally_id) {
            $pendingCount = Package::query()
                ->where('ally_id', $contextStop->ally_id)
                ->where('current_status', Package::STATUS_RECIBIDO_AGENCIA)
                ->count();
        } elseif ($operation === 'hub_departure') {
            $pendingCount = HubDistributionPhase::pendingDepartureCount($activeRoute);
        } elseif ($operation === 'hub_arrival') {
            $pendingCount = HubDistributionPhase::pendingArrivalsCount($driver);
        }

        $operationTotal = $pendingCount !== null
            ? $pendingCount + $this->operationProcessedCount
            : null;

        return view('livewire.driver.scanner', [
            'isDistribution' => $routeType === Route::TYPE_HUB_DISTRIBUTION,
            'activeRoute' => $activeRoute,
            'operation' => $operation,
            'operationTitle' => $operationTitle,
            'operationInstructions' => $operationInstructions,
            'contextStop' => $contextStop,
            'arrivalWarehouse' => $arrivalWarehouse,
            'pendingCount' => $pendingCount,
            'operationTotal' => $operationTotal,
        ]);
    }

    /**
     * Parada de referencia para mostrar en la interfaz antes de
     * escanear: la próxima pendiente, igual criterio que ya usa
     * Dashboard.php para "Próxima parada".
     */
    protected function contextStop(Route $route): ?RouteStop
    {
        return $route->stops->firstWhere('status', RouteStop::STATUS_PENDING)
            ?? $route->stops->first();
    }
}
