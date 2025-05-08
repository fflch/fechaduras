<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class ReplicadoService{

    public static function cadastroUsuario($ip, array $faltantes){
        
        $sessao = LockSessionService::conexao($ip);

        $url = "http://" . $ip . "/create_objects.fcgi?session=" . $sessao;
        
        foreach($faltantes as $codpes => $faltante){
            $response = Http::asJson()->post($url,[
                'object' => 'users',
                'values' => [
                    [
                        'id' => (int)$faltante,
                        'registration' => (string)$faltante,
                        'name' => $faltante['nompesttd'] ?? $faltante['nompes'],
                        'password' => rand(100, 10000), //a fazer
                        'salt' => ''
                    ]
                ]
            ]);
            if($response->successful()){
                FotoUpdateService::updateFoto($ip, $codpes);
            }
        }
        return $response;
    }
    public static function updateUsuario($ip, array $usuarios){
        $sessao = LockSessionService::conexao($ip);

        $url = "http://". $ip ."/modify_objects.fcgi?session=".$sessao;
        
        foreach($usuarios as $codpes => $usuario){
            $response = Http::asJson()->post($url, [
                'object' => 'users',
                'values' => [
                    'id' => (int)$codpes,
                    'name' => $usuario['nompesttd'] ?? $usuario['nompes'],
                    'registration' => (string)$codpes,
                ],
                'where' => [
                    'users' => [
                        'id' => (int)$codpes
                    ]
                ]
            ]);
            if($response->successful()){
                FotoUpdateService::updateFoto($ip, $codpes);
            }
        }
        return $response;
    }
}