<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RenoController;
use App\Http\Controllers\MantenimientoController;

Route::get('/renos', [RenoController::class, 'index']);
Route::get('/renos/{id}', [RenoController::class, 'show']);
Route::get('/mantenimientos', [MantenimientoController::class, 'index']);

Route::middleware('role:AYUDANTE')->group(function () {
    Route::post('/renos', [RenoController::class, 'store']);
    Route::put('/renos/{id}', [RenoController::class, 'update']);
    Route::delete('/renos/{id}', [RenoController::class, 'destroy']);
    Route::patch('/renos/{id}/asignacion', [RenoController::class, 'asignar']);

    Route::post('/mantenimientos', [MantenimientoController::class, 'store']);
    Route::patch('/mantenimientos/{id}/estado', [MantenimientoController::class, 'cambiarEstado']);
});
