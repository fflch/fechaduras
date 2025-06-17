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
        $usuariosFechadura = $fechadura->usuarios->select(['codpes','name'])->keyBy('codpes');

        $usuariosReplicado = $setores->isNotEmpty() ?
            ReplicadoService::pessoa($setores->implode('codset', ',')) :
            collect();

        $usuarios = $usuariosReplicado->merge($usuariosFechadura)->keyBy('codpes');

        $faltantes = $usuarios->diffKeys($loadUsers->keyBy('id'))
            ->merge($usuarios->diffKeys($loadUsers->keyBy('registration')))->keyBy('codpes');

        if ($faltantes->isNotEmpty()) {
            $api->createUsers($faltantes);
        }
        $api->updateUsers($usuarios);
    }

}
