<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;

class SyncUsersAction
{
    public static function execute($fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $setores = $fechadura->setores->pluck('codset');
        $areas = $fechadura->areas->pluck('codare');

        // Busca usuários de setores e áreas
        $usuariosSetor = collect();
        $alunosPos = collect();

        if ($setores->isNotEmpty()) {
            $usuariosSetor = ReplicadoService::pessoa($setores->implode(','));
        }

        if ($areas->isNotEmpty()) {
            $alunosPos = ReplicadoService::retornaAlunosPos($areas->implode(','));
        }

        // Juntar todos os usuários vinculados (setores + áreas) para filtro
        $todosUsuariosVinculados = $usuariosSetor->merge($alunosPos)->pluck('codpes');

        // Filtrar usuários manuais (que não estão em setores/áreas)
        $usuariosManuais = collect();
        foreach ($fechadura->usuarios as $user) {
            if (!$todosUsuariosVinculados->contains($user->codpes)) {
                $usuariosManuais[$user->codpes] = [
                    'codpes' => $user->codpes,
                    'nompes' => $user->name,
                    'name' => $user->name
                ];
            }
        }

        // Combina todos os usuários
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
        $usersWithoutPhotos = [];
        foreach ($loadUsers as $userFechadura) {
            if ( $userFechadura['image_timestamp'] == 0 ) {
                $usersWithoutPhotos[] = $userFechadura['registration'] ?? $userFechadura['id'];
            }
        }
        $api->updateUsers($usuarios, $usersWithoutPhotos);
    }
}
