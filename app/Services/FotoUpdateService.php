<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use Uspdev\Wsfoto;

class FotoUpdateService {

    // Upload de foto automática do sistema USP, pega foto de WSfoto ao sincronizar 
    public static function updateFoto(Fechadura $fechadura, $codpes, $force = false)
    {
        // Se não for forçado, verifica se já existe foto na fechadura
        if (!$force) {
            // Usa a lista de usuários fornecida ou carrega se não for fornecida
            $usuarios = $usuariosFechadura ?? (new ApiControlIdService($fechadura))->loadUsers();
            
            // Encontra o usuário na lista da fechadura
            foreach ($usuarios as $usuario) {
                // Verifica tanto por ID quanto por registration para maior segurança
                $userId = $usuario['id'] ?? null;
                $userRegistration = $usuario['registration'] ?? null;
                
                if (($userId == $codpes || $userRegistration == $codpes) && 
                    ($usuario['image_timestamp'] > 0)) {
                    // Usuário já tem foto, não sobrescreve
                    return ['skipped' => true, 'message' => 'Foto já existe, mantida'];
                }
            }
        }

        // Se chegou aqui, ou é forçado ou não tem foto ainda
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $codpes ."&timestamp=".time()."&match=0&session=" . $sessao;

        $foto = Wsfoto::obter($codpes);
        $img = base64_decode($foto);

        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($url);

        return $response;
    }
}
