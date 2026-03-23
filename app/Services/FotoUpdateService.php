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
    public static function updateFoto(Fechadura $fechadura, $codpes, $foto = null)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $codpes .'&timestamp='.time().'&match=0&session=' . $sessao;

        // Prioridade 1: foto usuário externo
        if ( ! is_null($foto) ) {
            $img = Storage::disk('fotos')->get($foto);
        }
        else {
            $user = User::where('codpes', $codpes)->first();
            // Prioridade 2: foto local de usuario usp ou do Wsfoto
            $img = $user?->foto ? Storage::disk('fotos')->get($user->foto) :
                base64_decode(Wsfoto::obter($codpes));
        }

        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($url);

        return $response;
    }
}
