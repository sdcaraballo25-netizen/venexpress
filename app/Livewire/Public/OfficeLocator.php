<?php

namespace App\Livewire\Public;

use App\Livewire\Concerns\ResolvesLayoutForViewer;
use App\Models\Ally;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Localizador de agencias aliadas. Muestra únicamente las agencias
 * con status ACTIVO y coordenadas cargadas (ver
 * Ally::scopePubliclyVisible); las coordenadas se cargan desde el
 * panel admin (AlliesManager::editLocation). También se enlaza desde
 * el menú de un usuario logueado, que ve esta misma página dentro del
 * layout de su propio panel — ver ResolvesLayoutForViewer.
 */
class OfficeLocator extends Component
{
    use ResolvesLayoutForViewer;

    public string $state = '';

    public string $search = '';

    public ?float $userLat = null;

    public ?float $userLng = null;

    public ?string $locationError = null;

    #[Computed]
    public function states(): array
    {
        return Ally::publiclyVisible()
            ->whereNotNull('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->all();
    }

    #[Computed]
    public function allies()
    {
        $allies = Ally::publiclyVisible()
            ->when($this->state !== '', fn ($q) => $q->where('state', $this->state))
            ->when($this->search !== '', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('business_name', 'like', '%' . $this->search . '%')
                        ->orWhere('city', 'like', '%' . $this->search . '%')
                        ->orWhere('address', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('business_name')
            ->get();

        if ($this->userLat !== null && $this->userLng !== null) {
            return $allies
                ->sortBy(fn (Ally $ally) => $this->haversineKm(
                    $this->userLat,
                    $this->userLng,
                    (float) $ally->latitude,
                    (float) $ally->longitude,
                ))
                ->values();
        }

        return $allies;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['state', 'search'], true)) {
            $this->dispatch('offices-updated', allies: $this->mapPoints());
        }
    }

    /**
     * Usa la ubicación que el navegador del visitante reportó (ver
     * geolocationSuccess() en la vista) para ordenar la lista de
     * agencias de más cercana a más lejana.
     */
    public function useMyLocation(float $lat, float $lng): void
    {
        $this->userLat = $lat;
        $this->userLng = $lng;
        $this->locationError = null;

        unset($this->allies);

        $this->dispatch('offices-updated', allies: $this->mapPoints());
    }

    public function locationDenied(): void
    {
        $this->locationError = 'No pudimos acceder a tu ubicación. Revisa los permisos del navegador.';
    }

    /**
     * Distancia en kilómetros desde la ubicación del visitante hasta
     * esta agencia (null si el visitante no compartió su ubicación).
     */
    public function distanceTo(Ally $ally): ?float
    {
        if ($this->userLat === null || $this->userLng === null) {
            return null;
        }

        return $this->haversineKm($this->userLat, $this->userLng, (float) $ally->latitude, (float) $ally->longitude);
    }

    /**
     * Distancia en línea recta entre dos coordenadas (fórmula de
     * Haversine), suficiente para ordenar "más cercano primero" sin
     * depender de un servicio externo de rutas.
     */
    protected function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Datos mínimos que necesita el mapa Leaflet (evita mandar el modelo completo).
     */
    protected function mapPoints(): array
    {
        return $this->allies->map(fn (Ally $ally) => [
            'id' => $ally->id,
            'name' => $ally->business_name,
            'city' => $ally->city,
            'address' => $ally->address,
            'lat' => (float) $ally->latitude,
            'lng' => (float) $ally->longitude,
        ])->values()->all();
    }

    public function render()
    {
        return view('public.office-locator', [
            'allies' => $this->allies,
            'states' => $this->states,
            'mapPoints' => $this->mapPoints(),
            'userLat' => $this->userLat,
            'userLng' => $this->userLng,
        ])->layout(
            $this->resolveLayoutForViewer(),
            ['title' => 'Agencias aliadas — Venexpress']
        );
    }
}
