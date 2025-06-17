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
Route::post('/fechaduras/{fechadura}/delete_user/{user}',[FechaduraController::class, 'deleteUser']);
Route::post('/fechaduras/{fechadura}/create_fechadura_user', [FechaduraController::class, 'createFechaduraUser']);
Route::post('/fechaduras/{fechadura}/create_fechadura_setor', [FechaduraController::class, 'createFechaduraSetor']);

Route::get('/', function(){
    return redirect('/fechaduras');
});
