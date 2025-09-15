<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;
use Uspdev\Replicado\Pessoa;
use App\Models\Setor;
use App\Models\Area;

class SyncUsersAction
{
    public static function execute($fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $setores = $fechadura->setores->pluck('codset');
        $areas = $fechadura->areas->pluck('codare');
        
        // 1. Usuários dos setores configurados
        $usuariosSetor = $setores->isNotEmpty() ?
            ReplicadoService::pessoa($setores->implode(',')) :
            collect();

        // 2. Alunos de pós-graduação das áreas configuradas
        $alunosPos = $areas->isNotEmpty() ?
            ReplicadoService::retornaAlunosPos($areas->implode(',')) :
            collect();

        // 3. Usuários cadastrados manualmente
        $usuariosManuais = collect();

        // Obter todos os usuários de setores e áreas de uma vez
        $todosUsuariosSetores = collect();
        $todosUsuariosAreas = collect();

        if ($fechadura->setores->isNotEmpty()) {
            $todosUsuariosSetores = ReplicadoService::pessoa($fechadura->setores->pluck('codset')->implode(','));
        }

        if ($fechadura->areas->isNotEmpty()) {
            $todosUsuariosAreas = ReplicadoService::retornaAlunosPos($fechadura->areas->pluck('codare')->implode(','));
        }

        // Juntar todos os usuários de setores e áreas
        $todosUsuariosVinculados = $todosUsuariosSetores->merge($todosUsuariosAreas)->pluck('codpes');

        // Agora filtrar usuários manuais
        foreach ($fechadura->usuarios as $user) {
            if (!$todosUsuariosVinculados->contains($user->codpes)) {
                $usuariosManuais[$user->codpes] = [
                    'codpes' => $user->codpes,
                    'nompes' => $user->name,
                    'name' => $user->name
                ];
            }
        }

        // Combinar todos os usuários
        $usuarios = $usuariosSetor
            ->merge($alunosPos)
            ->merge($usuariosManuais)
            ->keyBy('codpes');

        // Verificar usuários faltantes na fechadura
        $faltantes = $usuarios->diffKeys($loadUsers->keyBy('id'))
            ->merge($usuarios->diffKeys($loadUsers->keyBy('registration')))
            ->keyBy('codpes');

        if ($faltantes->isNotEmpty()) {
            $api->createUsers($faltantes);
        }

        // Atualizar todos os usuários (fotos só para quem não tem)
        $api->updateUsers($usuarios, $loadUsers);
    }
}