<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Driver;
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


        /*
        |--------------------------------------------------------------------------
        | REPARTIDORES
        |--------------------------------------------------------------------------
        */

        $driversCount = Driver::count();


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
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        return view('livewire.admin.dashboard', [

            // Paquetes
            'totalPackages' => Package::count(),
            'statusCounts' => $statusCounts,
            'statuses' => Package::STATUSES,
            'currentRate' => BcvRate::current(),
            'recentPackages' => $recentPackages,

            // Aliados
            'alliesCount' => $alliesCount,
            'postulacionesCount' => $postulacionesCount,
            'recentAllies' => $recentAllies,

            // Usuarios
            'driversCount' => $driversCount,
            'clientsCount' => $clientsCount,
        ]);
    }
}