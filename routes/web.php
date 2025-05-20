<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FechaduraController;

// Rotas CRUD
Route::get('/fechaduras',[FechaduraController::class, 'index']);
Route::get('/fechaduras/create',[FechaduraController::class, 'create']);
Route::post('/fechaduras',[FechaduraController::class, 'store']);
Route::get('/fechaduras/{fechadura}',[FechaduraController::class, 'show']);
Route::get('/fechaduras/{fechadura}/edit',[FechaduraController::class, 'edit']);
Route::put('/fechaduras/{fechadura}',[FechaduraController::class, 'update']);
Route::delete('/fechaduras/{fechadura}',[FechaduraController::class, 'destroy']);

Route::get('/fechaduras/{fechadura}/logs', [FechaduraController::class, 'logs']);
Route::post('/fechaduras/{fechadura}/logs', [FechaduraController::class, 'updateLogs']);

Route::post('/fotos', [FechaduraController::class, 'fotos']);
Route::post('/sincronizar',[FechaduraController::class, 'sincronizar']);