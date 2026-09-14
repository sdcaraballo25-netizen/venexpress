<?php

namespace App\Jobs;

use App\Models\Package;
use App\Services\GeocodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeocodePackageDeliveryAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        protected int $packageId,
    ) {
        // Cola dedicada: un solo worker debe procesarla (numprocs=1
        // en Supervisor) para que las peticiones a Nominatim salgan
        // de a una, respetando su política de uso justo (~1/seg) sin
        // importar cuántos paquetes a nivel nacional lo disparen a
        // la vez — la cola los ordena automáticamente.
        $this->onQueue('geocoding');
    }

    public function handle(GeocodingService $geocoding): void
    {
        $package = Package::find($this->packageId);

        if (! $package || ! $package->requires_delivery) {
            return;
        }

        // DriverDeliveryController::routeOrder() ya intenta geocodificar
        // de forma síncrona en el momento; este job es solo el
        // respaldo para cuando esa consulta en vivo falló (Nominatim
        // caído o sin resultados momentáneamente).
        $geocoding->geocodePackageDeliveryAddress($package);

        // Throttle: al menos 1 segundo entre cada job de esta cola.
        // Con un solo worker dedicado, esto espacía las peticiones a
        // Nominatim sin importar cuántos jobs se encolen de golpe a
        // nivel nacional.
        sleep(1);
    }
}
