<?php

namespace App\Livewire\Tracking;

use App\Models\Incident;
use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicTracking extends Component
{
    public string $guia = '';
    public ?Package $package = null;
    public ?string $message = null;
    public bool $hasOpenIncident = false;

    public function search(): void
    {
        $this->validate(['guia' => ['required', 'string', 'max:80']]);
        $this->package = Package::query()
            ->where('tracking_number', trim($this->guia))
            ->with('histories')
            ->first();
        $this->message = $this->package ? null : 'No encontramos una guía con ese número.';

        // Hallazgo de auditoría #5: los estados públicos de Package
        // no incluyen "devuelto"/"con incidencia", así que sin esto
        // el tracking simplemente se congela en el último paso
        // conocido sin explicar por qué. Verificamos si hay una
        // incidencia abierta para poder avisarle al cliente en vez
        // de dejarlo adivinando.
        $this->hasOpenIncident = $this->package
            ? $this->package->incidents()
                ->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_IN_PROGRESS])
                ->exists()
            : false;
    }

    public function clear(): void
    {
        $this->reset(['guia', 'package', 'message', 'hasOpenIncident']);
    }

    public function render()
    {
        return view('livewire.tracking.public-tracking');
    }
}
