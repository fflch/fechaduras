<?php

namespace App\Services;

use App\Actions\CreateUserAction;
use App\Services\LockSessionService;
use App\Actions\CreateUserGroupAction;
use App\Actions\GroupAction;
use App\Actions\UpdateUserAction;
use Illuminate\Support\Facades\Http;

class ApiService
{
    /**
     * Create a new class instance.
     */

    protected $fechadura;
    protected $sessao;

    public function __construct($fechadura)
    {
        $this->fechadura = $fechadura;
        $this->sessao = LockSessionService::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);
    }

    public function loadUsers(){
        $route = 'http://' . $this->fechadura->ip . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "users"
        ]);
        return $response->json()['users'] ?? [];
    }

    public function createUsers($faltantes, $dadosFechadura){
        $url = 'http://' . $this->fechadura->ip . '/create_objects.fcgi?session=' . $this->sessao;

        foreach ($faltantes as $codpes => $usuario) {
            /*
            2. Verifica se o usuário existe na fechadura pela matrícula ou id (este número é o codpes).
            caso o usuario não exista, pois não há ID nem MATRÍCULA, será feito o cadastro
            */
            $codpesFaltante = 
            isset($dadosFechadura['fechaduraReg'][$codpes]['registration'])
            ? $dadosFechadura['fechaduraReg'][$codpes]['registration']
            : $dadosFechadura['fechaduraId'][$codpes]['id'] ?? '';

            if(!empty($faltantes[$codpes]) && $faltantes[$codpes]['codpes'] != $codpesFaltante){
                dd('cadastrar');
                $response = Http::asJson()->post($url, [
                    'object' => 'users',
                    'values' => [
                        'id' => (int)$codpes,
                        'name' => $usuario['nompesttd'] ?? $usuario['nompes'],
                        'registration' => (string)$codpes,
                    ]
                ]);
                if($response->successful()){
                    FotoUpdateService::updateFoto($this->fechadura, $codpes);
                    GroupAction::createUserGroups($this->fechadura, $codpes, $faltantes); //verificar depois
                }
            }
        }
    }

    public function updateUsers($usuariosReplicado){
        $url = 'http://' . $this->fechadura->ip . '/modify_objects.fcgi?session=' . $this->sessao;
        foreach($usuariosReplicado as $codpes => $usuario){
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
                FotoUpdateService::updateFoto($this->fechadura, $codpes);
                GroupAction::createUserGroups($this->fechadura, $codpes, $usuariosReplicado);
            }
        }
    }

    public function loadUserGroups(){
        $route = 'http://' . $this->fechadura->ip . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "user_groups"
        ]);

        return $response->json()['user_groups'] ?? [];
    }

    public function createUserGroups($usuariosSemGrupo){
        $urlCreate = "http://" . $this->fechadura->ip . "/create_objects.fcgi?session=" . $this->sessao;
        foreach($usuariosSemGrupo as $user){
            $response = Http::post($urlCreate, [
                'object' => 'user_groups',
                'fields' => ['user_id','group_id'],
                'values' => [
                    [
                        'user_id' => (int)$user['codpes'],
                        'group_id' => 1
                    ]
                ]
            ]);
        }
    }

}
