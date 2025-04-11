<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FechaduraController;

Route::get('/usuarios',[FechaduraController::class, 'index']);
