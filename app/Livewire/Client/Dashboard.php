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
     * Hallazgo de auditoría #6: customers.email no es único (a
     * propósito: varios familiares pueden compartir un correo con
     * cédulas distintas). Antes este método tomaba solo el PRIMER
     * Customer encontrado con ->first(), lo que podía dejar fuera
     * paquetes de otros id_doc asociados al mismo correo. Ahora se
     * consideran TODOS los id_doc registrados con ese correo.
     *
     * También se incluye, aparte, el id_doc vinculado por user_id
     * (la cédula con la que este usuario se registró — ver
     * register.blade.php): así nunca pierde acceso a su propio
     * historial si más tarde cambia el email de su cuenta desde su
     * perfil, aunque el Customer todavía tenga el email viejo.
     *
     * @return list<string>
     */
    protected function customerIdDocsForCurrentUser(): array
    {
        $user = Auth::user();

        return Customer::query()
            ->where('email', $user->email)
            ->orWhere('user_id', $user->id)
            ->pluck('id_doc')
            ->unique()
            ->values()
            ->all();
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
     *   (ni ENTREGADO ni DEVUELTO; incluye EN_DEVOLUCION). Es la
     *   vista por defecto.
     * - Historial: paquetes ENTREGADO o DEVUELTO al remitente, del
     *   más reciente al más antiguo, filtrable por rango de fecha de
     *   entrega/devolución.
     */
    public function render()
    {
        $idDocs = $this->customerIdDocsForCurrentUser();

        // whereIn()/orWhereIn() con un array vacío ya compilan a "sin
        // resultados" de forma segura en Laravel, así que no hace
        // falta (ni conviene) ramificar por separado el caso "$idDocs
        // vacío": antes, ese caso especial dejaba $historyPackages en
        // null en vez de un paginador vacío, y la vista de Historial
        // (que llama ->isEmpty() y ->links() incondicionalmente)
        // reventaba para cualquier cliente sin ningún id_doc asociado
        // todavía.
        $baseQuery = fn () => Package::query()
            ->where(function ($query) use ($idDocs) {
                $query->whereIn('recipient_id_doc', $idDocs)
                    ->orWhereIn('sender_id_doc', $idDocs);
            });

        $packages = collect();
        $historyPackages = null;

        // Estados finales: entregado al destinatario o devuelto al
        // remitente. Un DEVUELTO ya no está "en curso", así que va al
        // historial (fechado por returned_at) en vez de quedarse para
        // siempre entre los pendientes.
        $finalStatuses = [Package::STATUS_ENTREGADO, Package::STATUS_DEVUELTO];
        $finishedAt = 'COALESCE(delivery_completed_at, returned_at)';

        if ($this->activeTab === self::TAB_HISTORY) {
            $historyPackages = $baseQuery()
                ->whereIn('current_status', $finalStatuses)
                ->when(
                    $this->historyFrom !== '',
                    fn ($q) => $q->whereRaw("DATE({$finishedAt}) >= ?", [$this->historyFrom])
                )
                ->when(
                    $this->historyTo !== '',
                    fn ($q) => $q->whereRaw("DATE({$finishedAt}) <= ?", [$this->historyTo])
                )
                ->with(['histories', 'incidents'])
                ->orderByRaw("{$finishedAt} DESC")
                ->paginate(10)
                ->through(fn (Package $package) => $this->withClientRole($package, $idDocs));
        } else {
            $packages = $baseQuery()
                ->whereNotIn('current_status', $finalStatuses)
                ->with(['histories', 'incidents'])
                ->latest()
                ->get()
                ->map(fn (Package $package) => $this->withClientRole($package, $idDocs));
        }

        // Datos para la franja de accesos rápidos del encabezado
        // (mismo criterio que PendingPayments.php/Incidents.php, no se
        // duplica la lógica de negocio, solo el conteo).
        $pendingPaymentsPackages = empty($idDocs)
            ? collect()
            : Package::query()
                ->whereIn('recipient_id_doc', $idDocs)
                ->where('is_cod', true)
                ->where('cod_status', Package::COD_PENDIENTE)
                ->get();

        $openIncidentsCount = Incident::query()
            ->where('reported_by_user_id', Auth::id())
            ->where('status', Incident::STATUS_OPEN)
            ->count();

        return view(
            'livewire.client.dashboard',
            [
                'packages' => $packages,
                'historyPackages' => $historyPackages,
                'pendingPaymentsCount' => $pendingPaymentsPackages->count(),
                'pendingPaymentsTotalUsd' => $pendingPaymentsPackages->sum(
                    fn (Package $package) => (float) $package->cod_amount_usd
                ),
                'openIncidentsCount' => $openIncidentsCount,
            ]
        );
    }
}
