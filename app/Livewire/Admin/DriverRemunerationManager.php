<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\DriverRemunerationRate;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Remuneración de Repartidores')]
class DriverRemunerationManager extends Component
{
    use WithPagination;

    public string $amount_usd = '';

    protected function rules(): array
    {
        return [
            'amount_usd' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    protected $messages = [
        'amount_usd.required' => 'Ingresa el monto por paquete entregado.',
        'amount_usd.numeric' => 'El monto debe ser un número.',
        'amount_usd.min' => 'El monto debe ser mayor a cero.',
    ];

    public function mount(): void
    {
        $current = DriverRemunerationRate::current();

        $this->amount_usd = $current
            ? (string) $current->amount_usd
            : '';
    }

    /**
     * Registra una nueva tarifa vigente. No se edita la tarifa
     * anterior: se crea una nueva fila, igual que con BcvRate, para
     * conservar el historial de cuánto se pagaba en cada momento
     * (relevante si en el futuro se audita una remuneración vieja).
     */
    public function save(): void
    {
        $this->validate();

        $previous = DriverRemunerationRate::current();

        $rate = DriverRemunerationRate::create([
            'amount_usd' => $this->amount_usd,
            'effective_at' => now(),
            'source' => 'manual',
            'created_by_user_id' => Auth::id(),
        ]);

        AuditLog::create([
            'actor_user_id' => Auth::id(),
            'action' => 'driver_remuneration_rate.updated',
            'target_type' => DriverRemunerationRate::class,
            'target_id' => $rate->id,
            'description' => $previous
                ? "Cambió la remuneración por paquete entregado de \${$previous->amount_usd} a \${$this->amount_usd}."
                : "Registró la primera remuneración por paquete entregado: \${$this->amount_usd}.",
            'metadata' => [
                'previous_amount_usd' => $previous ? (float) $previous->amount_usd : null,
                'new_amount_usd' => (float) $this->amount_usd,
            ],
            'ip_address' => request()?->ip(),
        ]);

        session()->flash(
            'success',
            'Tarifa de remuneración actualizada correctamente. Aplica a partir de ahora para toda entrega completada.'
        );
    }

    public function render()
    {
        return view('livewire.admin.driver-remuneration-manager', [
            'current' => DriverRemunerationRate::current(),
            'history' => DriverRemunerationRate::query()
                ->with('createdBy')
                ->orderByDesc('effective_at')
                ->paginate(10),
        ]);
    }
}
