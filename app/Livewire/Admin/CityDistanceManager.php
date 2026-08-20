<?php

namespace App\Livewire\Admin;

use App\Models\CityDistance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CRUD de distancias entre ciudades, usadas por TariffService para
 * calcular el componente de precio por km de cada guía.
 *
 * Las distancias son simétricas (A→B = B→A): el modelo CityDistance
 * se encarga de normalizar el orden para no duplicar la ruta inversa.
 */
#[Layout('layouts.admin')]
#[Title('Distancias entre ciudades')]
class CityDistanceManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $city_one = '';

    public string $city_two = '';

    public string $distance_km = '';

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'city_one' => ['required', 'string', 'max:255'],
            'city_two' => ['required', 'string', 'max:255', 'different:city_one'],
            'distance_km' => ['required', 'integer', 'min:1'],
        ];
    }

    protected $messages = [
        'city_one.required' => 'Ingresa la primera ciudad.',
        'city_two.required' => 'Ingresa la segunda ciudad.',
        'city_two.different' => 'Las dos ciudades deben ser distintas.',
        'distance_km.required' => 'Ingresa la distancia en kilómetros.',
        'distance_km.integer' => 'La distancia debe ser un número entero de km.',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        // Si estamos editando, primero liberamos esa fila del chequeo de duplicados.
        $existing = CityDistance::between($this->city_one, $this->city_two);

        if ($existing && $existing->id !== $this->editingId) {
            $this->addError('city_two', 'Ya existe una distancia registrada para estas dos ciudades.');

            return;
        }

        if ($this->editingId && $existing?->id === $this->editingId) {
            // Se está editando la misma pareja de ciudades: solo actualiza el km.
            $existing->update(['distance_km' => $this->distance_km]);
        } elseif ($this->editingId) {
            // Se cambiaron las ciudades al editar: hay que reemplazar la fila.
            CityDistance::find($this->editingId)?->delete();
            CityDistance::setDistance($this->city_one, $this->city_two, (int) $this->distance_km);
        } else {
            CityDistance::setDistance($this->city_one, $this->city_two, (int) $this->distance_km);
        }

        session()->flash('success', 'Distancia guardada correctamente.');
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $distance = CityDistance::findOrFail($id);

        $this->editingId = $distance->id;
        $this->city_one = $distance->city_a;
        $this->city_two = $distance->city_b;
        $this->distance_km = (string) $distance->distance_km;
        $this->showForm = true;
    }

    public function delete(int $id): void
    {
        CityDistance::findOrFail($id)->delete();
        session()->flash('success', 'Distancia eliminada.');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'city_one', 'city_two', 'distance_km', 'showForm']);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.city-distance-manager', [
            'distances' => CityDistance::query()
                ->orderBy('city_a')
                ->orderBy('city_b')
                ->paginate(10),
        ]);
    }
}
