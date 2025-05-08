<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FotoUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;
use Uspdev\Wsfoto;
use App\Services\LockSessionService;
use App\Services\ReplicadoService;

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
        //caso haja alguem diferente de fora do setor, adicionar um input com codpes para inserir "manualmente" na fechaduar
        
        $ip = '10.84.0.62';
        $usuariosFechadura = $this->index()->usuarios;
        $usuariosReplicado = User::pessoa(env('REPLICADO_CODUNDCLG'));
        //$usuariosReplicado = User::all()->toArray();

        $fechaduraId = [];
        $fechaduraReg = [];
        foreach($usuariosFechadura as $user){
            //pega todos os usuários da fechadura pela matrícula ou id (este número é o codpes).
            $fechaduraId[$user['id']] = $user; 
            $fechaduraReg[$user['registration']] = $user;
        }
        
        $replicadoId = [];
        foreach($usuariosReplicado as $fflch){
            $replicadoId[$fflch['codpes']] = $fflch; //pega todos os usuários do replicado pelo codpes
        }
        
        
        $faltantes = array_diff_key($replicadoId, $fechaduraReg); // usar a matrícula
        dd($faltantes);
        //jogar todo o foreach na service
        if(!empty($faltantes)){
            $response = ReplicadoService::cadastroUsuario($ip, $faltantes);
        }
        
        $response = ReplicadoService::updateUsuario($ip, $replicadoId);
        
        return back()->with('success','Dados sincronizados');
    }
}
