<?php

namespace App\Livewire\Client;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

#[Layout('layouts.client')]
class Dashboard extends Component
{
    use WithPagination;

    public const TAB_PENDING = 'pending';

    public const TAB_HISTORY = 'history';

    public string $activeTab = self::TAB_PENDING;

    /**
     * Filtro de fecha del historial (rango sobre delivery_completed_at).
     * Inputs <input type="date">, así que llegan como 'YYYY-MM-DD' o ''.
     */
    public string $historyFrom = '';

    public string $historyTo = '';

    public function showPending(): void
    {
        $this->activeTab = self::TAB_PENDING;
    }

    public function showHistory(): void
    {
        $this->activeTab = self::TAB_HISTORY;
    }

    public function updatedHistoryFrom(): void
    {
        $this->resetPage();
    }

    public function updatedHistoryTo(): void
    {
        $this->resetPage();
    }

    public function clearHistoryFilters(): void
    {
        $this->reset(['historyFrom', 'historyTo']);
        $this->resetPage();
    }

    /**
     * Confirma que el cliente recibirá el paquete a domicilio. Esto NO
     * marca el paquete como entregado: solo da el visto bueno para que
     * un repartidor pueda tomarlo (DeliveryAssignmentService::assign()
     * exige delivery_status === DELIVERY_ACCEPTED antes de asignarlo a
     * una ruta, y PackageService::claimForDelivery() lo mismo cuando
     * un repartidor lo reclama). La entrega física la confirma el
     * repartidor con completeDelivery(), que es lo que de verdad pone
     * current_status en ENTREGADO.
     *
     * Solo se puede confirmar cuando el paquete ya está LISTO_RETIRO
     * (llegó a la agencia/hub destino) — antes de eso no tiene sentido
     * pedirle al cliente que confirme algo que todavía está en
     * tránsito.
     */
    public function acceptDelivery(int $packageId): void
    {
        try {
            $package = $this->clientPackage($packageId);

            if (! $package->requires_delivery) {
                throw new RuntimeException(
                    'Este paquete no requiere entrega a domicilio.'
                );
            }

            if ($package->current_status !== Package::STATUS_LISTO_RETIRO) {
                throw new RuntimeException(
                    'Todavía no puedes confirmar la recepción: tu paquete debe estar Listo para Retiro. '
                    .'Estado actual: '.$package->statusLabel().'.'
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
                'description' => "El cliente confirmó la recepción a domicilio de la guía {$package->tracking_number}.",
                'metadata' => [
                    'tracking_number' => $package->tracking_number,
                ],
                'ip_address' => request()?->ip(),
            ]);

            session()->flash(
                'success',
                'Confirmaste la recepción a domicilio. Un repartidor se pondrá en camino.'
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
            ->where(function ($query) use ($idDocs) {
                $query->whereIn('recipient_id_doc', $idDocs)
                    ->orWhereIn('sender_id_doc', $idDocs);
            })
            ->firstOrFail();
    }

    /**
     * Rol del cliente respecto a ESTE paquete en particular, para
     * poder rotularlo en la UI ("Enviado por ti" / "Para ti").
     */
    protected function withClientRole(Package $package, array $idDocs): Package
    {
        $package->client_role = in_array(
            $package->recipient_id_doc,
            $idDocs,
            true
        ) ? 'recipient' : 'sender';

        return $package;
    }

    /**
     * Trae los paquetes a nombre del cliente, ya sea que los envió
     * (sender_id_doc) o que los está recibiendo (recipient_id_doc),
     * separados en dos vistas:
     *
     * - Pendientes: todo lo que todavía no llegó a su destino final
     *   (current_status distinto de ENTREGADO). Es la vista por
     *   defecto.
     * - Historial: paquetes ya ENTREGADO, del más reciente al más
     *   antiguo, filtrable por rango de fecha de entrega.
     */
    public function render()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        $packages = collect();
        $historyPackages = null;

        if (! empty($idDocs)) {
            $baseQuery = fn () => Package::query()
                ->where(function ($query) use ($idDocs) {
                    $query->whereIn('recipient_id_doc', $idDocs)
                        ->orWhereIn('sender_id_doc', $idDocs);
                });

            if ($this->activeTab === self::TAB_HISTORY) {
                $historyPackages = $baseQuery()
                    ->where('current_status', Package::STATUS_ENTREGADO)
                    ->when(
                        $this->historyFrom !== '',
                        fn ($q) => $q->whereDate('delivery_completed_at', '>=', $this->historyFrom)
                    )
                    ->when(
                        $this->historyTo !== '',
                        fn ($q) => $q->whereDate('delivery_completed_at', '<=', $this->historyTo)
                    )
                    ->with(['histories', 'incidents'])
                    ->orderByDesc('delivery_completed_at')
                    ->paginate(10)
                    ->through(fn (Package $package) => $this->withClientRole($package, $idDocs));
            } else {
                $packages = $baseQuery()
                    ->where('current_status', '!=', Package::STATUS_ENTREGADO)
                    ->with(['histories', 'incidents'])
                    ->latest()
                    ->get()
                    ->map(fn (Package $package) => $this->withClientRole($package, $idDocs));
            }
        }

        return view(
            'livewire.client.dashboard',
            [
                'packages' => $packages,
                'historyPackages' => $historyPackages,
            ]
        );
    }
}
