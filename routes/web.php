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
Route::get('/fechaduras/{fechadura}/cadastrar-foto/{userId}', [FechaduraController::class, 'showCadastrarFoto']);
Route::post('/fechaduras/{fechadura}/cadastrar-foto/{userId}', [FechaduraController::class, 'cadastrarFoto']);

Route::get('/fechaduras/{fechadura}/cadastrar-senha/{userId}', [FechaduraController::class, 'showCadastrarSenha']);
Route::post('/fechaduras/{fechadura}/cadastrar-senha/{userId}', [FechaduraController::class, 'cadastrarSenha']);

Route::post('/fechaduras/{fechadura}/sincronizar',[FechaduraController::class, 'sincronizar']);
Route::post('/fechaduras/{fechadura}/delete_user/{user}',[FechaduraController::class, 'deleteUser']);
Route::post('/fechaduras/{fechadura}/create_fechadura_user', [FechaduraController::class, 'createFechaduraUser']);
Route::post('/fechaduras/{fechadura}/create_fechadura_setor', [FechaduraController::class, 'createFechaduraSetor']);
Route::post('/fechaduras/{fechadura}/create_fechadura_pos', [FechaduraController::class, 'createFechaduraPos']);

Route::get('/', function(){
    return redirect('/fechaduras');
});
