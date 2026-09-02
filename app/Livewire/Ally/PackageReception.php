<?php

namespace App\Livewire\Ally;

use App\Models\Package;
use App\Services\DestinationReceptionService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.ally')]
class PackageReception extends Component
{
    public string $trackingNumber = '';
    public ?Package $package = null;
    public ?string $message = null;
    public ?string $error = null;

    public function search(): void
    {
        $this->package = null;
        $this->message = null;
        $this->error = null;
        $this->trackingNumber = trim($this->trackingNumber);

        if ($this->trackingNumber === '') {
            $this->error = 'Introduce el número de guía.';
            return;
        }

        $ally = auth()->user()->resolveAlly();
        if (! $ally) abort(403);

        $package = Package::query()
            ->where('tracking_number', $this->trackingNumber)
            ->with(['ally', 'histories'])
            ->first();

        if (! $package) {
            $this->error = 'Guía no encontrada.';
            return;
        }

        if (mb_strtolower(trim((string) $package->destination_city)) !== mb_strtolower(trim((string) $ally->city))) {
            $this->error = 'Esta guía no pertenece a la ciudad de destino de tu agencia.';
            return;
        }

        $this->package = $package;
    }

    public function receive(): void
    {
        $this->message = null;
        $this->error = null;
        $this->validate(['trackingNumber' => ['required', 'string', 'max:50']]);

        try {
            $ally = auth()->user()->resolveAlly();
            if (! $ally) abort(403);

            $package = Package::where('tracking_number', trim($this->trackingNumber))->firstOrFail();

            if (mb_strtolower(trim((string) $package->destination_city)) !== mb_strtolower(trim((string) $ally->city))) {
                throw new RuntimeException('La guía no corresponde a la ciudad de esta agencia.');
            }

            $this->package = app(DestinationReceptionService::class)->receive(
                package: $package,
                userId: (int) auth()->id(),
                destinationLocation: $ally->business_name,
            );

            $this->message = 'Recepción registrada. El paquete quedó LISTO_RETIRO.';
        } catch (RuntimeException $e) {
            $this->error = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.ally.package-reception');
    }
}
