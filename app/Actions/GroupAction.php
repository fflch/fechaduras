<?php

namespace App\Actions;

use App\Services\ApiService;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class GroupAction
{
    public static function createUserGroups($fechadura, $codpes, $usuarios)
    {
        //1. varre todos os usuários que já possuem departamento para fazer uma comparação
        $apiService = new ApiService($fechadura);
        $grupo_usuarios = $apiService->loadUserGroups(); //usuarios com grupo
        
        $reindexado = [];
        foreach($grupo_usuarios as $grupo_usuario){
            $reindexado[$grupo_usuario['user_id']] = $grupo_usuario;
        }
        
        //2. Pega todos os usuários que não possuem grupo
        $idsComGrupo = array_column($reindexado, 'user_id');
            $usuariosSemGrupo = array_filter($usuarios, function($usuario) use ($idsComGrupo){
                return !in_array($usuario['codpes'], $idsComGrupo);
            });

        $apiService->createUserGroups($usuariosSemGrupo);
    }
}