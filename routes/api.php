<?php

use App\Http\Controllers\Api\DriverAuthController;
use App\Http\Controllers\Api\DriverDashboardController;
use App\Http\Controllers\Api\DriverDeliveryController;
use App\Http\Controllers\Api\DriverHubDistributionController;
use App\Http\Controllers\Api\DriverIncidentController;
use App\Http\Controllers\Api\DriverPackageController;
use App\Http\Controllers\Api\DriverRouteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — App del Repartidor (Flutter)
|--------------------------------------------------------------------------
*/

Route::prefix('driver')
    ->name('api.driver.')
    ->group(function () {

        // Login público (sin sanctum todavía).
        Route::post('/login', [DriverAuthController::class, 'login'])
            ->middleware('throttle:10,1')
            ->name('login');

        Route::middleware([
            'auth:sanctum',
            'ability:driver',
            'role:repartidor',
        ])->group(function () {

            Route::post('/logout', [DriverAuthController::class, 'logout'])
                ->name('logout');

            Route::get('/me', [DriverAuthController::class, 'me'])
                ->name('me');

            Route::get('/dashboard', [DriverDashboardController::class, 'summary'])
                ->name('dashboard');

            Route::get('/route', [DriverRouteController::class, 'active'])
                ->name('route.active');

            Route::post('/route/start', [DriverRouteController::class, 'start'])
                ->name('route.start');

            Route::get('/route/available', [DriverRouteController::class, 'available'])
                ->name('route.available');

            Route::post('/route/{routeId}/claim', [DriverRouteController::class, 'claim'])
                ->name('route.claim');

            Route::post('/route/complete', [DriverRouteController::class, 'complete'])
                ->name('route.complete');

            Route::get('/commissions', [DriverDashboardController::class, 'commissions'])
                ->name('commissions');

            Route::post('/deliveries/claim-by-scan', [DriverDeliveryController::class, 'claimByScan'])
                ->name('deliveries.claim-by-scan');

            Route::get('/deliveries/available', [DriverDeliveryController::class, 'available'])
                ->name('deliveries.available');

            Route::get('/deliveries/route-order', [DriverDeliveryController::class, 'routeOrder'])
                ->name('deliveries.route-order');

            Route::post('/packages/{packageId}/claim', [DriverDeliveryController::class, 'claim'])
                ->name('packages.claim');

            Route::post('/scan', [DriverPackageController::class, 'scan'])
                ->name('scan');

            // HUB Distribución: HUB -> almacén propio de Venexpress destino.
            // Actor distinto del driver de HUB Recolección (arriba) y de la
            // app de Delivery.
            Route::post('/hub/dispatch', [DriverHubDistributionController::class, 'departFromHub'])
                ->name('hub.dispatch');

            Route::post('/hub/arrival', [DriverHubDistributionController::class, 'arriveAtDestination'])
                ->name('hub.arrival');

            Route::get('/packages', [DriverPackageController::class, 'index'])
                ->name('packages.index');

            Route::get('/packages/{packageId}', [DriverPackageController::class, 'show'])
                ->name('packages.show');

            Route::post('/packages/{packageId}/complete-delivery', [DriverPackageController::class, 'completeDelivery'])
                ->name('packages.complete-delivery');

            Route::post('/packages/{packageId}/collect-cod', [DriverPackageController::class, 'collectCod'])
                ->name('packages.collect-cod');

            Route::get('/packages/{packageId}/incidents', [DriverIncidentController::class, 'index'])
                ->name('packages.incidents.index');

            Route::post('/packages/{packageId}/incidents', [DriverIncidentController::class, 'store'])
                ->name('packages.incidents.store');
        });
    });
