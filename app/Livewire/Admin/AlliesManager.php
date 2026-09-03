<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Gestión de Aliados')]
class AlliesManager extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Estado del modal de ubicación (usado por el localizador público).
     */
    public bool $showLocationModal = false;

    public ?int $editingAllyId = null;

    public string $location_state = '';

    public ?float $location_latitude = null;

    public ?float $location_longitude = null;

    /**
     * Abre el modal de ubicación con los datos actuales del aliado.
     */
    public function editLocation(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $this->editingAllyId = $ally->id;
        $this->location_state = $ally->state ?? '';
        $this->location_latitude = $ally->latitude ? (float) $ally->latitude : null;
        $this->location_longitude = $ally->longitude ? (float) $ally->longitude : null;
        $this->showLocationModal = true;
    }

    /**
     * Recibe la posición elegida por clic en el mapa (evento de Alpine/Leaflet).
     */
    public function setLocationFromMap(float $lat, float $lng): void
    {
        $this->location_latitude = round($lat, 7);
        $this->location_longitude = round($lng, 7);
    }

    public function saveLocation(): void
    {
        $this->validate([
            'location_state' => ['required', 'string'],
            'location_latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [], [
            'location_state' => 'estado',
            'location_latitude' => 'latitud',
            'location_longitude' => 'longitud',
        ]);

        $ally = Ally::findOrFail($this->editingAllyId);

        $ally->update([
            'state' => $this->location_state,
            'latitude' => $this->location_latitude,
            'longitude' => $this->location_longitude,
        ]);

        $this->showLocationModal = false;
        $this->editingAllyId = null;

        session()->flash('success', 'La ubicación de la agencia se actualizó correctamente. Ya es visible en el localizador público.');
    }

    /**
     * Aprobar un aliado.
     */
    public function approve(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        session()->flash('success', 'El aliado fue aprobado correctamente.');
    }

    /**
     * Rechazar un aliado.
     */
    public function reject(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_REJECTED,
        ]);

        session()->flash('success', 'El aliado fue rechazado.');
    }

    /**
     * Suspender un aliado.
     */
    public function suspend(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_SUSPENDED,
        ]);

        session()->flash('success', 'El aliado fue suspendido.');
    }

    /**
     * Reactivar un aliado suspendido.
     */
    public function activate(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        session()->flash('success', 'El aliado fue activado nuevamente.');
    }

    /**
     * Reiniciar paginación cuando cambia la búsqueda.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $allies = Ally::query()
            ->with('user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('business_name', 'like', '%' . $this->search . '%')
                        ->orWhere('rif', 'like', '%' . $this->search . '%')
                        ->orWhere('city', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.allies-manager', [
            'allies' => $allies,
        ]);
    }
}