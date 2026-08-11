<?php

namespace App\Livewire\Admin;

use App\Models\Ally;
use App\Models\BcvRate;
use App\Models\Driver;
use App\Models\Package;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        $statusCounts = Package::query()
            ->selectRaw('current_status, count(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $recentPackages = Package::query()
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.admin.dashboard', [
            'totalPackages' => Package::count(),
            'statusCounts' => $statusCounts,
            'statuses' => Package::STATUSES,
            'currentRate' => BcvRate::current(),
            'alliesCount' => Ally::count(),
            'driversCount' => Driver::count(),
            'recentPackages' => $recentPackages,
        ]);
    }
}
