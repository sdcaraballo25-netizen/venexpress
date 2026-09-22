<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public', ['title' => 'Términos y Condiciones — Venexpress'])]
#[Title('Términos y Condiciones')]
class TermsAndConditions extends Component
{
    public function render()
    {
        return view('public.terms-and-conditions');
    }
}
