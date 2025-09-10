<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;
use App\Models\User;
use Uspdev\Replicado\Pessoa;

class SyncUsersAction
{
    public static function execute($fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $setores = $fechadura->setores->select('codset');
        $areas = $fechadura->areas->select('codare');
        
        // Obtem todos os usuários vinculados à fechadura 
        $usuariosFechadura = $fechadura->usuarios()->get()->keyBy('codpes');

        // 1. Usuários dos setores configurados
        $usuariosSetor = $setores->isNotEmpty() ?
            ReplicadoService::pessoa($setores->implode('codset', ',')) :
            collect();

        // 2. Alunos de pós-graduação das áreas configuradas
        $alunosPos = $areas->isNotEmpty() ?
            ReplicadoService::retornaAlunosPos($areas->implode('codare',',')) :
            collect();

        // 3. Usuários cadastrados manualmente na fechadura (sem setor)
        $usuariosManuais = collect();
        foreach ($usuariosFechadura as $codpes => $user) {
            // Verifica se o usuário não está em nenhum setor/área configurado
            $hasSetor = $fechadura->setores()->whereHas('usuarios', function($query) use ($codpes) {
                $query->where('codpes', $codpes);
            })->exists();
            
            $hasArea = $fechadura->areas()->whereHas('usuarios', function($query) use ($codpes) {
                $query->where('codpes', $codpes);
            })->exists();
            
            if (!$hasSetor && !$hasArea) {
                // Busca informações do usuário no replicado
                $pessoa = Pessoa::dump($codpes);
                if ($pessoa) {
                    $usuariosManuais[$codpes] = [
                        'codpes' => $codpes,
                        'nompes' => $pessoa['nompesttd'] ?? $pessoa['nompes'] ?? 'Nome não encontrado',
                        'name' => $pessoa['nompesttd'] ?? $pessoa['nompes'] ?? 'Nome não encontrado'
                    ];
                }
            }
        }

        // Combinar todos os usuários
        $usuarios = $usuariosSetor
            ->merge($alunosPos)
            ->merge($usuariosManuais)
            ->keyBy('codpes');

        // Verifica usuários faltantes na fechadura
        $faltantes = $usuarios->diffKeys($loadUsers->keyBy('id'))
            ->merge($usuarios->diffKeys($loadUsers->keyBy('registration')))
            ->keyBy('codpes');

        if ($faltantes->isNotEmpty()) {
            $api->createUsers($faltantes);
        }

        // Atualiza todos os usuários 
        $api->updateUsers($usuarios);
    }
}