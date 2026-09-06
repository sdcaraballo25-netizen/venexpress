<?php

namespace App\Livewire\Client;

use App\Models\Customer;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.client')]
class Incidents extends Component
{
    use WithPagination;

    public string $trackingNumber = '';

    public string $description = '';

    /**
     * Igual que en Client\Dashboard: un cliente puede tener varios
     * id_doc asociados a su correo (p. ej. familiares que comparten
     * cuenta), así que se consideran todos.
     *
     * @return list<string>
     */
    protected function customerIdDocsForCurrentUser(): array
    {
        $user = Auth::user();

        return Customer::query()
            ->where('email', $user->email)
            ->pluck('id_doc')
            ->all();
    }

    /**
     * Paquetes sobre los que el cliente puede reportar una
     * incidencia: donde aparece como remitente o como destinatario.
     */
    protected function clientPackagesQuery()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        return Package::query()->where(function ($query) use ($idDocs) {
            $query->whereIn('recipient_id_doc', $idDocs)
                ->orWhereIn('sender_id_doc', $idDocs);
        });
    }

    /**
     * Crea un nuevo reporte del cliente sobre uno de sus paquetes.
     *
     * Reutiliza el mismo modelo Incident que usan los Aliados
     * (Incident::TYPE_RECLAMO_CLIENTE los diferencia en el listado
     * de Admin), en vez de duplicar una tabla nueva.
     */
    public function create(): void
    {
        $this->validate([
            'trackingNumber' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'trackingNumber.required' => 'Indica el número de guía.',
            'description.required' => 'Describe brevemente el problema.',
            'description.min' => 'Danos un poco más de detalle (mínimo 5 caracteres).',
        ]);

        $package = $this->clientPackagesQuery()
            ->where('tracking_number', trim($this->trackingNumber))
            ->first();

        if (! $package) {
            $this->addError(
                'trackingNumber',
                'Esa guía no está asociada a tu cuenta. Verifica el número.'
            );

            return;
        }

        Incident::create([
            'ally_id' => $package->ally_id,
            'package_id' => $package->id,
            'reported_by_user_id' => Auth::id(),
            'type' => Incident::TYPE_RECLAMO_CLIENTE,
            'description' => $this->description,
            'status' => Incident::STATUS_OPEN,
        ]);

        $this->reset(['trackingNumber', 'description']);

        session()->flash(
            'success',
            'Tu reporte fue registrado. Nuestro equipo lo revisará pronto.'
        );
    }

    public function render()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        $incidents = Incident::query()
            ->whereHas('package', function ($query) use ($idDocs) {
                $query->whereIn('recipient_id_doc', $idDocs)
                    ->orWhereIn('sender_id_doc', $idDocs);
            })
            ->with('package')
            ->latest()
            ->paginate(10);

        return view('livewire.client.incidents', [
            'incidents' => $incidents,
        ]);
    }
}
