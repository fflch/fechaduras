<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use App\Services\GroupService;

class ReplicadoService{

    public static function cadastroUsuario(array $faltantes, Fechadura $fechadura, $dados){
        $sessao = LockSessionService::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);

        $url = "http://" . $fechadura->ip . "/create_objects.fcgi?session=" . $sessao;
        
        foreach($faltantes as $codpes => $faltante){
            /*
            2. Verifica se o usuário existe na fechadura pela matrícula ou id (este número é o codpes).
            caso o usuario não exista, pois não há ID nem MATRÍCULA, será feito o cadastro
            */
            $codpesFaltante = 
            isset($dados['fechaduraReg'][$codpes]['registration'])
            ? $dados['fechaduraReg'][$codpes]['registration']
            : $dados['fechaduraId'][$codpes]['id'] ?? '';
            if(!empty($faltantes[$codpes]) && $faltantes[$codpes]['codpes'] != $codpesFaltante){
                $response = Http::asJson()->post($url,[
                    'object' => 'users',
                    'values' => [
                        [
                            'id' => (int)$faltante,
                            'registration' => (string)$faltante,
                            'name' => $faltante['nompesttd'] ?? $faltante['nompes'],
                            'password' => (string)$faltante['codpes'],
                            'salt' => ''
                        ]
                    ]
                ]);
                if($response->successful()){
                    FotoUpdateService::updateFoto($fechadura, $codpes);
                    //GroupService::createUserGroups($fechadura, $codpes, $faltantes); //verificar depois
                }
            }
        }
        return $response ?? [];
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
                GroupService::createUserGroups($fechadura, $codpes, $usuarios);
            }
        }
        return $response;
    }
}