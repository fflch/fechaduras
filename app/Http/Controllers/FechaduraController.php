<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Fechadura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;
use Uspdev\Wsfoto;
use App\Services\LockSessionService;

class FechaduraController extends Controller
{
    # https://www.controlid.com.br/docs/access-api-pt/primeiros-passos/realizar-login/

    # Métodos CRUD
    // Mostra fechaduras cadastradas 
    public function index() {
        $fechaduras = Fechadura::all();
        return view('fechaduras.index', [
            'fechaduras' => $fechaduras
        ]);
    }
    
    // Mostra formulário de criação
    public function create() {
        return view('fechaduras.create');
    }
    
    // Cadastra novas fechaduras
    public function store(Request $request) {
        $fechadura = new Fechadura();
        $fechadura->local = $request->local;
        $fechadura->ip = $request->ip;
        $fechadura->usuario = $request->usuario;
        $fechadura->senha = $request->senha;
        $fechadura->save();
    
        return redirect('/fechaduras');
    }
    
    // Mostra uma fechadura específica e lista os usuários cadastrados nela
    public function show(Fechadura $fechadura) {
        // 1 - Autenticação na API da fechadura
        $session = LockSessionService::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);
        
        // 2 - Carregamento dos usuários cadastrados na fechadura
        $route = 'http://' . $fechadura->ip . '/load_objects.fcgi?session=' . $session;
        $response = Http::post($route, [
            "object" => "users"
        ]);

        $usuarios = $response->json()['users'] ?? [];
        
        // 3 - passa os dados para a view
        return view('fechaduras.show', [
            'fechadura' => $fechadura,
            'usuarios' => $usuarios
        ]);
    }
    
    # Mostra formulário de edição
    public function edit(Fechadura $fechadura) {
        return view('fechaduras.edit', [
            'fechadura' => $fechadura
        ]);
    }
    
    # Atualiza fechadura
    public function update(Request $request, Fechadura $fechadura) {
        $fechadura->local = $request->local;
        $fechadura->ip = $request->ip;
        $fechadura->usuario = $request->usuario;
        $fechadura->senha = $request->senha;
    
        $fechadura->save();
    
        return redirect("/fechaduras/{$fechadura->id}");
    }
    
    # Dela fechadura
    public function destroy(Fechadura $fechadura) {
        $fechadura->delete();
        return redirect('/fechaduras');
    }

    //https://documenter.getpostman.com/view/7260734/S1LvX9b1?version=latest#76b4c5d7-e776-4569-bb19-341fdc1ccb7f
    public function sincronizar(Request $request){
        
        $ip = '10.84.0.62';
        $routeCreate = "http://" . $ip . "/create_objects.fcgi?session=" . $this->index()->session;
        $routeUpdate = "http://". $ip ."/modify_objects.fcgi?session=".$this->index()->session;

        $usuariosFechadura = $this->index()->usuarios;
        $usuariosReplicado = User::pessoa(env('REPLICADO_CODUNDCLG'));

        $fechaduraId = [];
        $fechaduraReg = [];
        foreach($usuariosFechadura as $user){
            $fechaduraId[$user['id']] = $user; //pega todos os usuários da fechadura pela matrícula (este número é o codpes).
            $fechaduraReg[$user['registration']] = $user;
        }
        
        $replicadoId = [];
        foreach($usuariosReplicado as $fflch){
            $replicadoId[$fflch['codpes']] = $fflch; //pega todos os usuários do replicado pelo codpes
        }
        
        $faltantes = array_diff_key($replicadoId, $fechaduraReg); // usar a matrícula

        foreach($faltantes as $codpes => $faltante){
        $isset = 
        isset($fechaduraReg[$codpes]['registration'])
        ? $fechaduraReg[$codpes]['registration'] 
        : $fechaduraId[$codpes]['id'] ?? ''; //pega o codpes pelo ID ou Matrícula do usuário, se não houver nenhum, ocorre o cadastro.
        
        if(!empty($faltantes[$codpes]) && 
        $faltantes[$codpes]['codpes'] != $isset){ //vefificar se já existe um codpes, se não existir, cadastre. Se existir atualize.
            
            $response = Http::asJson()->post($routeCreate, [
                'object' => 'users',
                'values' => [
                    [
                    //cadastrando nº usp como id pra evitar possíveis conflitos futuros (há usuários já cadastrados com id ordinal)
                    'id' => $codpes, 
                    'registration' => (string)$codpes,
                    'name' => $faltante['nompesttd'] ?? $faltante['nompes'],
                    'password' => '', 
                    'salt' => ''
                    ]
                ]
            ]);
            if($faltantes[$codpes]){
                $ip = "10.84.0.62/user_set_image.fcgi?user_id=". $codpes ."&timestamp=1624997578&match=0&session=" . $this->index()->session;
                $data = $codpes;
                $foto = Wsfoto::obter($data);
                header('Content-Type: image/png');
                $img = base64_decode($foto);
                if(isset($foto)){
                    $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
                    ->withBody($img, 'application/octet-stream')
                    ->post($ip);    
                }
            }
        }
    }
    
    foreach($replicadoId as $codpes => $replicado){
        $response = Http::asJson()->post($routeUpdate,[ //link pra update
            'object' => 'users',
            'values' => [
                'id' => (int)$codpes,
                'name' => $replicado['nompesttd'] ?? $replicado['nompes'],
                'registration' => (string)$codpes,
            ],
            'where' => [
                'users' => [
                    'id' => (int)$codpes
                ]
            ]
        ]);
        $ip = "10.84.0.62/user_set_image.fcgi?user_id=". $codpes ."&timestamp=1624997578&match=0&session=" . $this->index()->session;
        
        $foto = Wsfoto::obter($codpes);
        header('Content-Type: image/png');
        $img = base64_decode($foto);
        if(isset($img)){
            $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($ip);
        }
    }
        return back()->with('success','Dados sincronizados');
    }
}
