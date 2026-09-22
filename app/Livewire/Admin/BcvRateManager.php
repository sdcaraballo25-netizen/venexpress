<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\BcvRate;
use App\Services\BcvRateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.admin')]
#[Title('Tasa BCV')]
class BcvRateManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $rate = '';

    public string $effective_date = '';

    protected function rules(): array
    {
        return [
            'rate' => ['required', 'numeric', 'min:0.000001'],
            'effective_date' => [
                'required',
                'date',
            ],
        ];
    }

    protected $messages = [
        'rate.required' => 'Ingresa el valor de la tasa.',
        'rate.numeric' => 'La tasa debe ser un número.',
        'rate.min' => 'La tasa debe ser mayor a cero.',
        'effective_date.required' => 'Selecciona la fecha de vigencia.',
        'effective_date.unique' => 'Ya existe una tasa registrada para esa fecha.',
    ];

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    /**
     * BcvRate::current() decide la tasa vigente ordenando por
     * effective_at (no por effective_date), justamente para poder
     * distinguir dos tasas publicadas el mismo día. Si aquí siempre
     * usáramos now(), un admin corrigiendo una tasa de una fecha
     * pasada la volvería "vigente" al instante sin importar qué fecha
     * eligió. Para la fecha de hoy usamos now() (para que una
     * corrección manual de hoy sí desplace a la sincronización
     * automática de hoy); para cualquier otra fecha usamos el final de
     * ese día, así ordena correctamente contra tasas de otros días.
     */
    protected function resolveEffectiveAt(?BcvRate $existing = null): Carbon
    {
        $date = Carbon::parse($this->effective_date);

        if ($date->isToday()) {
            return now();
        }

        // Si estamos editando y la fecha de vigencia no cambió, no
        // tocamos el effective_at existente: pudo venir de una
        // sincronización automática con hora precisa (ej. la
        // publicación de la tarde de BCV), y recalcularlo a fin de día
        // lo desordenaría frente a otra tasa del mismo día que sí
        // conserva su hora real.
        if ($existing && $existing->effective_date->isSameDay($date)) {
            return $existing->effective_at;
        }

        return $date->endOfDay();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $bcvRate = BcvRate::findOrFail($this->editingId);
            $previousRate = (float) $bcvRate->rate;

            $bcvRate->update([
                'rate' => $this->rate,
                'effective_date' => $this->effective_date,
                'effective_at' => $this->resolveEffectiveAt($bcvRate),
                'source' => 'manual',
            ]);

            AuditLog::create([
                'actor_user_id' => Auth::id(),
                'action' => 'bcv_rate.updated',
                'target_type' => BcvRate::class,
                'target_id' => $bcvRate->id,
                'description' => "Editó manualmente la tasa BCV de {$previousRate} a {$this->rate}.",
                'metadata' => [
                    'previous_rate' => $previousRate,
                    'new_rate' => (float) $this->rate,
                    'effective_date' => $this->effective_date,
                ],
                'ip_address' => request()?->ip(),
            ]);

            session()->flash('success', 'Tasa actualizada correctamente.');
        } else {
            $bcvRate = BcvRate::create([
                'rate' => $this->rate,
                'effective_date' => $this->effective_date,
                'effective_at' => $this->resolveEffectiveAt(),
                'source' => 'manual',
            ]);

            AuditLog::create([
                'actor_user_id' => Auth::id(),
                'action' => 'bcv_rate.created',
                'target_type' => BcvRate::class,
                'target_id' => $bcvRate->id,
                'description' => "Registró manualmente una nueva tasa BCV de {$this->rate}.",
                'metadata' => [
                    'rate' => (float) $this->rate,
                    'effective_date' => $this->effective_date,
                ],
                'ip_address' => request()?->ip(),
            ]);

            session()->flash('success', 'Nueva tasa registrada correctamente.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $bcvRate = BcvRate::findOrFail($id);

        $this->editingId = $bcvRate->id;
        $this->rate = (string) $bcvRate->rate;
        $this->effective_date = $bcvRate->effective_date->format('Y-m-d');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /**
     * Consulta la API oficial ahora mismo, sin esperar al cron/scheduler.
     * Usa el mismo servicio que el comando `bcv:sync`.
     */
    public function syncNow(BcvRateService $service): void
    {
        try {
            $rate = $service->syncFromApi();

            if ($rate) {
                AuditLog::create([
                    'actor_user_id' => Auth::id(),
                    'action' => 'bcv_rate.synced',
                    'target_type' => BcvRate::class,
                    'target_id' => $rate->id,
                    'description' => "Sincronizó manualmente la tasa BCV desde la API: {$rate->rate}.",
                    'metadata' => ['rate' => (float) $rate->rate, 'source' => $rate->source],
                    'ip_address' => request()?->ip(),
                ]);
            }

            session()->flash(
                'success',
                $rate
                    ? 'Tasa sincronizada: ' . number_format((float) $rate->rate, 2) . ' Bs. por USD.'
                    : 'Ya estás al día: la tasa oficial no ha cambiado desde la última sincronización.'
            );
        } catch (\Throwable $e) {
            session()->flash(
                'error',
                'No se pudo consultar la tasa oficial. Verifica tu conexión a internet e inténtalo de nuevo. (' . $e->getMessage() . ')'
            );
        }
    }

    public function delete(int $id): void
    {
        // No permitir borrar la única tasa existente.
        if (BcvRate::count() <= 1) {
            session()->flash('error', 'No puedes eliminar la única tasa registrada.');

            return;
        }

        // Tampoco la tasa vigente: borrarla revertiría todo el sistema
        // a la tasa anterior sin ningún aviso.
        if (BcvRate::current()?->id === $id) {
            session()->flash('error', 'No puedes eliminar la tasa vigente. Registra o sincroniza una tasa más reciente primero.');

            return;
        }

        $bcvRate = BcvRate::findOrFail($id);
        $deletedRate = (float) $bcvRate->rate;
        $bcvRate->delete();

        AuditLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'bcv_rate.deleted',
            'target_type' => BcvRate::class,
            'target_id' => $id,
            'description' => "Eliminó la tasa BCV de {$deletedRate}.",
            'metadata' => ['rate' => $deletedRate],
            'ip_address' => request()?->ip(),
        ]);

        session()->flash('success', 'Tasa eliminada.');
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'rate']);
        $this->effective_date = now()->format('Y-m-d');
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.bcv-rate-manager', [
            'current' => BcvRate::current(),
            'history' => BcvRate::query()
                ->orderByDesc('effective_date')
                ->paginate(10),
        ]);
    }
}