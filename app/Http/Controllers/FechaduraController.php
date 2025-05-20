<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FotoUpdateService;
use App\Models\Fechadura;
use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Uspdev\Replicado\Pessoa;
use Uspdev\Wsfoto;
use App\Services\LockSessionService;
use App\Services\ReplicadoService;
use App\Http\Controllers\UsuariosFechaduraController;

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
        $apiService = new ApiService($fechadura);
        $usuariosFechadura = $apiService->loadUsers();
        $usuariosReplicado = ReplicadoService::pessoa();
        //$usuariosReplicado = User::all()->toArray(); //somente para melhor desempenho

        $fechaduraId = [];
        $fechaduraReg = [];
        foreach($usuariosFechadura as $user){
            //pega todos os usuários da fechadura pela matrícula ou id (este número é o codpes).
            $fechaduraId[$user['id']] = $user; 
            $fechaduraReg[$user['registration']] = $user;
        }
        $faltantes = array_diff_key($usuariosReplicado, $fechaduraReg); // preferência pela matrícula
        
        $dadosFechadura = [ //mudar para dadosUsuario
            'fechaduraId' => $fechaduraId,
            'fechaduraReg' => $fechaduraReg,
        ];
        //1. verifica o usuário possui número de matrícula
        if(!empty($faltantes)){
            $response = UsuariosFechaduraController::store($faltantes, $fechadura, $dadosFechadura);
        }
        $response = UsuariosFechaduraController::update($usuariosReplicado, $fechadura);
        
        return back()->with('success','Dados sincronizados');
    }
}
