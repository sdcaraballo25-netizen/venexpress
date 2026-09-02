<?php

namespace App\Livewire\Client;

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

            Incident::create([
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

    protected function clientPackage(int $packageId): Package
    {
        $user = Auth::user();

        $customer = Customer::query()
            ->where('email', $user->email)
            ->first();

        if (! $customer) {
            throw new RuntimeException(
                'No existe un registro de cliente asociado a tu cuenta.'
            );
        }

        return Package::query()
            ->whereKey($packageId)
            ->where(
                'recipient_id_doc',
                $customer->id_doc
            )
            ->firstOrFail();
    }

    public function render()
    {
        $user = Auth::user();

        $customer = Customer::query()
            ->where('email', $user->email)
            ->first();

        $packages = collect();

        if ($customer) {
            $packages = Package::query()
                ->where(
                    'recipient_id_doc',
                    $customer->id_doc
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
