<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;

class SyncUsersAction
{
    /**
     * Create a new class instance.
     */

     public $fechadura;

    public function __construct($fechadura)
    {
        $this->fechadura = $fechadura;
    }

    public function execute($request, $fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $fechadura_setores = $fechadura->setores->select('codset');
        $fechadura_usuarios = $fechadura->usuarios->select(['codpes','name'])->keyBy('codpes');

        $usuariosReplicado = $fechadura_setores->when($fechadura_setores->isNotEmpty(), function($codsets){
            return ReplicadoService::pessoa($codsets->implode('codset',','));
        }, function() use ($fechadura_usuarios){
            return $fechadura_usuarios;
        })->mergeRecursive($fechadura_usuarios)->keyBy('codpes');

        $faltantes = $usuariosReplicado->diffKeys($loadUsers->keyBy('registration'));
        if($faltantes->isNotEmpty()){
            $chaves = collect(['id','registration']);
            $dadosFechadura = $chaves->combine([$loadUsers->keyBy('id'), $loadUsers->keyBy('registration')]);
            $api->createUsers($faltantes, $dadosFechadura);
        }
        $api->updateUsers($usuariosReplicado);

    }

}
