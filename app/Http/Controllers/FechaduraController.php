<?php

namespace App\Http\Controllers;

use App\Actions\CreateSetorAction;
use App\Services\LockSessionService;
use App\Models\Fechadura;
use App\Models\Acesso;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\FechaduraRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\Request;

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

    // Método para logs
    public function logs(Fechadura $fechadura)
    {
        // Busca os logs do banco local, ordenados pelos mais recentes
        $acessos = Acesso::where('fechadura_id', $fechadura->id)
                    ->orderBy('datahora', 'desc')
                    ->paginate(20); // Paginação para muitos registros

        return view('fechaduras.logs', [
            'fechadura' => $fechadura,
            'acessos' => $acessos
        ]);
    }

    //Atualiza logs
    public function updateLogs(Fechadura $fechadura)
    {
        $apiService = new ApiService($fechadura);
        $count = $apiService->updateLogs();

        if($count === false) {
            return back()->with('error', 'Erro ao atualizar os logs na base local!');
        }

        return back()->with('success', "{$count} logs atualizados");
    }

    public function createFechaduraUser(Fechadura $fechadura, Request $request){
        if(!$request->codpes){
            request()->session()->flash('alert-danger', 'Informe número USP!');
            return back();
        }
        $usuariosRequest = explode(',', $request->codpes);
        $requestsNumericos = array_filter($usuariosRequest, 'is_numeric');
        foreach($requestsNumericos as $codpes){
            $codpes = UsuarioService::verifyAndCreateUsers($codpes, $fechadura, $request);
            if($codpes instanceof \Illuminate\Http\RedirectResponse){
                return $codpes;
            }
        }
        request()->session()->flash('alert-success', "Usuário(s) cadastrado(s) com sucesso!");
        return back();
    }

    public function createFechaduraSetor(Fechadura $fechadura, Request $request){
        $fechadura->setores()->detach(); //remove todos os setores antes de adicionar novos
        if(empty($request->setores)){
            $request->session()->flash('alert-success','Setores atualizados');
            return back();
        }
        
        foreach($request->setores as $codset){
            $createSetor = new CreateSetorAction($codset, $fechadura);
            $createSetor->execute();
        }
        request()->session()->flash('alert-success', 'Setores atualizados com sucesso!');
        return back();
    }

    public function deleteUser(Fechadura $fechadura, User $user){
        $fechadura->usuarios()->detach($user->id);
        request()->session()->flash('alert-warning', "Usuário {$user->name} removido com sucesso!");
        return back();
    }

    //https://documenter.getpostman.com/view/7260734/S1LvX9b1?version=latest#76b4c5d7-e776-4569-bb19-341fdc1ccb7f
    //Sincroniza replicado com fechadura
    public function sincronizar(Request $request, Fechadura $fechadura)
    {
        $apiService = new ApiService($fechadura);
        $apiService->syncUsers($request, $fechadura);
        //verificar se realmente houve um retorno de sucesso
        request()->session()->flash('alert-success','Dados sincronizados!');
        return back();
    }
}
