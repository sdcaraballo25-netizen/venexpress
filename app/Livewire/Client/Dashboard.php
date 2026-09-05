<?php

namespace App\Livewire\Client;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Incident;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $rejectionReason = '';

    public ?int $rejectingPackageId = null;

    public function acceptDelivery(int $packageId): void
    {
        try {
            $package = $this->clientPackage($packageId);

            if (! $package->requires_delivery) {
                throw new RuntimeException(
                    'Este paquete no requiere entrega a domicilio.'
                );
            }

            if (
                $package->delivery_status
                !== Package::DELIVERY_PENDING
            ) {
                throw new RuntimeException(
                    'Este paquete ya tiene una respuesta registrada.'
                );
            }

            $package->update([
                'delivery_status' =>
                    Package::DELIVERY_ACCEPTED,

                'delivery_accepted_at' =>
                    now(),
            ]);

            AuditLog::create([
                'actor_user_id' => Auth::id(),
                'action' => 'client.delivery_accepted',
                'target_type' => Package::class,
                'target_id' => $package->id,
                'description' => "El cliente aceptó la entrega a domicilio de la guía {$package->tracking_number}.",
                'metadata' => [
                    'tracking_number' => $package->tracking_number,
                ],
                'ip_address' => request()?->ip(),
            ]);

            session()->flash(
                'success',
                'Has aceptado la entrega a domicilio.'
            );
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    public function startRejectDelivery(int $packageId): void
    {
        $this->clientPackage($packageId);

        $this->rejectingPackageId = $packageId;
        $this->rejectionReason = '';
    }

    public function cancelRejectDelivery(): void
    {
        $this->rejectingPackageId = null;
        $this->rejectionReason = '';
    }

    public function rejectDelivery(): void
    {
        try {
            if (! $this->rejectingPackageId) {
                throw new RuntimeException(
                    'No se seleccionó ningún paquete.'
                );
            }

            $this->validate([
                'rejectionReason' => [
                    'required',
                    'string',
                    'min:5',
                    'max:1000',
                ],
            ]);

            $package = $this->clientPackage(
                $this->rejectingPackageId
            );

            if (! $package->requires_delivery) {
                throw new RuntimeException(
                    'Este paquete no requiere entrega a domicilio.'
                );
            }

            if (
                $package->delivery_status
                !== Package::DELIVERY_PENDING
            ) {
                throw new RuntimeException(
                    'Este paquete ya tiene una respuesta registrada.'
                );
            }

            $package->update([
                'delivery_status' =>
                    Package::DELIVERY_REJECTED,

                'delivery_rejected_at' =>
                    now(),

                'delivery_rejection_reason' =>
                    $this->rejectionReason,

                'driver_remuneration_status' =>
                    Package::REMUNERATION_CANCELLED,
            ]);

            $incident = Incident::create([
                'ally_id' =>
                    $package->ally_id,

                'package_id' =>
                    $package->id,

                'reported_by_user_id' =>
                    Auth::id(),

                'type' =>
                    'ENTREGA_RECHAZADA',

                'description' =>
                    'El cliente rechazó la entrega a domicilio. '
                    . 'Motivo: '
                    . $this->rejectionReason,

                'status' =>
                    Incident::STATUS_OPEN,
            ]);

            AuditLog::create([
                'actor_user_id' => Auth::id(),
                'action' => 'client.delivery_rejected',
                'target_type' => Package::class,
                'target_id' => $package->id,
                'description' => "El cliente rechazó la entrega a domicilio de la guía {$package->tracking_number}. Motivo: {$this->rejectionReason}",
                'metadata' => [
                    'tracking_number' => $package->tracking_number,
                    'reason' => $this->rejectionReason,
                    'incident_id' => $incident->id,
                ],
                'ip_address' => request()?->ip(),
            ]);

            $this->rejectingPackageId = null;
            $this->rejectionReason = '';

            session()->flash(
                'success',
                'La entrega fue rechazada y se registró la incidencia.'
            );
        } catch (RuntimeException $e) {
            session()->flash(
                'error',
                $e->getMessage()
            );
        }
    }

    /**
     * Hallazgo de auditoría #6: customers.email no es único (a
     * propósito: varios familiares pueden compartir un correo con
     * cédulas distintas). Antes este método tomaba solo el PRIMER
     * Customer encontrado con ->first(), lo que podía dejar fuera
     * paquetes de otros id_doc asociados al mismo correo. Ahora se
     * consideran TODOS los id_doc registrados con ese correo.
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

    protected function clientPackage(int $packageId): Package
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        if (empty($idDocs)) {
            throw new RuntimeException(
                'No existe un registro de cliente asociado a tu cuenta.'
            );
        }

        return Package::query()
            ->whereKey($packageId)
            ->whereIn(
                'recipient_id_doc',
                $idDocs
            )
            ->firstOrFail();
    }

    public function render()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        $packages = collect();

        if (! empty($idDocs)) {
            $packages = Package::query()
                ->whereIn(
                    'recipient_id_doc',
                    $idDocs
                )
                ->with([
                    'histories',
                    'incidents',
                ])
                ->latest()
                ->get();
        }

        return view(
            'livewire.client.dashboard',
            [
                'packages' => $packages,
            ]
        );
    }
}
