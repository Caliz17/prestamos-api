<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\PrestamoController;
use App\Http\Controllers\Api\PagoController;
use App\Http\Controllers\Api\PasswordResetController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/reset-password/{token}', [PasswordResetController::class, 'show'])
     ->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    /* Protected routes */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /* Cliente resource routes */
    Route::apiResource('clientes', App\Http\Controllers\Api\ClienteController::class);
    /* Solicitud resource routes */
    Route::apiResource('solicitudes', App\Http\Controllers\Api\SolicitudController::class);
    Route::put('solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar']);
    Route::put('solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar']);
    Route::get('solicitudes/cliente/{cliente_id}', [SolicitudController::class, 'solicitudesPorCliente']);
    /* Prestamo resource routes */
    Route::apiResource('prestamos', App\Http\Controllers\Api\PrestamoController::class);
    Route::get('prestamos/cliente/{cliente_id}', [PrestamoController::class, 'prestamosPorCliente']);

    /* Pago resource routes */
    Route::apiResource('pagos', App\Http\Controllers\Api\PagoController::class);
    Route::get('pagos/prestamo/{prestamo_id}', [PagoController::class, 'pagosPorPrestamo']);
    Route::get('pagos/cliente/{cliente_id}', [PagoController::class, 'pagosPorCliente']);

});
