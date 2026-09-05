<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrackingController;

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
use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\AllyFinance;

use App\Livewire\Client\Dashboard as ClientDashboard;

use App\Livewire\Ally\Dashboard as AllyDashboard;
use App\Livewire\Ally\PackageCreate as AllyPackageCreate;
use App\Livewire\Ally\Commissions as AllyCommissions;
use App\Livewire\Ally\DailyCashCut;
use App\Livewire\Ally\Cod as AllyCod;
use App\Livewire\Ally\Incidents as AllyIncidents;
use App\Livewire\Ally\PackagePickup as AllyPackagePickup;
use App\Livewire\Ally\PackageReception;

use App\Livewire\Driver\Dashboard as DriverDashboard;

use App\Livewire\Public\PriceCalculator;
use App\Livewire\Public\OfficeLocator;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Aliados
|--------------------------------------------------------------------------
*/

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

        /*
        |--------------------------------------------------------------------------
        | Corte de caja
        |--------------------------------------------------------------------------
        */

        Route::get('/corte-caja', DailyCashCut::class)
            ->middleware('role:aliado')
            ->name('cash-cut');

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
    });


/*
|--------------------------------------------------------------------------
| Página principal
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');


/*
|--------------------------------------------------------------------------
| Dashboard genérico
|--------------------------------------------------------------------------
*/

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Perfil
|--------------------------------------------------------------------------
*/

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


Route::get(
    '/repartidor/escanear',
    \App\Livewire\Driver\Scanner::class
)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.scanner');


Route::get(
    '/repartidor/paquetes',
    \App\Livewire\Driver\Packages::class
)
    ->middleware(['auth', 'verified', 'role:repartidor'])
    ->name('repartidor.packages');


Route::get(
    '/repartidor/paquetes/{packageId}',
    \App\Livewire\Driver\PackageDetail::class
)
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

Route::get('/rastreo', [TrackingController::class, 'index'])
    ->name('tracking.index');


Route::get('/rastreo/resultado', [TrackingController::class, 'show'])
    ->middleware('throttle:60,1')
    ->name('tracking.show');


/*
|--------------------------------------------------------------------------
| Calculadora pública
|--------------------------------------------------------------------------
*/

Route::get('/calcular-precio', PriceCalculator::class)
    ->name('public.calculator');


/*
|--------------------------------------------------------------------------
| Localizador público de agencias
|--------------------------------------------------------------------------
*/

Route::get('/agencias', OfficeLocator::class)
    ->name('public.offices');


/*
|--------------------------------------------------------------------------
| Administración
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware([
        'auth',
        'role:admin_principal,admin_operativo',
    ])
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/', AdminDashboard::class)
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Aliados
        |--------------------------------------------------------------------------
        */

        Route::get('/allies', AlliesManager::class)
            ->name('allies');


        /*
        |--------------------------------------------------------------------------
        | Finanzas de aliados
        |--------------------------------------------------------------------------
        */

        Route::get('/finanzas-aliados', AllyFinance::class)
            ->name('ally-finance');


        /*
        |--------------------------------------------------------------------------
        | Tarifas
        |--------------------------------------------------------------------------
        */

        Route::get('/bcv-rates', BcvRateManager::class)
            ->name('bcv-rates');

        Route::get('/rate-matrices', RateMatrixManager::class)
            ->name('rate-matrices');

        Route::get('/city-distances', CityDistanceManager::class)
            ->name('city-distances');


        /*
        |--------------------------------------------------------------------------
        | Usuarios
        |--------------------------------------------------------------------------
        */

        Route::get('/users', UsersManager::class)
            ->name('users');


        /*
        |--------------------------------------------------------------------------
        | Rutas
        |--------------------------------------------------------------------------
        */

        Route::get('/rutas', RoutesManager::class)
            ->name('routes');

        Route::get('/rutas/dashboard', RoutesDashboard::class)
            ->name('routes.dashboard');


        /*
        |--------------------------------------------------------------------------
        | Paquetes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/paquetes/recepcion',
            \App\Livewire\Admin\PackageReception::class
        )
            ->name('packages.reception');


        Route::get(
            '/paquetes/despacho',
            \App\Livewire\Admin\PackageDispatch::class
        )
            ->name('packages.dispatch');


        Route::get(
            '/paquetes/asignar-repartidor',
            DriverAssignment::class
        )
            ->name('packages.assignment');


        /*
        |--------------------------------------------------------------------------
        | Repartidores
        |--------------------------------------------------------------------------
        */

        Route::get('/remuneraciones', DriverPayments::class)
            ->name('driver-payments');


        /*
        |--------------------------------------------------------------------------
        | Incidencias
        |--------------------------------------------------------------------------
        */

        Route::get('/incidencias', IncidentsManager::class)
            ->name('incidents');


        /*
        |--------------------------------------------------------------------------
        | Auditoría
        |--------------------------------------------------------------------------
        */

        Route::get('/bitacora', AuditLogViewer::class)
            ->middleware('role:admin_principal')
            ->name('audit-log');
    });


/*
|--------------------------------------------------------------------------
| Guía / etiqueta PDF
|--------------------------------------------------------------------------
|
| La autorización fina se realiza dentro de
| PackageLabelController.
|
*/

Route::get(
    '/paquetes/{package}/guia',
    [\App\Http\Controllers\PackageLabelController::class, 'pdf']
)
    ->middleware(['auth'])
    ->name('packages.label');


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
