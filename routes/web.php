<?php

use Illuminate\Support\Facades\Route;
use App\Models\Package;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\BcvRateManager;
use App\Livewire\Admin\RateMatrixManager;
use App\Livewire\Admin\CityDistanceManager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


    Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Rastreo público
Route::get('/rastreo', function () {
    return view('tracking.index');
})->name('tracking.index');

Route::get('/rastreo/resultado', function () {
    $guia = trim((string) request('guia'));

    $package = Package::where('tracking_number', $guia)->first();

    $statusOrder = [
        'RECIBIDO_AGENCIA'        => ['label' => 'Recibido en Agencia Aliada', 'icon' => 'fa-warehouse'],
        'RECOLECTADO_VENEXPRESS'  => ['label' => 'Recolectado por Venexpress', 'icon' => 'fa-truck'],
        'EN_HUB'                  => ['label' => 'En Hub de Clasificación', 'icon' => 'fa-warehouse'],
        'EN_TRANSITO_NACIONAL'    => ['label' => 'En Tránsito Nacional', 'icon' => 'fa-truck-fast'],
        'LISTO_RETIRO'            => ['label' => 'Listo para Retiro en Agencia Destino', 'icon' => 'fa-truck-ramp-box'],
        'ENTREGADO'               => ['label' => 'Entregado al Cliente', 'icon' => 'fa-house-circle-check'],
    ];

    $statusSteps = [];
    $progressPercent = 0;

    if ($package) {
        $keys = array_keys($statusOrder);
        $currentIndex = array_search($package->status, $keys, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;

        // Historial de timestamps por estado, si tienes la tabla package_histories
        $history = $package->histories()
            ->pluck('created_at', 'status')
            ?? collect();

        foreach ($keys as $i => $key) {
            $statusSteps[] = [
                'label'     => $statusOrder[$key]['label'],
                'icon'      => $statusOrder[$key]['icon'],
                'done'      => $i < $currentIndex,
                'current'   => $i === $currentIndex,
                'timestamp' => isset($history[$key])
                    ? \Carbon\Carbon::parse($history[$key])->format('d/m/Y') . '<br>' . \Carbon\Carbon::parse($history[$key])->format('h:i a')
                    : null,
            ];
        }

        $progressPercent = $currentIndex === 0 ? 8 : ($currentIndex / (count($keys) - 1)) * 100;
    }

    return view('tracking.show', [
        'guia'             => $guia,
        'package'          => $package,
        'statusSteps'      => $statusSteps,
        'progressPercent'  => $progressPercent,
    ]);
})->name('tracking.show');

// Panel administrativo (solo usuarios con role = admin)
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/bcv-rates', BcvRateManager::class)->name('bcv-rates');
        Route::get('/rate-matrices', RateMatrixManager::class)->name('rate-matrices');
        Route::get('/city-distances', CityDistanceManager::class)->name('city-distances');
    });

require __DIR__.'/auth.php';