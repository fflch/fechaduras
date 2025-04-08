<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FechaduraController;

Route::get('/',[FechaduraController::class, 'index']);
