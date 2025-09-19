<?php

namespace App\Services;

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
        $this->sessao = LockSessionService::conexao(
            $fechadura->ip, $fechadura->porta, $fechadura->usuario, $fechadura->senha
        );
    }

    public function loadUsers(){
        $route = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "users"
        ]);
        return $response->json()['users'] ?? [];
    }

    public function createUsers($faltantes){
        $url = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/create_objects.fcgi?session=' . $this->sessao;

        $loadUsers = $this->loadUsers();

        foreach ($faltantes as $codpes => $usuario) {
            $response = Http::asJson()->post($url, [
                'object' => 'users',
                'values' => [[
                    'id' => (int)$codpes,
                    'name' => $usuario['nompes'] ?? $usuario['name'],
                    'registration' => (string)$codpes,
                ]]
            ]);

            if($response->successful()){
                FotoUpdateService::updateFoto($this->fechadura, $codpes, false, $loadUsers);
                $this->createUserGroups($codpes);
            }
        }
    }

    public function updateUsers($usuarios, $usersWithoutPhotos){
        $url = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/modify_objects.fcgi?session=' . $this->sessao;

        foreach($usuarios as $codpes => $usuario){
            // Atualiza informações básicas do usuário
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
                // Atualizar foto apenas se o usuário não tiver foto
                if (in_array($codpes, $usersWithoutPhotos)) {
                    FotoUpdateService::updateFoto($this->fechadura, $codpes);
                }

                $this->createUserGroups($codpes);
            }
        }
    }

    public function loadUserGroups(){
        $route = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "user_groups"
        ]);

        return $response->json()['user_groups'] ?? [];
    }

    public function createUserGroups($codpes, $group = 1){
        $urlCreate = "http://" . $this->fechadura->ip . ':' . $this->fechadura->porta . "/create_objects.fcgi?session=" . $this->sessao;
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
        $route = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/load_objects.fcgi?session=' . $this->sessao;
        $response = Http::post($route, [
            "object" => "users"
        ]);
        return $response;
    }

    // Atualiza os logs de acesso da fechadura no banco de dados local
    public function updateLogs()
    {
        $route = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/load_objects.fcgi?session=' . $this->sessao;
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

    public function uploadFoto($userId, $foto)
    {
        $url = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/user_set_image.fcgi?user_id='. $userId ."&timestamp=".time()."&match=0&session=" . $this->sessao;

        $response = Http::timeout(30)->withHeaders([
            'Content-Type' => 'application/octet-stream'
        ])->withBody(
            file_get_contents($foto->path()),
            'application/octet-stream'
        )->post($url);

        // Analisa a resposta JSON
        return [
            'success' => $response->json('success'),
            'message' => $response->json('success') ?
                'Foto cadastrada com sucesso.' :
                $this->getErrorMessage($response->json('errors'))
        ];
    }

    // Método para traduzir códigos de erro
    private function getErrorMessage(array $errors)
    {
        $errorMessages = [
            1 => 'Erro nos parâmetros da requisição ou formato de imagem inválido.',
            2 => 'Rosto não detectado na imagem.',
            3 => 'Esta face já está cadastrada para outro usuário.',
            4 => 'Rosto não está centralizado na imagem.',
            5 => 'Rosto muito distante da câmera.',
            6 => 'Rosto muito próximo da câmera.',
            7 => 'Rosto não está posicionado corretamente (está torto).',
            8 => 'Imagem com baixa nitidez.',
            9 => 'Rosto muito próximo das bordas da imagem.'
        ];

        $error = head($errors);

        return $errorMessages[$error['code']] ?? 'Erro ao cadastrar a foto, erro não definido.';
    }

    public function cadastrarSenha($userId, $senha)
    {
        // 1. Gerar o hash da senha
        $hashUrl = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/user_hash_password.fcgi?session=' . $this->sessao;
        $hashResponse = Http::asJson()->post($hashUrl, [
            'password' => (string)$senha
        ]);

        $hashedData = $hashResponse->json();

        // 2. Atualizar o usuário com o hash
        $updateUrl = 'http://' . $this->fechadura->ip . ':' . $this->fechadura->porta . '/modify_objects.fcgi?session=' . $this->sessao;

        $response = Http::asJson()->post($updateUrl, [
            'object' => 'users',
            'values' => [
                'password' => $hashedData['password'],
                'salt' => $hashedData['salt']
            ],
            'where' => [
                'users' => [
                    'id' => (int)$userId
                ]
            ]
        ]);

        return $response->successful();
    }
}
