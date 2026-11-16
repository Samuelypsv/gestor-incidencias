<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ComentarioController;
use App\Http\Controllers\Api\EstadisticasController;
use App\Http\Controllers\Api\TecnicoController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/yo', [AuthController::class, 'yo']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/estadisticas', EstadisticasController::class);
    Route::get('/tecnicos', [TecnicoController::class, 'index']);

    Route::apiResource('clientes', ClienteController::class);
    Route::apiResource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/comentarios', [ComentarioController::class, 'store']);
});
