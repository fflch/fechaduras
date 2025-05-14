<?php

namespace App\Services;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class GroupService
{
    public static function createUserGroups($fechadura, $codpes, $usuarios)
    {
        $sessao = LockSessionService::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);
        $loadUsersGroupUrl = "http://" . $fechadura->ip . "/load_objects.fcgi?session=" . $sessao;
        $urlCreate = "http://" . $fechadura->ip . "/create_objects.fcgi?session=" . $sessao;

        //1. varre todos os usuários que já possuem departamento para fazer uma comparação
        $usuariosComGrupo = Http::post($loadUsersGroupUrl, [
            'object' => 'user_groups',
        ]);
        
        $grupo_usuarios = $usuariosComGrupo->json()['user_groups']; //usuários com grupos
        
        $reindexado = [];
        foreach($grupo_usuarios as $grupo_usuario){
            $reindexado[$grupo_usuario['user_id']] = $grupo_usuario;
        }
        //pega somente quem está sem grupo
        $idsComGrupo = array_column($reindexado, 'user_id');
            $usuariosSemGrupo = array_filter($usuarios, function($usuario) use ($idsComGrupo){
                return !in_array($usuario['codpes'], $idsComGrupo);
            });
        
        foreach($usuariosSemGrupo as $user){
        $response = Http::post($urlCreate, [
            'object' => 'user_groups',
            'fields' => ['user_id','group_id'],
            'values' => [
                [
                    'user_id' => (int)$user['codpes'],
                    'group_id' => 1,
                ]
            ]
        ]);
    }
        return $response ?? [];
    }
}