<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Services\ReplicadoService;
use App\Services\FotoUpdateService;
use App\Models\User;

class SyncUsersAction
{
    public static function execute($fechadura){
        $api = new ApiControlIdService($fechadura);
        $loadUsers = collect($api->loadUsers());
        $setores = $fechadura->setores->pluck('codset');
        $areas = $fechadura->areas->pluck('codare');

        // Busca usuários bloqueados
        $usuariosBloqueados = $fechadura->usuariosBloqueados->pluck('codpes');

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

        // Combina todos os usuários (menos bloqueados)
        $usuarios = $usuariosSetor
            ->merge($alunosPos)
            ->merge($usuariosManuais)
            ->merge($usuariosExternos)
            ->filter(function ($usuario) use ($usuariosBloqueados) {
                return $usuariosBloqueados->doesntContain($usuario['codpes']);
            })
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

        // Combinar todas as listas para exclusão
        $todosParaExcluir = $usuariosSemRegistration->merge($usuariosForaSistema)->unique('id');

        // Exclui usuários da fechadura
        if ($todosParaExcluir->isNotEmpty()) {
            $idsParaExcluir = $todosParaExcluir->pluck('id');

            foreach ($idsParaExcluir->chunk(50) as $lote) {
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

        // Atualizar fotos dos usuários que não têm foto na fechadura
        foreach ($loadUsers as $userFechadura) {
            // Verifica se o usuário não tem foto na fechadura
            if ($userFechadura['image_timestamp'] == 0) {
                $codpes = $userFechadura['registration'] ?? $userFechadura['id'];
                
                // Verifica se é usuário externo 
                $usuarioInfo = $usuarios->get($codpes);
                if ($usuarioInfo && isset($usuarioInfo['is_external']) && $usuarioInfo['is_external']) {
                    continue;
                }

                // Tenta foto local primeiro
                $user = User::where('codpes', $codpes)->first();
                if ($user && $user->foto) {
                    FotoUpdateService::updateFoto($fechadura, $codpes, $user->foto);
                } 
                // Se não tem foto local, usa do replicado
                elseif ($usuarios->has($codpes)) {
                    FotoUpdateService::updateFoto($fechadura, $codpes, null);
                }
            }
        }
    }
}