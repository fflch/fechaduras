<?php

namespace App\Services;

use App\Models\User;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\LockSessionService;
use Uspdev\Wsfoto;

class FotoUpdateService {

    // Upload de foto automática do sistema USP
    public static function updateFoto(Fechadura $fechadura, $codpes, $fotoPath = null)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $codpes .'&timestamp='.time().'&match=0&session=' . $sessao;

        // Prioridade 1: foto usuário externo
        if ($fotoPath && Storage::disk('fotos')->exists($fotoPath)) {
            $img = Storage::disk('fotos')->get($fotoPath);
        }
        // Prioridade 2: foto local de usuario usp
        elseif (!$fotoPath) {
            $user = User::where('codpes', $codpes)->first();
            if ($user && $user->foto && Storage::disk('fotos')->exists($user->foto)) {
                $img = Storage::disk('fotos')->get($user->foto);
            }
        }

        // Se não tem foto local tenta do replicado
        if (!isset($img)) {
            $img = base64_decode(Wsfoto::obter($codpes));
            if (!$img) {
                return null; // Sem imagem disponível
            }
        }

        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($url);

        return $response;
    }
}
