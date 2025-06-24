<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;

class SyncUsersAction
{
    /**
     * Create a new class instance.
     */

    public static function execute($fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $setores = $fechadura->setores->select('codset');
        $areas = $fechadura->areas->select('codare');
        $usuariosFechadura = $fechadura->usuarios->select(['codpes','name'])->keyBy('codpes');

        $usuariosSetor = $setores->isNotEmpty() ?
        ReplicadoService::pessoa($setores->implode('codset', ',')) :
        collect();

        $alunosPos = $areas->isNotEmpty() ? 
        ReplicadoService::retornaAlunosPos($areas->implode('codare',',')) :
        collect();
          
        $usuarios = $alunosPos->merge($usuariosSetor)
                            ->merge($usuariosFechadura)
                            ->keyBy('codpes');

        //usa ID ou matricula para verificar se o usuário existe
        $faltantes = $usuarios->diffKeys($loadUsers->keyBy('id'))
            ->merge($usuarios->diffKeys($loadUsers->keyBy('registration')))->keyBy('codpes');

        if ($faltantes->isNotEmpty()) {
            $api->createUsers($faltantes);
        }
        $api->updateUsers($usuarios);
    }

}
