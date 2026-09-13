<?php

namespace App\Livewire\Driver;

use App\Livewire\Driver\Support\HubDistributionPhase;
use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
use App\Models\RouteStop;
use App\Services\LogisticsScanService;
use App\Services\RouteService;
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
     * 'hub_reception', 'hub_departure' o 'hub_arrival'. Solo se usa para
     * presentar la confirmación correcta en la interfaz (qué operación
     * se acaba de registrar); no participa en ninguna decisión de
     * negocio, que sigue resuelta exclusivamente por LogisticsScanService.
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

    /*
    |--------------------------------------------------------------------------
    | ESCANEAR -> IDENTIFICAR -> MOSTRAR OPERACIÓN -> CONFIRMAR -> EJECUTAR
    |--------------------------------------------------------------------------
    |
    | Un escaneo (searchPackage) SOLO identifica la guía y calcula qué
    | operación correspondería (resolveOperation). Nunca ejecuta una
    | transición de estado por sí solo. La operación queda "pendiente"
    | en estas tres propiedades hasta que el repartidor confirma
    | explícitamente con confirmOperation(); ahí, y solo ahí, se llama a
    | LogisticsScanService.
    |
    | Esto evita que un segundo escaneo accidental de la misma guía (el
    | lector dispara dos lecturas, o el repartidor la vuelve a acercar)
    | encadene una segunda etapa distinta sin que nadie lo haya pedido:
    | ese segundo escaneo vuelve a pasar por identificar + mostrar, no
    | por ejecutar.
    */

    /**
     * Operación que quedó pendiente de confirmación tras el último
     * escaneo válido: 'collection', 'hub_reception', 'hub_departure' o
     * 'hub_arrival'. Null si no hay ninguna operación esperando
     * confirmación (por ejemplo, justo después de ejecutarla, o si el
     * escaneo no encontró una operación válida).
     */
    public ?string $pendingOperation = null;

    /**
     * ID del Package sobre el que aplica $pendingOperation. confirmOperation()
     * siempre recarga el paquete desde la base de datos usando este ID
     * en vez de confiar en la instancia $package hidratada por Livewire.
     */
    public ?int $pendingPackageId = null;

    /**
     * current_status del paquete en el momento exacto en que se calculó
     * $pendingOperation. confirmOperation() exige que el paquete siga
     * teniendo ese mismo estado antes de ejecutar nada: si cambió
     * (porque ya se confirmó antes, o porque otro movimiento lo alteró),
     * la confirmación se rechaza sin tocar LogisticsScanService. Esto es
     * una protección adicional de UX, NO reemplaza el lockForUpdate() ni
     * las validaciones de negocio que ya hace el servicio.
     */
    public ?string $pendingStatusSnapshot = null;

    /**
     * Paso 1 y 2: ESCANEAR -> IDENTIFICAR. Busca la guía y calcula qué
     * operación correspondería, pero no ejecuta ninguna transición de
     * estado.
     */
    public function searchPackage(): void
    {
        $this->reset([
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
            'lastAction',
            'pendingOperation',
            'pendingPackageId',
            'pendingStatusSnapshot',
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

        $this->package = $package;

        $activeRoute = $this->activeRoute($driver);

        if (! $activeRoute) {
            $this->errorMessage =
                'No tienes una ruta en curso. Inicia una ruta antes de escanear paquetes.';

            return;
        }

        $resolved = $this->resolveOperation($package, $activeRoute);

        if (! $resolved['eligible']) {
            $this->errorMessage = $resolved['blockedReason'];

            return;
        }

        $this->pendingOperation = $resolved['key'];
        $this->pendingPackageId = $package->id;
        $this->pendingStatusSnapshot = $package->current_status;
    }

    /**
     * Paso 3: MOSTRAR OPERACIÓN (lectura pura, sin efectos secundarios).
     *
     * Decide qué operación correspondería a este paquete dado el
     * route_type de la ruta activa, mirando únicamente su
     * current_status — el mismo criterio que antes decidía a qué método
     * de LogisticsScanService despachar, ahora usado solo para mostrar
     * una propuesta, no para ejecutarla. Las validaciones reales
     * (agencia/parada/ruta, driver_id, custodia) siguen viviendo
     * exclusivamente en LogisticsScanService y se aplican recién en
     * confirmOperation().
     */
    protected function resolveOperation(Package $package, Route $route): array
    {
        if ($route->route_type === Route::TYPE_HUB_TRANSFER) {
            if ($package->current_status === Package::STATUS_RECIBIDO_AGENCIA) {
                return [
                    'key' => 'collection',
                    'eligible' => true,
                    'label' => 'Registrar recolección',
                    'cta' => 'Confirmar recolección',
                    'hint' => 'Guía localizada en la agencia. Confirma para registrar la salida hacia Venexpress.',
                    'blockedReason' => null,
                ];
            }

            if ($package->current_status === Package::STATUS_RECOLECTADO_VENEXPRESS) {
                return [
                    'key' => 'hub_reception',
                    'eligible' => true,
                    'label' => 'Registrar recepción en HUB',
                    'cta' => 'Confirmar recepción en HUB',
                    'hint' => 'La recolección de esta guía ya fue registrada. Siguiente etapa disponible: recepción en HUB.',
                    'blockedReason' => null,
                ];
            }

            return [
                'key' => null,
                'eligible' => false,
                'label' => null,
                'cta' => null,
                'hint' => null,
                'blockedReason' => 'Este paquete no está en un estado válido para tu ruta de '
                    .'recolección. Estado actual: '.$package->statusLabel().'.',
            ];
        }

        if ($route->route_type === Route::TYPE_HUB_DISTRIBUTION) {
            if ($package->current_status === Package::STATUS_EN_HUB) {
                return [
                    'key' => 'hub_departure',
                    'eligible' => true,
                    'label' => 'Registrar salida de HUB',
                    'cta' => 'Confirmar salida de HUB',
                    'hint' => 'Guía localizada en HUB. Confirma para registrar su salida hacia el almacén destino.',
                    'blockedReason' => null,
                ];
            }

            if ($package->current_status === Package::STATUS_EN_TRANSITO_NACIONAL) {
                return [
                    'key' => 'hub_arrival',
                    'eligible' => true,
                    'label' => 'Registrar llegada a almacén',
                    'cta' => 'Confirmar llegada a almacén',
                    'hint' => 'Este paquete ya salió del HUB y está en tránsito nacional. '
                        .'Siguiente etapa disponible: llegada al almacén destino.',
                    'blockedReason' => null,
                ];
            }

            return [
                'key' => null,
                'eligible' => false,
                'label' => null,
                'cta' => null,
                'hint' => null,
                'blockedReason' => 'Este paquete no está en un estado válido para tu ruta de '
                    .'distribución. Estado actual: '.$package->statusLabel().'.',
            ];
        }

        return [
            'key' => null,
            'eligible' => false,
            'label' => null,
            'cta' => null,
            'hint' => null,
            'blockedReason' => "Tipo de ruta no soportado para escaneo: {$route->route_type}.",
        ];
    }

    /**
     * Paso 5: EJECUTAR. Único punto de entrada que llama a
     * LogisticsScanService. Solo se dispara por un clic explícito del
     * repartidor sobre el botón de confirmación — nunca desde un
     * escaneo.
     */
    public function confirmOperation(string $operation): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;

        if ($this->pendingOperation === null || $this->pendingPackageId === null) {
            $this->errorMessage =
                'No hay ninguna operación pendiente de confirmar. Vuelve a escanear la guía.';

            return;
        }

        if ($operation !== $this->pendingOperation) {
            $this->errorMessage =
                'La operación seleccionada ya no coincide con la guía escaneada. Vuelve a escanear.';

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
            ->with(['ally', 'driver', 'histories'])
            ->find($this->pendingPackageId);

        // Protección adicional de UX (capa 2, además de la limpieza de
        // pendingOperation y del lockForUpdate() dentro del servicio):
        // si el estado ya no es el mismo que cuando se calculó la
        // operación pendiente, no se ejecuta nada. No sustituye ninguna
        // validación de negocio, solo evita una llamada innecesaria al
        // servicio cuando ya sabemos que la premisa cambió.
        if (! $package || $package->current_status !== $this->pendingStatusSnapshot) {
            $this->clearPendingOperation();

            $this->package = $package ?? $this->package;
            $this->errorMessage =
                'Este paquete ya cambió de estado. Vuelve a escanearlo para ver la operación disponible.';

            return;
        }

        $activeRoute = $this->activeRoute($driver);

        if (! $activeRoute) {
            $this->clearPendingOperation();

            $this->errorMessage =
                'No tienes una ruta en curso. Inicia una ruta antes de escanear paquetes.';

            return;
        }

        $service = app(LogisticsScanService::class);

        try {
            $package = match ($this->pendingOperation) {
                'collection' => $this->scanForCollection($service, $package, $driver, (int) $user->id),
                'hub_reception' => $this->executeHubReception($service, $package, $driver, (int) $user->id),
                'hub_departure' => $this->executeHubDeparture($service, $package, $driver, (int) $user->id),
                'hub_arrival' => $this->executeHubArrival($service, $package, $driver, (int) $user->id),
                default => throw new RuntimeException('Operación de escaneo desconocida.'),
            };

            $this->package = $package;
        } catch (RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
            $this->package = $package->fresh(['ally', 'driver', 'histories']) ?? $package;
        } finally {
            // Capa 1: se limpia siempre, haya éxito o error, para que
            // ningún clic posterior (doble clic que se coló, botón
            // desincronizado) pueda reintentar sobre este mismo estado
            // pendiente.
            $this->clearPendingOperation();
        }
    }

    protected function clearPendingOperation(): void
    {
        $this->pendingOperation = null;
        $this->pendingPackageId = null;
        $this->pendingStatusSnapshot = null;
    }

    /**
     * Ejecuta la recolección (agencia -> Venexpress). Reutiliza
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
     * Ejecuta la recepción física en el HUB (segunda mitad de "Aliado
     * -> HUB"). Reutiliza LogisticsScanService::scanHubReception() tal
     * cual, sin duplicar ninguna de sus validaciones.
     */
    protected function executeHubReception(
        LogisticsScanService $service,
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
        $package = $service->scanHubReception(
            package: $package,
            driver: $driver,
            userId: $userId,
        );

        $this->lastAction = 'hub_reception';
        $this->processedCount++;
        $this->trackOperation('hub_reception');

        $this->successMessage =
            'Recepción en HUB registrada correctamente. El paquete quedó EN_HUB.';

        return $package;
    }

    /**
     * Ejecuta la salida de HUB (HUB -> almacén destino). Reutiliza
     * LogisticsScanService::scanHubDeparture() tal cual, sin duplicar
     * ninguna de sus validaciones.
     */
    protected function executeHubDeparture(
        LogisticsScanService $service,
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
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

    /**
     * Ejecuta la llegada al almacén destino. Reutiliza
     * LogisticsScanService::scanHubArrival() tal cual, sin duplicar
     * ninguna de sus validaciones.
     */
    protected function executeHubArrival(
        LogisticsScanService $service,
        Package $package,
        Driver $driver,
        int $userId,
    ): Package {
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
            'pendingOperation',
            'pendingPackageId',
            'pendingStatusSnapshot',
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
            $operation = $this->lastAction === 'hub_reception'
                ? 'hub_reception'
                : 'collection';

            if ($operation === 'hub_reception') {
                $operationTitle = 'RECEPCIÓN EN HUB';
                $operationInstructions = 'Escanea los paquetes que estás recibiendo en el HUB.';
            } else {
                $operationTitle = 'RECOLECCIÓN EN ALIADO';
                $operationInstructions = 'Escanea las guías que estás recogiendo de este aliado.';
            }

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
        } elseif ($operation === 'hub_reception' && $activeRoute) {
            $pendingCount = Package::query()
                ->whereIn(
                    'id',
                    app(RouteService::class)->packageIdsCollectedOnRoute($activeRoute)
                )
                ->where('current_status', Package::STATUS_RECOLECTADO_VENEXPRESS)
                ->count();
        } elseif ($operation === 'hub_departure') {
            $pendingCount = HubDistributionPhase::pendingDepartureCount($activeRoute);
        } elseif ($operation === 'hub_arrival') {
            $pendingCount = HubDistributionPhase::pendingArrivalsCount($driver);
        }

        $operationTotal = $pendingCount !== null
            ? $pendingCount + $this->operationProcessedCount
            : null;

        // Paso 3 (MOSTRAR OPERACIÓN): datos de la operación pendiente de
        // confirmación para la guía identificada, si la hay. Reutiliza
        // resolveOperation() en vez de recalcular el texto por separado,
        // para que searchPackage() y render() nunca puedan mostrar cosas
        // distintas.
        $pendingOperationView = null;

        if ($this->pendingOperation !== null && $this->package && $activeRoute) {
            $pendingOperationView = $this->resolveOperation($this->package, $activeRoute);
        }

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
            'pendingOperationView' => $pendingOperationView,
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
