<?php

namespace App\Actions;

use App\Services\ApiControlIdService;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class CreateGroupAction
{
    public static function execute($fechadura, $codpes, $usuarios)
    {
        $apiService = new ApiControlIdService($fechadura);
        $grupo_usuarios = collect($apiService->loadUserGroups())->keyBy('user_id');
        $usuariosSemGrupo = $usuarios->diffKeys($grupo_usuarios);
        
        $apiService->createUserGroups($usuariosSemGrupo);
    }
}