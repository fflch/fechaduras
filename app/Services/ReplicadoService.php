<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;

class ReplicadoService{

    public static function cadastroUsuario(array $faltantes, Fechadura $fechadura){
        
        $sessao = LockSessionService::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);

        $url = "http://" . $fechadura->ip . "/create_objects.fcgi?session=" . $sessao;
        
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
                FotoUpdateService::updateFoto($fechadura, $codpes);
            }
        }
        return $response;
    }
    public static function updateUsuario(array $usuarios, Fechadura $fechadura){
        $sessao = LockSessionService::conexao($fechadura->ip, $fechadura->usuario,$fechadura->senha);

        $url = "http://". $fechadura->ip ."/modify_objects.fcgi?session=".$sessao;
        
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
                FotoUpdateService::updateFoto($fechadura, $codpes);
            }
        }
        return $response;
    }
}