<?php

namespace App\Livewire\Public;

use App\Models\Ally;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Localizador público de agencias aliadas.
 *
 * Muestra únicamente las agencias con status ACTIVO y coordenadas
 * cargadas (ver Ally::scopePubliclyVisible). Las coordenadas se
 * cargan desde el panel admin (AlliesManager::editLocation).
 */
#[Layout('layouts.public', ['title' => 'Agencias aliadas — Venexpress'])]
class OfficeLocator extends Component
{
    public string $state = '';

    public string $search = '';

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
        return Ally::publiclyVisible()
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
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['state', 'search'], true)) {
            $this->dispatch('offices-updated', allies: $this->mapPoints());
        }
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
        ]);
    }
}
