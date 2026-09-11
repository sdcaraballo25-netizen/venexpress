<?php

use App\Http\Controllers\Api\DriverAuthController;
use App\Http\Controllers\Api\DriverDashboardController;
use App\Http\Controllers\Api\DriverPackageController;
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

            Route::get('/commissions', [DriverDashboardController::class, 'commissions'])
                ->name('commissions');

            Route::post('/scan', [DriverPackageController::class, 'scan'])
                ->name('scan');

            Route::get('/packages', [DriverPackageController::class, 'index'])
                ->name('packages.index');

            Route::get('/packages/{packageId}', [DriverPackageController::class, 'show'])
                ->name('packages.show');

            Route::post('/packages/{packageId}/complete-delivery', [DriverPackageController::class, 'completeDelivery'])
                ->name('packages.complete-delivery');

            Route::post('/packages/{packageId}/collect-cod', [DriverPackageController::class, 'collectCod'])
                ->name('packages.collect-cod');
        });
    });
