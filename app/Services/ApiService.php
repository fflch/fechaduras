<?php

namespace App\Services;

use App\Actions\CreateSetorAction;
use App\Services\LockSessionService;
use \App\Models\Log;
use App\Actions\GroupAction;
use App\Models\Fechadura;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;

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
                
                $response = Http::asJson()->post($url, [
                    'object' => 'users',
                    'values' => [
                        'id' => (int)$codpes,
                        'name' => $usuario['nompesttd'] ?? $usuario['name'],
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
                    'name' => $usuario['nompesttd'] ?? $usuario['name'],
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
        foreach($usuariosSemGrupo as $codpes => $user){
            $response = Http::post($urlCreate, [
                'object' => 'user_groups',
                'fields' => ['user_id','group_id'],
                'values' => [
                    [
                        'user_id' => (int)$codpes,
                        'group_id' => 1
                    ]
                ]
            ]);
        }
    }

    public function loadLogs(){
        // 2 - Carregamento dos usuários cadastrados na fechadura
        $route = 'http://' . $this->fechadura->ip . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "users"
        ]);
        return $response;
    }

    // Atualiza os logs de acesso da fechadura no banco de dados local
    public function updateLogs()
    {
        $route = 'http://' . $this->fechadura->ip . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "access_logs",
            "limit" => 300,
            "order" => ["descending", "time"]
        ]);

        $logs = $response->json()['access_logs'] ?? [];

        $count = 0;
        foreach ($logs as $log) {

            Log::updateOrCreate(
                ['log_id_externo' => $log['id']],
                [
                    'event' => $log['event'],
                    'fechadura_id' => $this->fechadura->id,
                    'codpes' => $log['user_id'] ?? 0,
                    'datahora' => date('Y-m-d H:i:s', $log['time'])
                ]
            );
            $count++;
        }

        return $count;
    }

    // Sincroniza usuários entre o Replicado e fechadura
    public function syncUsers($request, $fechadura)
    {
        $loadUsers = $this->loadUsers();
        $fechadura_setores = array_column($fechadura->setores->toArray(), 'codset');
        $fechadura_usuarios = $fechadura->usuarios->keyBy('codpes')->toArray();
        
        if($fechadura_setores){
            $usuariosReplicado = ReplicadoService::pessoa($fechadura_setores);
            $usuariosReplicado += $fechadura_usuarios;
        }else{
            $usuariosReplicado = [];
            $usuariosReplicado += $fechadura_usuarios;
        }
        
        $collectUsersFechadura = collect($loadUsers);
        $fechaduraId = $collectUsersFechadura->keyBy('id')->toArray();
        $fechaduraReg = $collectUsersFechadura->keyBy('registration')->toArray();

        $faltantes = array_diff_key($usuariosReplicado, $fechaduraReg);
        
        $dadosFechadura = [
            'fechaduraId' => $fechaduraId,
            'fechaduraReg' => $fechaduraReg,
        ];
        if(!empty($faltantes)) {
            $this->createUsers($faltantes, $dadosFechadura);
        }
        $this->updateUsers($usuariosReplicado);

        return true;
    }

}
