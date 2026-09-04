<?php

namespace App\Livewire\Tracking;

use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicTracking extends Component
{
    public string $guia = '';
    public ?Package $package = null;
    public ?string $message = null;

    public function search(): void
    {
        $this->validate(['guia' => ['required', 'string', 'max:80']]);
        $this->package = Package::query()
            ->where('tracking_number', trim($this->guia))
            ->with('histories')
            ->first();
        $this->message = $this->package ? null : 'No encontramos una guía con ese número.';
    }

    public function clear(): void
    {
        $this->reset(['guia', 'package', 'message']);
    }

    public function render()
    {
        return view('livewire.tracking.public-tracking');
    }
}
