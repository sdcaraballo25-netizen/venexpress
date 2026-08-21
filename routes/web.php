<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Rastreo público
Route::get('/rastreo', function () {
    return view('tracking.index');
})->name('tracking.index');

Route::get('/rastreo/resultado', function () {
    $guia = request('guia');

    return view('tracking.show', [
        'guia' => $guia,
    ]);
})->name('tracking.show');

// Ruta temporal de login para arreglar la vista welcome
Route::get('/login', function () {
    return "Pantalla de Login temporal";
})->name('login');
