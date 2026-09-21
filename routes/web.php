<?php

use App\Http\Controllers\DocumentPhotoController;
use App\Http\Controllers\DriverScanController;
use App\Http\Controllers\PackageLabelController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\TrackingController;
use App\Livewire\Admin\AlliesManager;
use App\Livewire\Admin\AllyFinance;
use App\Livewire\Admin\AuditLogViewer;
use App\Livewire\Admin\BcvRateManager;
use App\Livewire\Admin\CityDistanceManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\DriverAssignment;
use App\Livewire\Admin\DriverPayments;
use App\Livewire\Admin\DriverRemunerationManager;
use App\Livewire\Admin\DriversApprovalManager;
use App\Livewire\Admin\EmprendedoresApprovalManager;
use App\Livewire\Admin\HelpCenter as AdminHelpCenter;
use App\Livewire\Admin\IncidentsManager;
use App\Livewire\Admin\PackageDispatch;
use App\Livewire\Admin\PaymentOrders;
use App\Livewire\Admin\RateMatrixManager;
use App\Livewire\Admin\RecommendationsManager;
use App\Livewire\Admin\RemunerationsSummary;
use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Admin\RoutesDashboard;
use App\Livewire\Admin\RoutesManager;
use App\Livewire\Admin\UsersManager;
use App\Livewire\Admin\WarehousesManager;
use App\Livewire\Ally\Cod as AllyCod;
use App\Livewire\Ally\Commissions as AllyCommissions;
use App\Livewire\Ally\DailyCashCut;
use App\Livewire\Ally\Dashboard as AllyDashboard;
use App\Livewire\Ally\HelpCenter as AllyHelpCenter;
use App\Livewire\Ally\Incidents as AllyIncidents;
use App\Livewire\Ally\PackageCreate as AllyPackageCreate;
use App\Livewire\Ally\PackageDetail as AllyPackageDetail;
use App\Livewire\Ally\PackagePickup as AllyPackagePickup;
use App\Livewire\Ally\PackageReception;
use App\Livewire\Ally\Packages as AllyPackages;
use App\Livewire\Ally\SalesCloseout as AllySalesCloseout;
use App\Livewire\Ally\StaffManager as AllyStaffManager;
use App\Livewire\Almacen\Dashboard as AlmacenDashboard;
use App\Livewire\Almacen\HelpCenter as AlmacenHelpCenter;
use App\Livewire\Emprendedor\Dashboard as EmprendedorDashboard;
use App\Livewire\Emprendedor\Pedidos as EmprendedorPedidos;
use App\Livewire\Emprendedor\Productos as EmprendedorProductos;
use App\Livewire\Public\Marketplace;
use App\Livewire\Client\Dashboard as ClientDashboard;
use App\Livewire\Client\HelpCenter as ClientHelpCenter;
use App\Livewire\Client\Incidents as ClientIncidents;
use App\Livewire\Client\PendingPayments as ClientPendingPayments;
use App\Livewire\Driver\AppDownload;
use App\Livewire\Driver\Dashboard as DriverDashboard;
use App\Livewire\Driver\HelpCenter as DriverHelpCenter;
use App\Livewire\Driver\PackageDetail;
use App\Livewire\Driver\Packages;
use App\Livewire\Driver\RouteDetail;
use App\Livewire\Driver\RouteHistory;
use App\Livewire\Driver\Scanner;
use App\Livewire\Profile\Show as ProfileShow;
use App\Livewire\Recommendations\Create as RecommendationCreate;
use App\Livewire\Public\HelpCenter;
use App\Livewire\Public\OfficeLocator;
use App\Livewire\Public\PriceCalculator;
use App\Livewire\Public\PrivacyPolicy;
use App\Livewire\Public\RecommendationForm;
use App\Livewire\Public\TermsAndConditions;
use Illuminate\Support\Facades\Route;

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
        'account.approved',
    ])
    ->name('ally.')
    ->group(function () {

        // Ventas de TODO el negocio (todas las taquillas combinadas):
        // solo el Aliado Administrador. Taquilla usa /cierre-del-dia
        // (más abajo), que solo muestra lo que ella misma registró.
        Route::get('/dashboard', AllyDashboard::class)
            ->middleware('role:aliado')
            ->name('dashboard');

        Route::get('/pedidos/nuevo', AllyPackageCreate::class)
            ->name('packages.create');

        Route::get('/comisiones', AllyCommissions::class)
            ->middleware('role:aliado')
            ->name('commissions');

        Route::get('/pedidos', AllyPackages::class)
            ->name('packages.index');

        Route::get('/pedidos/{packageId}', AllyPackageDetail::class)
            ->name('packages.show');

        /*
        |--------------------------------------------------------------------------
        | Gestión de Taquillas (RF-ALI-02)
        |--------------------------------------------------------------------------
        */

        Route::get('/taquillas', AllyStaffManager::class)
            ->middleware('role:aliado')
            ->name('staff');

        /*
        |--------------------------------------------------------------------------
        | Corte de caja
        |--------------------------------------------------------------------------
        */

        Route::get('/corte-caja', DailyCashCut::class)
            ->middleware('role:aliado')
            ->name('cash-cut');

        // Cierre del día por forma de pago: Administrador ve todo el
        // negocio (con filtro por taquilla); Taquilla solo lo suyo.
        Route::get('/cierre-del-dia', AllySalesCloseout::class)
            ->name('sales-closeout');

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

        Route::get('/ayuda', AllyHelpCenter::class)
            ->middleware('role:aliado,aliado_taquilla')
            ->name('help');
    });

/*
|--------------------------------------------------------------------------
| Cuenta pendiente de aprobación
|--------------------------------------------------------------------------
*/

Route::get('/cuenta/pendiente', function () {
    return view('account-pending');
})
    ->middleware('auth')
    ->name('account.pending');

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

Route::get('profile', ProfileShow::class)
    ->middleware(['auth'])
    ->name('profile');

/*
|--------------------------------------------------------------------------
| Recomendaciones (usuario autenticado)
|--------------------------------------------------------------------------
*/

Route::get('mis-recomendaciones', RecommendationCreate::class)
    ->middleware(['auth'])
    ->name('recommendations.create');

/*
|--------------------------------------------------------------------------
| Cliente
|--------------------------------------------------------------------------
*/

Route::prefix('cliente')
    ->middleware([
        'auth',
        'verified',
        'role:cliente',

        // Verificación propia de VenExpress por código (distinta del
        // "verified" nativo de Laravel, que este proyecto no usa).
        'account.verified',
    ])
    ->name('cliente.')
    ->group(function () {

        Route::get('/dashboard', ClientDashboard::class)
            ->name('dashboard');

        Route::get('/incidencias', ClientIncidents::class)
            ->name('incidents');

        Route::get('/pagos-pendientes', ClientPendingPayments::class)
            ->name('pending-payments');

        Route::get('/ayuda', ClientHelpCenter::class)
            ->name('help');
    });

/*
|--------------------------------------------------------------------------
| Repartidor
|--------------------------------------------------------------------------
*/

Route::get('/repartidor/dashboard', DriverDashboard::class)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.dashboard');

Route::get(
    '/repartidor/escanear',
    Scanner::class
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.scanner');

Route::get(
    '/repartidor/paquetes',
    Packages::class
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.packages');

Route::get(
    '/repartidor/paquetes/{packageId}',
    PackageDetail::class
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.package-detail');

Route::get(
    '/repartidor/ruta/{routeId}',
    RouteDetail::class
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.route-detail');

Route::get(
    '/repartidor/historial-rutas',
    RouteHistory::class
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.route-history');

Route::post(
    '/repartidor/verificar-guia',
    [DriverScanController::class, 'verify']
)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.scan.verify');

// IMPORTANTE: la descarga de la app vive detrás del login de
// repartidor a propósito — exponerla en el sitio público permitiría
// a cualquiera descargar el APK y explorar la superficie de la API
// del driver sin ser un repartidor real.
Route::get('/repartidor/descargar-app', AppDownload::class)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.app-download');

Route::get('/repartidor/ayuda', DriverHelpCenter::class)
    ->middleware(['auth', 'verified', 'role:repartidor', 'account.approved'])
    ->name('repartidor.help');

/*
|--------------------------------------------------------------------------
| Almacén
|--------------------------------------------------------------------------
*/

Route::get('/almacen/dashboard', AlmacenDashboard::class)
    ->middleware(['auth', 'verified', 'role:almacen'])
    ->name('almacen.dashboard');

Route::get('/almacen/ayuda', AlmacenHelpCenter::class)
    ->middleware(['auth', 'verified', 'role:almacen'])
    ->name('almacen.help');

/*
|--------------------------------------------------------------------------
| Emprendedor (marketplace)
|--------------------------------------------------------------------------
*/

Route::get('/emprendedor/dashboard', EmprendedorDashboard::class)
    ->middleware(['auth', 'verified', 'role:emprendedor', 'account.approved'])
    ->name('emprendedor.dashboard');

Route::get('/emprendedor/productos', EmprendedorProductos::class)
    ->middleware(['auth', 'verified', 'role:emprendedor', 'account.approved'])
    ->name('emprendedor.productos');

Route::get('/emprendedor/pedidos', EmprendedorPedidos::class)
    ->middleware(['auth', 'verified', 'role:emprendedor', 'account.approved'])
    ->name('emprendedor.pedidos');

/*
|--------------------------------------------------------------------------
| Tienda pública (marketplace)
|--------------------------------------------------------------------------
*/

Route::get('/tienda', Marketplace::class)
    ->name('public.marketplace');

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
| Legal (público)
|--------------------------------------------------------------------------
*/

Route::get('/terminos-y-condiciones', TermsAndConditions::class)
    ->name('public.terms');

Route::get('/politica-de-privacidad', PrivacyPolicy::class)
    ->name('public.privacy');

/*
|--------------------------------------------------------------------------
| Ayuda y recomendaciones (público)
|--------------------------------------------------------------------------
*/

Route::get('/ayuda', HelpCenter::class)
    ->name('public.help');

Route::get('/recomendaciones', RecommendationForm::class)
    ->name('public.recommendations');

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
        | Reportes
        |--------------------------------------------------------------------------
        */

        Route::get('/reportes', AdminReports::class)
            ->name('reports');

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

        Route::get('/almacenes', WarehousesManager::class)
            ->name('warehouses');

        /*
        |--------------------------------------------------------------------------
        | Paquetes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/paquetes/recepcion',
            App\Livewire\Admin\PackageReception::class
        )
            ->name('packages.reception');

        Route::get(
            '/paquetes/despacho',
            PackageDispatch::class
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
        | Resumen de pagos pendientes (Aliados + Repartidores juntos)
        |--------------------------------------------------------------------------
        */

        Route::get('/remuneraciones/resumen', RemunerationsSummary::class)
            ->name('remunerations-summary');

        Route::get('/repartidores/aprobacion', DriversApprovalManager::class)
            ->name('drivers.approval');

        Route::get('/emprendedores/aprobacion', EmprendedoresApprovalManager::class)
            ->name('emprendedores.approval');

        Route::get('/remuneraciones/tarifa', DriverRemunerationManager::class)
            ->name('driver-remuneration-rate');

        /*
        |--------------------------------------------------------------------------
        | Incidencias
        |--------------------------------------------------------------------------
        */

        Route::get('/incidencias', IncidentsManager::class)
            ->name('incidents');

        /*
        |--------------------------------------------------------------------------
        | Recomendaciones
        |--------------------------------------------------------------------------
        */

        Route::get('/recomendaciones', RecommendationsManager::class)
            ->name('recommendations');

        /*
        |--------------------------------------------------------------------------
        | Ayuda
        |--------------------------------------------------------------------------
        */

        Route::get('/ayuda', AdminHelpCenter::class)
            ->name('help');

        /*
        |--------------------------------------------------------------------------
        | Auditoría
        |--------------------------------------------------------------------------
        */

        Route::get('/bitacora', AuditLogViewer::class)
            ->middleware('role:admin_principal')
            ->name('audit-log');

        /*
        |--------------------------------------------------------------------------
        | Pagos automatizados
        |--------------------------------------------------------------------------
        |
        | Esta ruta ya está dentro del grupo admin.
        |
        | URL:
        | /admin/payments/{paymentOrder}/confirm-test
        |
        | Nombre:
        | admin.payments.confirm-test
        |
        */

        Route::post(
            '/payments/{paymentOrder}/confirm-test',
            [PaymentWebhookController::class, 'confirmForTesting']
        )
            ->name('payments.confirm-test');

        Route::get('/payments', PaymentOrders::class)
            ->name('payments');

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
    [PackageLabelController::class, 'pdf']
)
    ->middleware(['auth'])
    ->name('packages.label');

/*
|--------------------------------------------------------------------------
| Documentos de identidad (privados)
|--------------------------------------------------------------------------
|
| Fotos de cédula, licencia, carnet de circulación, fachada de agencia
| y evidencia de entrega. Se guardan en el disco privado ("local") y
| solo se sirven a través de estas rutas autenticadas; la autorización
| fina se realiza dentro de DocumentPhotoController.
|
*/

Route::get(
    '/aliados/{ally}/documentos/fachada',
    [DocumentPhotoController::class, 'allyStorefront']
)
    ->middleware(['auth'])
    ->name('allies.documents.storefront');

Route::get(
    '/aliados/{ally}/documentos/rif',
    [DocumentPhotoController::class, 'allyRifDocument']
)
    ->middleware(['auth'])
    ->name('allies.documents.rif');

Route::get(
    '/aliados/{ally}/documentos/registro-mercantil',
    [DocumentPhotoController::class, 'allyMercantileRegistry']
)
    ->middleware(['auth'])
    ->name('allies.documents.mercantile-registry');

Route::get(
    '/aliados/{ally}/documentos/cedula-titular',
    [DocumentPhotoController::class, 'allyOwnerIdDocument']
)
    ->middleware(['auth'])
    ->name('allies.documents.owner-id');

Route::get(
    '/repartidores/{driver}/documentos/licencia',
    [DocumentPhotoController::class, 'driverLicense']
)
    ->middleware(['auth'])
    ->name('drivers.documents.license');

Route::get(
    '/repartidores/{driver}/documentos/cedula',
    [DocumentPhotoController::class, 'driverId']
)
    ->middleware(['auth'])
    ->name('drivers.documents.id');

Route::get(
    '/repartidores/{driver}/documentos/carnet-circulacion',
    [DocumentPhotoController::class, 'driverVehicleRegistration']
)
    ->middleware(['auth'])
    ->name('drivers.documents.vehicle-registration');

Route::get(
    '/paquetes/{package}/evidencia-entrega',
    [DocumentPhotoController::class, 'packageDeliveryEvidence']
)
    ->middleware(['auth'])
    ->name('packages.delivery-evidence');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
