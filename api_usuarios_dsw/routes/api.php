<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Rutas No Protegidas
Route::post('/registrar', [AuthController::class, 'registrar']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas Protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/perfil', [AuthController::class, 'perfil']);
    Route::post('/logout', [AuthController::class, 'logout']);
});