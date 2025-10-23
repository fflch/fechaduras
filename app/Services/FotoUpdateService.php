<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use Uspdev\Wsfoto;

class FotoUpdateService {

    // Upload de foto automática do sistema USP
    public static function updateFoto(Fechadura $fechadura, $codpes)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $codpes .'&timestamp='.time().'&match=0&session=' . $sessao;

        $foto = Wsfoto::obter($codpes);
        $img = base64_decode($foto);

        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($url);

        return $response;
    }

    // Upload de foto para usuários externos (na sincronização)
    public static function updateFotoExterna(Fechadura $fechadura, $userId, $fotoPath)
    {
        $sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );

        $url = 'http://' . $fechadura->ip . ':' . $fechadura->porta . '/user_set_image.fcgi?user_id='. $userId .'&timestamp='.time().'&match=0&session=' . $sessao;

        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody(
                file_get_contents(storage_path('app/public/' . $fotoPath)),
                'application/octet-stream'
            )->post($url);

        return $response;
    }
}