<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;
use App\Services\FotoUpdateService;

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

        // Adicionar usuários externos
        $usuariosExternos = collect();
        foreach ($fechadura->usuariosExternos as $usuarioExterno) {
            $externalId = 10000 + $usuarioExterno->id;

            $usuariosExternos[$externalId] = [
                'id' => $externalId,
                'codpes' => $externalId,
                'nompes' => $usuarioExterno->nome . ' - ' . $usuarioExterno->vinculo,
                'name' => $usuarioExterno->nome . ' - ' . $usuarioExterno->vinculo,
                'is_external' => true,
                'usuario_externo' => $usuarioExterno
            ];
        }

        // Combina todos os usuários
        $usuarios = $usuariosSetor
            ->merge($alunosPos)
            ->merge($usuariosManuais)
            ->merge($usuariosExternos)
            ->keyBy('codpes');

        // Usuários sem registration (serão excluídos)
        $usuariosSemRegistration = $loadUsers->filter(function ($user) {
            return empty($user['registration']) || $user['registration'] === '';
        });

        // IDs dos usuários do sistema
        $idsUsuariosSistema = $usuarios->keys()->map(function ($codpes) {
            return (int)$codpes;
        });

        // Usuários que estão na fechadura mas não estão no sistema
        $usuariosForaSistema = $loadUsers->filter(function ($user) use ($idsUsuariosSistema) {
            $userId = (int)$user['id'];
            return !$idsUsuariosSistema->contains($userId);
        });

        // Combinar ambas as listas (sem registration + fora do sistema)
        $todosParaExcluir = $usuariosSemRegistration->merge($usuariosForaSistema)->unique('id');

        // Exclui usuários da fechadura 
        if ($todosParaExcluir->isNotEmpty()) {
            $idsParaExcluir = $todosParaExcluir->pluck('id');
            
            $lotes = $idsParaExcluir->chunk(50);
            
            foreach ($lotes as $lote) {
                $api->deleteUsersBatch($lote->toArray());
            }
        }

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
            if ($userFechadura['image_timestamp'] == 0) {
                $usersWithoutPhotos[] = $userFechadura['registration'] ?? $userFechadura['id'];
            }
        }

        $api->updateUsers($usuarios, $usersWithoutPhotos);
    }
}
