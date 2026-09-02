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
use App\Livewire\Admin\DriverPayments;
use App\Livewire\Admin\IncidentsManager;
use App\Livewire\Admin\DriverAssignment;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Ally\Dashboard as AllyDashboard;
use App\Livewire\Ally\CreatePackage;
use App\Livewire\Ally\PackageCreate as AllyPackageCreate;
use App\Livewire\Ally\Commissions as AllyCommissions;
use App\Livewire\Ally\Cod as AllyCod;
use App\Livewire\Ally\Incidents as AllyIncidents;
use App\Livewire\Ally\PackagePickup as AllyPackagePickup;
use App\Livewire\Ally\PackageReception;

use App\Livewire\Driver\Dashboard as DriverDashboard;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

use App\Livewire\Ally\PackageReception as AllyPackageReception;

use App\Livewire\Ally\DailyCashCut as AllyDailyCashCut;


Route::prefix('ally')
    ->middleware([
        'auth',
        'verified',
        'role:aliado,aliado_taquilla',
    ])
    ->name('ally.')
    ->group(function () {

        Route::get('/dashboard', AllyDashboard::class)
            ->name('dashboard');

        Route::get('/pedidos/nuevo', AllyPackageCreate::class)
            ->name('packages.create');

        Route::get('/comisiones', AllyCommissions::class)
            ->middleware('role:aliado')
            ->name('commissions');

        Route::get('/cod', AllyCod::class)
            ->middleware('role:aliado,aliado_taquilla')
            ->name('cod');

        Route::get('/incidencias', AllyIncidents::class)
            ->middleware('role:aliado,aliado_taquilla')
            ->name('incidents');

        Route::get('/paquetes/retiro', AllyPackagePickup::class)
            ->middleware('role:aliado,aliado_taquilla')
            ->name('packages.pickup');

        Route::get('/paquetes/recepcion', PackageReception::class)
            ->middleware('role:aliado,aliado_taquilla')
            ->name('packages.reception');

        // Próximas pantallas:
        // Route::get('/paquetes/recepcion', ...)->name('packages.receive');
        // Route::get('/seguimiento', ...)->name('tracking');
        // Route::get('/taquillas', ...)->name('staff');
        // Route::get('/cod', ...)->name('cod');
        // Route::get('/incidencias', ...)->name('incidents');
        // Route::get('/historial-financiero', ...)->name('financial-history');
    });

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


/*
|--------------------------------------------------------------------------
| Cliente
|--------------------------------------------------------------------------
*/

Route::get('/cliente/dashboard', ClientDashboard::class)
    ->middleware(['auth', 'verified', 'role:cliente'])
    ->name('cliente.dashboard');


/*
|--------------------------------------------------------------------------
| Repartidor
|--------------------------------------------------------------------------
*/

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


Route::post(
    '/repartidor/verificar-guia',
    [\App\Http\Controllers\DriverScanController::class, 'verify']
)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.scan.verify');




/*
|--------------------------------------------------------------------------
| Rastreo público
|--------------------------------------------------------------------------
*/

Route::get('/rastreo', function () {
    return view('tracking.index');
})->name('tracking.index');


Route::get('/rastreo/resultado', function () {

    $guia = trim((string) request('guia'));

    $package = Package::query()
        ->where('tracking_number', $guia)
        ->first();

    $statusOrder = [

        'RECIBIDO_AGENCIA' => [
            'label' => 'Recibido en Agencia Aliada',
            'icon'  => 'fa-warehouse',
        ],

        'RECOLECTADO_VENEXPRESS' => [
            'label' => 'Recolectado por Venexpress',
            'icon'  => 'fa-truck',
        ],

        'EN_HUB' => [
            'label' => 'En Hub de Clasificación',
            'icon'  => 'fa-warehouse',
        ],

        'EN_TRANSITO_NACIONAL' => [
            'label' => 'En Tránsito Nacional',
            'icon'  => 'fa-truck-fast',
        ],

        'LISTO_RETIRO' => [
            'label' => 'Listo para Retiro en Agencia Destino',
            'icon'  => 'fa-truck-ramp-box',
        ],

        'ENTREGADO' => [
            'label' => 'Entregado al Cliente',
            'icon'  => 'fa-house-circle-check',
        ],
    ];

    $statusSteps = [];
    $progressPercent = 0;

    if ($package) {

        $keys = array_keys($statusOrder);

        /*
         * El modelo Package utiliza current_status.
         */
        $currentIndex = array_search(
            $package->current_status,
            $keys,
            true
        );

        $currentIndex = $currentIndex === false
            ? 0
            : $currentIndex;


        /*
         * Historial de estados.
         */
        $history = $package->histories()
            ->pluck('created_at', 'status');


        foreach ($keys as $i => $key) {

            $timestamp = null;

            if (isset($history[$key])) {

                $date = \Carbon\Carbon::parse(
                    $history[$key]
                );

                $timestamp =
                    $date->format('d/m/Y')
                    . '<br>'
                    . $date->format('h:i a');
            }


            $statusSteps[] = [

                'label' => $statusOrder[$key]['label'],

                'icon' => $statusOrder[$key]['icon'],

                'done' => $i < $currentIndex,

                'current' => $i === $currentIndex,

                'timestamp' => $timestamp,
            ];
        }


        $progressPercent = $currentIndex === 0
            ? 8
            : (
                $currentIndex
                / (count($keys) - 1)
            ) * 100;
    }


    return view('tracking.show', [

        'guia' => $guia,

        'package' => $package,

        'statusSteps' => $statusSteps,

        'progressPercent' => $progressPercent,
    ]);

})->name('tracking.show');


/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware([
        'auth',
        'role:admin_principal,admin_operativo'
    ])
    ->name('admin.')
    ->group(function () {

        Route::get('/', AdminDashboard::class)
            ->name('dashboard');

        Route::get('/allies', AlliesManager::class)
            ->name('allies');

        Route::get('/bcv-rates', BcvRateManager::class)
            ->name('bcv-rates');

        Route::get('/rate-matrices', RateMatrixManager::class)
            ->name('rate-matrices');

        Route::get('/city-distances', CityDistanceManager::class)
            ->name('city-distances');

        Route::get('/users', UsersManager::class)
            ->name('users');

        Route::get('/rutas', RoutesManager::class)
            ->name('routes');

        Route::get('/rutas/dashboard', RoutesDashboard::class)
            ->name('routes.dashboard');

        Route::get('/paquetes/recepcion', \App\Livewire\Admin\PackageReception::class)
            ->name('packages.reception');

        Route::get('/paquetes/despacho', \App\Livewire\Admin\PackageDispatch::class)
            ->name('packages.dispatch');

        Route::get('/paquetes/asignar-repartidor', DriverAssignment::class)
            ->name('packages.assignment');

        Route::get('/remuneraciones', DriverPayments::class)
            ->name('driver-payments');

        Route::get('/incidencias', IncidentsManager::class)
            ->name('incidents');
    });


require __DIR__.'/auth.php';
