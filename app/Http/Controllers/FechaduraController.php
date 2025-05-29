<?php

namespace App\Http\Controllers;

use App\Services\LockSessionService;
use App\Models\Fechadura;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\FechaduraRequest;

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
    public function store(FechaduraRequest $request) {

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

    // Mostra formulário de edição
    public function edit(Fechadura $fechadura) {
        return view('fechaduras.edit', [
            'fechadura' => $fechadura
        ]);
    }

    // Atualiza fechadura
    public function update(FechaduraRequest $request, Fechadura $fechadura) {
        $fechadura->local = $request->local;
        $fechadura->ip = $request->ip;
        $fechadura->usuario = $request->usuario;

        // Só atualiza a senha se for informada
        if($request->senha) {
            $fechadura->senha = $request->senha;
        }

        $fechadura->save();

        return redirect("/fechaduras/{$fechadura->id}");
    }

    // Deleta fechadura
    public function destroy(Fechadura $fechadura) {
        $fechadura->delete();
        return redirect('/fechaduras');
    }

    //https://documenter.getpostman.com/view/7260734/S1LvX9b1?version=latest#76b4c5d7-e776-4569-bb19-341fdc1ccb7f
    //Sincroniza replicado com fechadura
    public function sincronizar(Fechadura $fechadura)
    {
        $apiService = new ApiService($fechadura);
        $apiService->syncUsers();

        return back()->with('success','Dados sincronizados');
    }
}
