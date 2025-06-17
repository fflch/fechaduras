<?php

namespace App\Services;

use App\Actions\CreateGroupAction;
use App\Services\LockSessionService;
use \App\Models\Log;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;

class ApiControlIdService
{
    /**
     * Create a new class instance.
     */

    protected $fechadura;
    protected $sessao;

    public function __construct(Fechadura $fechadura)
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

    public function createUsers($faltantes){
        $url = 'http://' . $this->fechadura->ip . '/create_objects.fcgi?session=' . $this->sessao;

        foreach ($faltantes as $codpes => $usuario) {
            $response = Http::asJson()->post($url, [
                'object' => 'users',
                'values' => [
                    'id' => (int)$codpes,
                    'name' => $usuario['nompes'] ?? $usuario['name'],
                    'registration' => (string)$codpes,
                ]
            ]);

            if($response->successful()){
                FotoUpdateService::updateFoto($this->fechadura, $codpes);
                $this->createUserGroups($codpes);
            }
        }
    }

    public function updateUsers($usuarios){
        $url = 'http://' . $this->fechadura->ip . '/modify_objects.fcgi?session=' . $this->sessao;
        foreach($usuarios as $codpes => $usuario){
            $response = Http::asJson()->post($url, [
                'object' => 'users',
                'values' => [
                    'id' => (int)$codpes,
                    'name' => $usuario['nompes'] ?? $usuario['name'],
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
                $this->createUserGroups($codpes);

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

    public function createUserGroups($codpes, $group = 1){
        $urlCreate = "http://" . $this->fechadura->ip . "/create_objects.fcgi?session=" . $this->sessao;
        Http::post($urlCreate, [
            'object' => 'user_groups',
            'fields' => ['user_id','group_id'],
            'values' => [
                [
                    'user_id' => (int)$codpes,
                    'group_id' => $group
                ]
            ]
        ]);
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
}
