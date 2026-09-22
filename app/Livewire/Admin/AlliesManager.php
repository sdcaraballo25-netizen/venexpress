<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\AuditLog;
use App\Notifications\AccountApproved;
use App\Notifications\AccountRejected;
use App\Services\VenezuelaLocationService;
use Illuminate\Validation\Rule;
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
     * Estado del modal de detalle (solo lectura) de un aliado.
     */
    public bool $showDetailsModal = false;

    public ?int $viewingAllyId = null;

    /**
     * Abre el modal de detalle con los datos completos del aliado,
     * incluyendo la foto de fachada en tamaño grande, para que el
     * Admin pueda revisarlos antes de aprobar/rechazar la postulación.
     */
    public function viewDetails(int $allyId): void
    {
        $this->viewingAllyId = $allyId;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->viewingAllyId = null;
    }

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
            'location_state' => ['required', 'string', Rule::in(app(VenezuelaLocationService::class)->states())],
            'location_latitude' => ['required', 'numeric', 'between:-90,90'],
            'location_longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [], [
            'location_state' => 'estado',
            'location_latitude' => 'latitud',
            'location_longitude' => 'longitud',
        ]);

        $ally = Ally::findOrFail($this->editingAllyId);

        $previousState = $ally->state;
        $previousLat = $ally->latitude;
        $previousLng = $ally->longitude;

        $ally->update([
            'state' => $this->location_state,
            'latitude' => $this->location_latitude,
            'longitude' => $this->location_longitude,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.location_updated',
            "Actualizó la ubicación de la agencia {$ally->business_name}.",
            [
                'previous' => [
                    'state' => $previousState,
                    'latitude' => $previousLat,
                    'longitude' => $previousLng,
                ],
                'new' => [
                    'state' => $this->location_state,
                    'latitude' => $this->location_latitude,
                    'longitude' => $this->location_longitude,
                ],
            ]
        );

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
        $previousStatus = $ally->status;

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.approved',
            "Aprobó al aliado {$ally->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Ally::STATUS_ACTIVE]
        );

        $ally->user?->notify(new AccountApproved('Aliado'));

        session()->flash('success', 'El aliado fue aprobado correctamente.');
    }

    /**
     * Rechazar un aliado.
     */
    public function reject(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);
        $previousStatus = $ally->status;

        $ally->update([
            'status' => Ally::STATUS_REJECTED,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.rejected',
            "Rechazó al aliado {$ally->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Ally::STATUS_REJECTED]
        );

        $ally->user?->notify(new AccountRejected('Aliado'));

        session()->flash('success', 'El aliado fue rechazado.');
    }

    /**
     * Suspender un aliado.
     */
    public function suspend(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);
        $previousStatus = $ally->status;

        $ally->update([
            'status' => Ally::STATUS_SUSPENDED,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.suspended',
            "Suspendió al aliado {$ally->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Ally::STATUS_SUSPENDED]
        );

        session()->flash('success', 'El aliado fue suspendido.');
    }

    /**
     * Reactivar un aliado suspendido.
     */
    public function activate(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);
        $previousStatus = $ally->status;

        $ally->update([
            'status' => Ally::STATUS_ACTIVE,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.activated',
            "Reactivó al aliado {$ally->business_name}.",
            ['previous_status' => $previousStatus, 'new_status' => Ally::STATUS_ACTIVE]
        );

        session()->flash('success', 'El aliado fue activado nuevamente.');
    }

    /**
     * Activa/desactiva manualmente la capacidad de "Aliado verificado
     * como punto final de entrega/retiro".
     *
     * ESTO ES UNA CONFIGURACIÓN ADMINISTRATIVA TEMPORAL: todavía no
     * existe el flujo real de verificación documental (solicitud ->
     * revisión -> aprobado/rechazado), así que por ahora el Admin
     * activa o desactiva el flag directamente. Cuando ese flujo se
     * construya, esta acción manual dejará de ser el único camino.
     */
    public function toggleVerifiedDestination(int $allyId): void
    {
        $ally = Ally::findOrFail($allyId);

        $newValue = ! $ally->is_verified_destination;

        $ally->update([
            'is_verified_destination' => $newValue,
            'destination_verification_status' => $newValue
                ? Ally::DESTINATION_VERIFICATION_APPROVED
                : null,
            'destination_verified_at' => $newValue ? now() : null,
        ]);

        $this->logAllyAction(
            $ally,
            'ally.destination_verification_toggled',
            $newValue
                ? "Marcó manualmente a {$ally->business_name} como punto verificado de entrega/retiro (configuración temporal)."
                : "Quitó la verificación de punto de entrega/retiro de {$ally->business_name}.",
            ['is_verified_destination' => $newValue]
        );

        session()->flash(
            'success',
            $newValue
                ? 'El aliado fue marcado como punto verificado de entrega/retiro.'
                : 'Se quitó la verificación de punto de entrega/retiro.'
        );
    }

    /**
     * Registra en la bitácora de auditoría una acción administrativa
     * sobre un aliado (cambio de estado o de ubicación).
     */
    protected function logAllyAction(Ally $ally, string $action, string $description, array $metadata = []): void
    {
        AuditLog::create([
            'actor_user_id' => auth()->id(),
            'action' => $action,
            'target_type' => Ally::class,
            'target_id' => $ally->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
        ]);
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
            'venezuelaStates' => app(VenezuelaLocationService::class)->states(),
            'viewingAlly' => $this->viewingAllyId
                ? Ally::with('user')->find($this->viewingAllyId)
                : null,
        ]);
    }
}