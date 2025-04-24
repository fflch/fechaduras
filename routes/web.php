<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FechaduraController;

Route::get('/usuarios',[FechaduraController::class, 'index']);
Route::post('/fotos', [FechaduraController::class, 'fotos']);
Route::post('/sincronizar',[FechaduraController::class, 'sincronizar']);