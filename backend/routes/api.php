<?php

use App\Http\Controllers\Api\EtiquetaController;
use App\Http\Controllers\Api\PrioridadController;
use App\Http\Controllers\Api\TareaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// whereNumber evita que /api/tareas/abc reviente al castear a int: devuelve 404.
Route::apiResource('tareas', TareaController::class)->whereNumber('tarea');

// Catálogos de sólo lectura para los selects del formulario.
Route::get('prioridades', [PrioridadController::class, 'index']);
Route::get('etiquetas', [EtiquetaController::class, 'index']);
