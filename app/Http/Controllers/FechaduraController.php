<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;
use Uspdev\Wsfoto;

class FechaduraController extends Controller
{
    # https://www.controlid.com.br/docs/access-api-pt/primeiros-passos/realizar-login/

    //cadastrar quem não tem cadastro na fechadura e atualizar o registro de quem já está cadastrado
    public function index(){

        // 1 - requisição para login: rota: login.fcgi
        $ip = '10.84.0.62';
        $response = Http::post('http://' . $ip . '/login.fcgi', [
            'login' => env('FECHADURAS_LOGIN'),
            'password' => env('FECHADURAS_PASSWORD'),
        ]);
        $response = $response->json();
        $session = $response['session'];

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

        $fechaduraId = [];
        foreach($usuariosFechadura as $user){
            $fechaduraId[$user['registration']] = $user; //pega todos os usuários da fechadura pela matrícula (este número é o codpes).
        }
        
        $replicadoId = [];
        foreach($usuariosReplicado as $fflch){
            $replicadoId[$fflch['codpes']] = $fflch; //pega todos os usuários do replicado pelo codpes
        }

        /*
        diferença entre os usuários da fechadura e o replicado. 
        Se alguém novo for inserido no replicado, será cadastrado na fechadura
        */
        $faltantes = array_diff_key($replicadoId, $fechaduraId); 

        foreach($faltantes as $codpes => $faltante){
        
        //if(!isset($fechaduraId[$codpes])){
        //if(!empty($faltantes[$codpes])){
        if(!isset($faltantes)){ //vefificar se já existe um codpes, se não existir, cadastre. Se existir atualize.
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
            return redirect()->back()->with('success','Usuário(s) cadastrado(s)');
        }else{
            $response = Http::asJson()->post($routeUpdate,[ //link pra update
                'object' => 'users',
                'values' => [
                    'name' => $faltante['nompesttd'] ?? $faltante['nompes'],
                    'registration' => "$codpes",
                ],
                'where' => [
                    'users' => [
                        'id' => $codpes
                    ]
                ]
            ]);
        }
    }   
        return redirect()->back()->with('success','Dados sincronizados');
    }

    public function fotos(){

        $usuariosReplicado = User::pessoa(env('REPLICADO_CODUNDCLG'));

        foreach($usuariosReplicado as $usuario){
            $ip = "10.84.0.62/user_set_image.fcgi?user_id=". $usuario['codpes'] ."&timestamp=1624997578&match=0&session=" . $this->index()->session;
            $data = $usuario['codpes'];
            $foto = Wsfoto::obter($data);
            header('Content-Type: image/png');
            $img = base64_decode($foto);

            //$imageData = File::get(public_path('ich.png'));
            $response = Http::withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($img, 'application/octet-stream')
            ->post($ip);    
        }
    
        if($response->successful()){
            echo 'sucesso';
        }else{
            echo $response->getReasonPhrase() .  $response;
        }
    }
}
