<?php

namespace App\Livewire\Driver;

use App\Models\Driver;
use App\Models\Package;
use App\Models\Route;
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

    public function searchPackage(): void
    {
        $this->reset([
            'package',
            'errorMessage',
            'successMessage',
            'securityWarning',
            'securityMessage',
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
     * Ruta en curso del repartidor, sin importar su tipo. Mismo
     * criterio que ya usan Dashboard.php, DriverRouteController y
     * LogisticsScanService: la más reciente en IN_PROGRESS.
     */
    protected function activeRoute(Driver $driver): ?Route
    {
        return Route::query()
            ->where('driver_id', $driver->id)
            ->where('status', Route::STATUS_IN_PROGRESS)
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
        ]);
    }

    public function render()
    {
        $driver = Auth::user()?->driver;

        $activeRoute = $driver ? $this->activeRoute($driver) : null;

        return view('livewire.driver.scanner', [
            'isDistribution' => $activeRoute?->route_type === Route::TYPE_HUB_DISTRIBUTION,
        ]);
    }
}
