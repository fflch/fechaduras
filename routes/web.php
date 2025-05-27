<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FechaduraController;
use App\Http\Controllers\LogController;

// Rotas CRUD
Route::get('/fechaduras',[FechaduraController::class, 'index']);
Route::get('/fechaduras/create',[FechaduraController::class, 'create']);
Route::post('/fechaduras',[FechaduraController::class, 'store']);
Route::get('/fechaduras/{fechadura}',[FechaduraController::class, 'show']);
Route::get('/fechaduras/{fechadura}/edit',[FechaduraController::class, 'edit']);
Route::put('/fechaduras/{fechadura}',[FechaduraController::class, 'update']);
Route::delete('/fechaduras/{fechadura}',[FechaduraController::class, 'destroy']);

Route::get('/fechaduras/{fechadura}/logs', [LogController::class, 'logs']);
Route::post('/fechaduras/{fechadura}/logs', [LogController::class, 'updateLogs']);

Route::post('/fotos', [FechaduraController::class, 'fotos']);
Route::post('/fechaduras/{fechadura}/sincronizar',[FechaduraController::class, 'sincronizar']);