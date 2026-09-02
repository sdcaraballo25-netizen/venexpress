<?php

use Illuminate\Support\Facades\Route;
use App\Models\Package;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\BcvRateManager;
use App\Livewire\Admin\RateMatrixManager;
use App\Livewire\Admin\CityDistanceManager;
use App\Livewire\Admin\AlliesManager;
use App\Livewire\Admin\UsersManager;
use App\Livewire\Admin\RoutesManager;
use App\Livewire\Admin\RoutesDashboard;
use App\Livewire\Ally\Dashboard as AllyDashboard;
use App\Livewire\Ally\CreatePackage;
use App\Livewire\Ally\PackageCreate as AllyPackageCreate;
use App\Livewire\Ally\Commissions as AllyCommissions;
use App\Livewire\Driver\Dashboard as DriverDashboard;



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

    Route::get('/cliente/dashboard', function () {
    return view('Livewire.client.dashboard');
})
    ->middleware(['auth', 'verified', 'role:cliente'])
    ->name('cliente.dashboard');


Route::get('/repartidor/dashboard', DriverDashboard::class)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.dashboard');

Route::get('/repartidor/escanear', \App\Livewire\Driver\Scanner::class)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.scanner');

Route::get('/repartidor/paquetes', \App\Livewire\Driver\Packages::class)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.packages');

Route::get('/repartidor/paquetes/{packageId}', \App\Livewire\Driver\PackageDetail::class)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.package-detail');

Route::post('/repartidor/verificar-guia', [\App\Http\Controllers\DriverScanController::class, 'verify'])
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.scan.verify');


Route::prefix('ally')
    ->middleware(['auth', 'verified', 'role:aliado,aliado_taquilla'])
    ->name('ally.')
    ->group(function () {
        Route::get('/dashboard', AllyDashboard::class)->name('dashboard');
        Route::get('/pedidos/nuevo', AllyPackageCreate::class)->name('packages.create');
        Route::get('/comisiones', AllyCommissions::class)
            ->middleware('role:aliado')
            ->name('commissions');

        // Próximas pantallas se agregan aquí:
        // Route::get('/paquetes/recepcion', ...)->name('packages.receive');
        // Route::get('/seguimiento', ...)->name('tracking');
        // Route::get('/taquillas', ...)->name('staff'); // solo Administrador
        // Route::get('/cod', ...)->name('cod');           // solo Administrador
        // Route::get('/incidencias', ...)->name('incidents'); // solo Administrador
        // Route::get('/historial-financiero', ...)->name('financial-history'); // solo Administrador
    });


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

// Panel administrativo para Administrador Principal y Administrador Operativo
Route::prefix('admin')
    ->middleware(['auth', 'role:admin_principal,admin_operativo'])
    ->name('admin.')
    ->group(function () {
        Route::get('/', AdminDashboard::class)->name('dashboard');
        Route::get('/allies', AlliesManager::class)->name('allies');
        Route::get('/bcv-rates', BcvRateManager::class)->name('bcv-rates');
        Route::get('/rate-matrices', RateMatrixManager::class)->name('rate-matrices');
        Route::get('/city-distances', CityDistanceManager::class)->name('city-distances');
        Route::get('/users', UsersManager::class)->name('users');
        Route::get('/rutas', RoutesManager::class)->name('routes');
        Route::get('/rutas/dashboard', RoutesDashboard::class)->name('routes.dashboard');
    });

require __DIR__.'/auth.php';
