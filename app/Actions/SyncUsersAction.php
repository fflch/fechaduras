<?php

namespace App\Actions;

use App\Models\Fechadura;
use App\Services\ApiService;
use App\Services\ReplicadoService;

class SyncUsersAction
{
    public static function execute(Fechadura $fechadura)
    {
        $apiService = new ApiService($fechadura);
        $usuariosFechadura = $apiService->loadUsers();
        $usuariosReplicado = ReplicadoService::pessoa();

        $fechaduraId = [];
        $fechaduraReg = [];
        
        foreach($usuariosFechadura as $user) {
            $fechaduraId[$user['id']] = $user; 
            $fechaduraReg[$user['registration']] = $user;
        }
        
        $faltantes = array_diff_key($usuariosReplicado, $fechaduraReg);
        
        $dadosFechadura = [
            'fechaduraId' => $fechaduraId,
            'fechaduraReg' => $fechaduraReg,
        ];

        if(!empty($faltantes)) {
            $apiService->createUsers($faltantes, $dadosFechadura);
        }
        
        $apiService->updateUsers($usuariosReplicado);
        
        return true;
    }
}