<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\PrestamoController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    /* Protected routes */
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    /* Cliente resource routes */
    Route::apiResource('clientes', App\Http\Controllers\Api\ClienteController::class);
    /* Solicitud resource routes */
    Route::apiResource('solicitudes', App\Http\Controllers\Api\SolicitudController::class);
    /* Prestamo resource routes */
    Route::apiResource('prestamos', App\Http\Controllers\Api\PrestamoController::class);
});


