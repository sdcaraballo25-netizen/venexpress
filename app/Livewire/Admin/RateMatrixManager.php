<?php

namespace App\Livewire\Admin;

use App\Models\RateMatrix;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Tarifa global de Venexpress (base + por kg + por km).
 *
 * Ya no es "por ruta": la diferencia de precio entre ciudades la
 * aporta la distancia configurada en CityDistanceManager. Aquí solo
 * se administra una fila (se edita si ya existe, se crea si no).
 */
#[Layout('layouts.admin')]
#[Title('Tarifa')]
class RateMatrixManager extends Component
{
    public ?int $rateMatrixId = null;

    public string $base_price_usd = '0';

    public string $price_per_kg_usd = '';

    public string $price_per_km_usd = '';

    protected function rules(): array
    {
        return [
            'base_price_usd' => ['required', 'numeric', 'min:0'],
            'price_per_kg_usd' => ['required', 'numeric', 'min:0'],
            'price_per_km_usd' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected $messages = [
        'base_price_usd.required' => 'Ingresa el precio base.',
        'price_per_kg_usd.required' => 'Ingresa el precio por kg.',
        'price_per_km_usd.required' => 'Ingresa el precio por km.',
    ];

    public function mount(): void
    {
        $current = RateMatrix::current();

        if ($current) {
            $this->rateMatrixId = $current->id;
            $this->base_price_usd = (string) $current->base_price_usd;
            $this->price_per_kg_usd = (string) $current->price_per_kg_usd;
            $this->price_per_km_usd = (string) $current->price_per_km_usd;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'base_price_usd' => $this->base_price_usd,
            'price_per_kg_usd' => $this->price_per_kg_usd,
            'price_per_km_usd' => $this->price_per_km_usd,
        ];

        if ($this->rateMatrixId) {
            RateMatrix::findOrFail($this->rateMatrixId)->update($data);
        } else {
            $rateMatrix = RateMatrix::create($data);
            $this->rateMatrixId = $rateMatrix->id;
        }

        session()->flash('success', 'Tarifa actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.rate-matrix-manager');
    }
}
