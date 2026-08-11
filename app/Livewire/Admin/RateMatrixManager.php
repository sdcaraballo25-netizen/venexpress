<?php

namespace App\Livewire\Admin;

use App\Models\RateMatrix;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('Matrices de tarifas')]
class RateMatrixManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $origin_city = '';

    public string $destination_city = '';

    public string $price_per_kg_usd = '';

    public string $base_price_usd = '0';

    public bool $showForm = false;

    protected function rules(): array
    {
        $uniqueRule = 'unique:rate_matrices,origin_city,NULL,id,destination_city,' . $this->destination_city;

        return [
            'origin_city' => ['required', 'string', 'max:255'],
            'destination_city' => ['required', 'string', 'max:255'],
            'price_per_kg_usd' => ['required', 'numeric', 'min:0'],
            'base_price_usd' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected $messages = [
        'origin_city.required' => 'Ingresa la ciudad de origen.',
        'destination_city.required' => 'Ingresa la ciudad de destino.',
        'price_per_kg_usd.required' => 'Ingresa el precio por kg.',
        'base_price_usd.required' => 'Ingresa el precio base.',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $exists = RateMatrix::query()
            ->where('origin_city', $this->origin_city)
            ->where('destination_city', $this->destination_city)
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->exists();

        if ($exists) {
            $this->addError('destination_city', 'Ya existe una tarifa configurada para esta ruta.');

            return;
        }

        if ($this->editingId) {
            RateMatrix::findOrFail($this->editingId)->update([
                'origin_city' => $this->origin_city,
                'destination_city' => $this->destination_city,
                'price_per_kg_usd' => $this->price_per_kg_usd,
                'base_price_usd' => $this->base_price_usd,
            ]);
            session()->flash('success', 'Ruta actualizada correctamente.');
        } else {
            RateMatrix::create([
                'origin_city' => $this->origin_city,
                'destination_city' => $this->destination_city,
                'price_per_kg_usd' => $this->price_per_kg_usd,
                'base_price_usd' => $this->base_price_usd,
            ]);
            session()->flash('success', 'Ruta creada correctamente.');
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $matrix = RateMatrix::findOrFail($id);

        $this->editingId = $matrix->id;
        $this->origin_city = $matrix->origin_city;
        $this->destination_city = $matrix->destination_city;
        $this->price_per_kg_usd = (string) $matrix->price_per_kg_usd;
        $this->base_price_usd = (string) $matrix->base_price_usd;
        $this->showForm = true;
    }

    public function delete(int $id): void
    {
        RateMatrix::findOrFail($id)->delete();
        session()->flash('success', 'Ruta eliminada.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'origin_city', 'destination_city', 'price_per_kg_usd', 'showForm']);
        $this->base_price_usd = '0';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.rate-matrix-manager', [
            'matrices' => RateMatrix::query()
                ->orderBy('origin_city')
                ->orderBy('destination_city')
                ->paginate(10),
        ]);
    }
}