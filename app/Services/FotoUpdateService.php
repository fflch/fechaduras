<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Actions\SessionAction;
use Uspdev\Wsfoto;

class FotoUpdateService {
//mudar pra action?
    public static function updateFoto(Fechadura $fechadura, $codpes){

        $sessao = SessionAction::conexao($fechadura->ip,$fechadura->usuario, $fechadura->senha);

        $url = $fechadura->ip . '/user_set_image.fcgi?user_id='. $codpes ."&timestamp=".time()."&match=0&session=" . $sessao;
        
        $foto = Wsfoto::obter($codpes);
        $img = base64_decode($foto);
        
        $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
        ->withBody($img, 'application/octet-stream')
        ->post($url);
        
        return $response;
    }

}