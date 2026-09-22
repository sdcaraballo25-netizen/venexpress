<?php

namespace App\Livewire\Recommendations;

use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Formulario de recomendaciones para un usuario ya autenticado
 * (Cliente, Aliado, Repartidor, Almacén). Mismo modelo que el
 * formulario público (App\Livewire\Public\RecommendationForm), pero
 * nombre/correo se toman de la cuenta en vez de pedirlos de nuevo, y
 * queda enlazada a ese usuario (user_id) para que el admin sepa quién
 * la envió.
 */
class Create extends Component
{
    public string $message = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        $user = Auth::user();

        Recommendation::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'message' => $validated['message'],
            'status' => Recommendation::STATUS_NEW,
        ]);

        $this->reset(['message']);

        $this->submitted = true;
    }

    protected function resolveLayout(): string
    {
        $user = Auth::user();

        return match (true) {
            $user->isAliadoModule() => 'layouts.ally',
            $user->isRepartidor() => 'layouts.driver',
            $user->isAlmacen() => 'layouts.almacen',
            $user->isEmprendedor() => 'layouts.emprendedor',
            default => 'layouts.client',
        };
    }

    public function render()
    {
        return view('livewire.recommendations.create')
            ->layout($this->resolveLayout());
    }
}
