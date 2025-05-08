<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use Uspdev\Wsfoto;

class FotoUpdateService {
//mudar pra action?
    public static function updateFoto($ip, $codpes){
        $sessao = LockSessionService::conexao($ip);

        $url = $ip . '/user_set_image.fcgi?user_id='. $codpes ."&timestamp=".time()."&match=0&session=" . $sessao;
        
        $foto = Wsfoto::obter($codpes);
        $img = base64_decode($foto);
        
        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
        ->withBody($img, 'application/octet-stream')
        ->post($url);
        
        return $response;
    }

}