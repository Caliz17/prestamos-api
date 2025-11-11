<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response()->json([
        'message' => 'Debe iniciar sesión para acceder a este recurso.'
    ], 401);
})->name('login');
