<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;
use Uspdev\Wsfoto;
use App\Services\LockSessionService;

class FechaduraController extends Controller
{
    # https://www.controlid.com.br/docs/access-api-pt/primeiros-passos/realizar-login/

    //cadastrar quem não tem cadastro na fechadura e atualizar o registro de quem já está cadastrado
    public function index(){
        // 1 - requisição para login: rota: login.fcgi
        $ip = '10.84.0.62';
        $session = LockSessionService::conexao($ip);

        // 2 - listas usuários /load_objects.fcgi
        $route = 'http://' . $ip . '/load_objects.fcgi?session=' . $session;

        $response = Http::post($route, [
            "object" => "users",
        ]);
        
        $usuarios = $response->json()['users'];

        // 3 - passar os dados para a view
        return view('fechadura.index', ['usuarios' => $usuarios, 'session' => $session]);
    }

    //https://documenter.getpostman.com/view/7260734/S1LvX9b1?version=latest#76b4c5d7-e776-4569-bb19-341fdc1ccb7f
    public function sincronizar(Request $request){
        
        $ip = '10.84.0.62';
        $routeCreate = "http://" . $ip . "/create_objects.fcgi?session=" . $this->index()->session;
        $routeUpdate = "http://". $ip ."/modify_objects.fcgi?session=".$this->index()->session;

        $usuariosFechadura = $this->index()->usuarios;
        $usuariosReplicado = User::pessoa(env('REPLICADO_CODUNDCLG'));
        //$usuariosReplicado = User::all()->toArray(); //somente por perfomance.

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
                    'registration' => $codpes,
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
        }else{
            $response = Http::asJson()->post($routeUpdate,[ //link pra update
                'object' => 'users',
                'values' => [
                    'id' => $codpes,
                    'name' => $faltante['nompesttd'] ?? $faltante['nompes'],
                    'registration' => (string)$codpes,
                ],
                'where' => [
                    'users' => [
                        'id' => $codpes
                    ]
                ]
            ]);
            $ip = "10.84.0.62/user_set_image.fcgi?user_id=". $faltante['codpes'] ."&timestamp=1624997578&match=0&session=" . $this->index()->session;
            $data = $faltante['codpes'];
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
        return redirect()->back()->with('success','Dados sincronizados');
    }
}
