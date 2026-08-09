<?php

use App\Livewire\Ally\CreatePackage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Taquilla Aliada
|--------------------------------------------------------------------------
|
| Por ahora solo protegidas con 'auth'. Cuando definamos el middleware
| de rol (Fase 5 - Panel Administrativo), aquí se agrega 'role:aliado'
| para que solophp artisan config:clear  usuarios con ese rol puedan entrar.
|
*/
Route::middleware(['auth'])->prefix('aliado')->name('ally.')->group(function () {
    Route::get('/guias/nueva', CreatePackage::class)->name('packages.create');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('dashboard');
    });
