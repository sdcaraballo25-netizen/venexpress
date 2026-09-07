<?php

namespace App\Livewire\Admin;

use App\Models\Incident;
use App\Services\IncidentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.admin')]
#[Title('Incidencias')]
class IncidentsManager extends Component
{
    use WithPagination;

    public string $status = 'abierta';

    public string $search = '';

    /*
    |--------------------------------------------------------------------------
    | Modal de notas de resolución
    |--------------------------------------------------------------------------
    |
    | Se muestra cuando el destino es 'resuelta' o 'cerrada' y la
    | incidencia todavía no tiene resolution_notes. Si ya las tiene
    | (por ejemplo, se resolvió antes y ahora solo se cierra), el
    | cambio se aplica directo sin pedirlas de nuevo.
    |
    */

    public bool $showResolutionModal = false;

    public ?int $pendingIncidentId = null;

    public string $pendingStatus = '';

    public string $resolutionNotesInput = '';

    public const STATUS_LABELS = [
        Incident::STATUS_OPEN => 'Abierta',
        Incident::STATUS_IN_PROGRESS => 'En proceso',
        Incident::STATUS_RESOLVED => 'Resuelta',
        Incident::STATUS_CLOSED => 'Cerrada',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Punto de entrada desde el <select> de la tabla.
     *
     * El rol se valida aquí mismo, no solo mediante el middleware de
     * la ruta: las peticiones de actualización de Livewire son
     * llamadas independientes de la carga inicial de la página, así
     * que la acción debe protegerse por sí sola.
     */
    public function updateStatus(int $id, string $status): void
    {
        /** @var \App\Models\User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->isAdmin(), 403);

        if (! in_array($status, Incident::STATUSES, true)) {
            return;
        }

        $incident = Incident::findOrFail($id);

        // Misma regla que aplica el servicio: se repite aquí solo
        // para evitar mostrarle al usuario un modal que de todas
        // formas el servicio va a rechazar. La autorización real y
        // definitiva vive en IncidentService::updateStatus().
        if ($incident->status === Incident::STATUS_CLOSED && ! $actor->isAdminPrincipal()) {
            session()->flash(
                'error',
                'Solo un Administrador Principal puede modificar una incidencia cerrada.'
            );

            return;
        }

        $needsResolutionNotes = in_array($status, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)
            && trim((string) $incident->resolution_notes) === '';

        if ($needsResolutionNotes) {
            $this->pendingIncidentId = $id;
            $this->pendingStatus = $status;
            $this->resolutionNotesInput = '';
            $this->resetErrorBag('resolutionNotesInput');
            $this->showResolutionModal = true;

            return;
        }

        $this->applyStatusChange($id, $status);
    }

    /**
     * Confirma el cambio de estado desde el modal de notas de
     * resolución.
     *
     * El modal solo se cierra si el cambio realmente se aplicó. Si
     * el servicio rechaza la operación (regla de negocio o de
     * autorización), el modal permanece abierto, las notas escritas
     * no se pierden, y el error queda visible dentro del propio
     * modal.
     */
    public function confirmResolution(): void
    {
        if (! $this->pendingIncidentId || $this->pendingStatus === '') {
            $this->cancelResolution();

            return;
        }

        if (trim($this->resolutionNotesInput) === '') {
            $this->addError('resolutionNotesInput', 'Debes indicar las notas de resolución.');

            return;
        }

        $succeeded = $this->applyStatusChange(
            $this->pendingIncidentId,
            $this->pendingStatus,
            $this->resolutionNotesInput
        );

        if ($succeeded) {
            $this->cancelResolution();
        }
    }

    public function cancelResolution(): void
    {
        $this->showResolutionModal = false;
        $this->pendingIncidentId = null;
        $this->pendingStatus = '';
        $this->resolutionNotesInput = '';
        $this->resetErrorBag('resolutionNotesInput');
    }

    /**
     * Aplica el cambio delegando toda la lógica de negocio, la
     * autorización, la auditoría y el historial de guía al servicio
     * existente.
     *
     * @return bool true únicamente si el cambio se aplicó de verdad.
     *              false si el servicio lo rechazó por una regla de
     *              negocio (ej. notas faltantes, incidencia cerrada
     *              sin ser admin_principal). Nunca se convierte un
     *              error real en un "éxito" silencioso.
     */
    protected function applyStatusChange(int $id, string $status, ?string $resolutionNotes = null): bool
    {
        /** @var \App\Models\User|null $actor */
        $actor = Auth::user();

        abort_unless($actor?->isAdmin(), 403);

        try {
            app(IncidentService::class)->updateStatus(
                incident: Incident::findOrFail($id),
                status: $status,
                actor: $actor,
                resolutionNotes: $resolutionNotes,
            );

            session()->flash('success', 'Incidencia actualizada.');

            return true;
        } catch (AuthorizationException $e) {
            // Fallo de autorización real: no se disfraza como error
            // de negocio, se corta la petición con 403.
            abort(403, $e->getMessage());
        } catch (RuntimeException $e) {
            // Error de negocio (notas faltantes, incidencia cerrada,
            // estado inválido, etc.). Se muestra tanto en el flash
            // de sesión (por si no hay modal abierto) como en el
            // propio campo de notas (visible dentro del modal, que
            // el llamador dejará abierto al recibir `false`).
            session()->flash('error', $e->getMessage());
            $this->addError('resolutionNotesInput', $e->getMessage());

            return false;
        }
    }

    public function render()
    {
        $query = Incident::with(['package', 'ally', 'reportedByUser'])
            ->latest();

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $search = trim($this->search);

            $query->where(function ($q) use ($search) {
                $q->where('type', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'package',
                        fn ($p) => $p->where('tracking_number', 'like', "%{$search}%")
                    );
            });
        }

        return view('livewire.admin.incidents-manager', [
            'incidents' => $query->paginate(15),
        ]);
    }
}
