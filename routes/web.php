<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rastreo público
Route::get('/rastreo', function () {
    return view('tracking.index');
})->name('tracking.index');

Route::get('/rastreo/resultado', function () {
    $guia = request('guia');

    // TODO: reemplazar por tu lógica real, ej:
    // $package = \App\Models\Package::where('tracking_number', $guia)->firstOrFail();

    return view('tracking.show', [
        'guia' => $guia,
    ]);
})->name('tracking.show');

// Si usas Laravel Breeze/Fortify para auth, esas rutas ya vienen incluidas
// desde routes/auth.php y no hace falta declarar 'login' aquí.
// Si NO tienes auth instalado todavía, comenta el enlace de "Iniciar sesión"
// en welcome.blade.php o crea una ruta temporal:
//
// Route::get('/login', function () {
//     return view('auth.login');
// })->name('login');
