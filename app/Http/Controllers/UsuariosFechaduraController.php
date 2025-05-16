<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;

class UsuariosFechaduraController extends Controller
{
    public static function store($faltantes, $fechadura, $dadosFechadura){
        $apiService = new ApiService($fechadura);
        $cadastroUsuarios = $apiService->createUsers($faltantes, $dadosFechadura);
    }

    public static function update($usuariosReplicado, $fechadura){
        $apiService = new ApiService($fechadura);
        $updateUsuarios = $apiService->updateUsers($usuariosReplicado);
    }
}
