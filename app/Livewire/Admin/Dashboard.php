<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Driver;
use App\Models\DriverPayment;
use App\Models\Incident;
use App\Models\Package;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        /*
        |--------------------------------------------------------------------------
        | PAQUETES
        |--------------------------------------------------------------------------
        */

        $statusCounts = Package::query()
            ->selectRaw('current_status, count(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $recentPackages = Package::query()
            ->latest()
            ->limit(8)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | ALIADOS
        |--------------------------------------------------------------------------
        */

        // Solamente los aliados aprobados/activos.
        $totalPackages = Package::count();

        $alliesCount = Ally::query()
            ->where('status', Ally::STATUS_ACTIVE)
            ->count();

        // Aliados pendientes de revisión por el administrador.
        $postulacionesCount = Ally::query()
            ->where('status', Ally::STATUS_PENDING)
            ->count();

        // Últimos aliados registrados.
        $recentAllies = Ally::query()
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        // Aliados con más paquetes despachados (volumen operativo).
        $topAllies = Ally::query()
            ->withCount('packages')
            ->where('status', Ally::STATUS_ACTIVE)
            ->orderByDesc('packages_count')
            ->limit(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | REPARTIDORES
        |--------------------------------------------------------------------------
        */

        $driversCount = Driver::count();

        // Remuneraciones ya generadas pero aún no pagadas a repartidores.
        $pendingPaymentsTotal = DriverPayment::query()
            ->where('status', DriverPayment::STATUS_PENDING)
            ->sum('amount_usd');

        $pendingPaymentsCount = DriverPayment::query()
            ->where('status', DriverPayment::STATUS_PENDING)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | CLIENTES
        |--------------------------------------------------------------------------
        */

        $clientsCount = User::query()
            ->where('role', User::ROLE_CLIENTE)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | REPORTES FINANCIEROS
        |--------------------------------------------------------------------------
        */

        // Ingresos por envíos entregados: solo lo que ya se cobró de verdad.
        $revenueToday = Package::query()
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereDate('updated_at', today())
            ->sum('total_price_usd');

        $revenueThisMonth = Package::query()
            ->where('current_status', Package::STATUS_ENTREGADO)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_price_usd');

        // Comisiones generadas a favor de los aliados por sus envíos.
        $commissionsThisMonth = Package::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission_amount_usd');

        // Dinero contra-entrega (COD) que las agencias tienen en mano
        // y todavía no han remitido a Venexpress.
        $codPendingTotal = Package::query()
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_PENDIENTE)
            ->sum('cod_amount_usd');

        $codPendingCount = Package::query()
            ->where('is_cod', true)
            ->where('cod_status', Package::COD_PENDIENTE)
            ->count();


        /*
        |--------------------------------------------------------------------------
        | INCIDENCIAS
        |--------------------------------------------------------------------------
        */

        $openIncidentsCount = Incident::query()
            ->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_IN_PROGRESS])
            ->count();


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view('livewire.admin.dashboard', [

            // Paquetes
            'totalPackages' => $totalPackages,
            'statusCounts' => $statusCounts,
            'statuses' => Package::STATUSES,
            'currentRate' => BcvRate::current(),
            'recentPackages' => $recentPackages,

            // Aliados
            'alliesCount' => $alliesCount,
            'postulacionesCount' => $postulacionesCount,
            'recentAllies' => $recentAllies,
            'topAllies' => $topAllies,

            // Usuarios / repartidores
            'driversCount' => $driversCount,
            'clientsCount' => $clientsCount,
            'pendingPaymentsTotal' => $pendingPaymentsTotal,
            'pendingPaymentsCount' => $pendingPaymentsCount,

            // Reportes financieros
            'revenueToday' => $revenueToday,
            'revenueThisMonth' => $revenueThisMonth,
            'commissionsThisMonth' => $commissionsThisMonth,
            'codPendingTotal' => $codPendingTotal,
            'codPendingCount' => $codPendingCount,

            // Incidencias
            'openIncidentsCount' => $openIncidentsCount,
        ]);
    }
}