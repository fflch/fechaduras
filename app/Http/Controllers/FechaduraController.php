<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FotoUpdateService;
use App\Models\Fechadura;
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
    public function sincronizar(Request $request, Fechadura $fechadura){
        //caso haja alguem diferente de fora do setor, adicionar um input com codpes para inserir "manualmente" na fechaduara
        
        //seria melhor jogar o login e o retorno dos usuarios numa Service?
        $ip = '10.84.0.62';
        $session = LockSessionService::conexao($ip, $fechadura->usuario, $fechadura->senha);
        $route = 'http://' . $ip . '/load_objects.fcgi?session=' . $session;
        $response = Http::post($route, [
            "object" => "users"
        ]);

        $usuariosFechadura = $response->json()['users'] ?? [];
        $usuariosReplicado = User::pessoa(env('REPLICADO_CODUNDCLG'));
        //$usuariosReplicado = User::all()->toArray(); //somente para melhor desempenho

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
        
        $faltantes = array_diff_key($replicadoId, $fechaduraReg); // preferência pela matrícula
        
        if(!empty($faltantes)){
            $response = ReplicadoService::cadastroUsuario($faltantes, $fechadura);
        }
        
        $response = ReplicadoService::updateUsuario($replicadoId, $fechadura);
        
        return back()->with('success','Dados sincronizados');
    }
}
