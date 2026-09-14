<?php

namespace App\Livewire\Public;

use App\Models\Recommendation;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Formulario público de recomendaciones/sugerencias. Cualquier
 * visitante puede enviar una, sin necesidad de cuenta.
 */
#[Layout('layouts.public', ['title' => 'Envíanos tu recomendación — Venexpress'])]
class RecommendationForm extends Component
{
    public string $name = '';
    public string $email = '';
    public string $message = '';

    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Recommendation::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?: null,
            'message' => $validated['message'],
            'status' => Recommendation::STATUS_NEW,
        ]);

        $this->reset(['name', 'email', 'message']);

        $this->submitted = true;
    }

    public function render()
    {
        return view('public.recommendation-form');
    }
}
