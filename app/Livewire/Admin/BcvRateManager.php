<?php

namespace App\Livewire\Admin;

use App\Models\BcvRate;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

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
                $this->editingId
                    ? 'unique:bcv_rates,effective_date,' . $this->editingId
                    : 'unique:bcv_rates,effective_date',
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

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $bcvRate = BcvRate::findOrFail($this->editingId);
            $bcvRate->update([
                'rate' => $this->rate,
                'effective_date' => $this->effective_date,
            ]);
            session()->flash('success', 'Tasa actualizada correctamente.');
        } else {
            BcvRate::create([
                'rate' => $this->rate,
                'effective_date' => $this->effective_date,
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

    public function delete(int $id): void
    {
        // No permitir borrar la única tasa existente o la vigente si es la última.
        if (BcvRate::count() <= 1) {
            session()->flash('error', 'No puedes eliminar la única tasa registrada.');

            return;
        }

        BcvRate::findOrFail($id)->delete();
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