<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public', ['title' => 'Política de Privacidad — Venexpress'])]
#[Title('Política de Privacidad')]
class PrivacyPolicy extends Component
{
    public function render()
    {
        return view('public.privacy-policy');
    }
}
