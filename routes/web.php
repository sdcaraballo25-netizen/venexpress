<?php

use App\Livewire\Ally\CreatePackage;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\BcvRateManager;
use App\Livewire\Admin\RateMatrixManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

/*
|--------------------------------------------------------------------------
| Taquilla Aliada
|--------------------------------------------------------------------------
|
| Protegidas con 'auth'. El middleware de rol 'role:aliado' se agregará
| cuando se defina formalmente el flujo de permisos del aliado.
|
*/
Route::middleware(['auth'])->prefix('aliado')->name('ally.')->group(function () {
    Route::get('/guias/nueva', CreatePackage::class)->name('packages.create');
});

/*
|--------------------------------------------------------------------------
| Panel Administrativo
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        // Fase 5 — Tasa BCV y matrices de tarifas
        Route::get('/tasa-bcv', BcvRateManager::class)->name('bcv-rates');
        Route::get('/tarifas', RateMatrixManager::class)->name('rate-matrices');
    });

require __DIR__.'/auth.php';