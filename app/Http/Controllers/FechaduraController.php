<?php

namespace App\Http\Controllers;

use App\Actions\CreateSetorAction;
use App\Actions\SyncUsersAction;
use App\Actions\CreateAreasAction;
use App\Services\LockSessionService;
use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\FechaduraRequest;
use App\Models\User;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use App\Services\ApiControlIdService;
use App\Http\Requests\CadastrarFotoRequest;
use App\Http\Requests\CadastrarSenhaRequest;
use App\Services\ReplicadoService;
use Illuminate\Support\Facades\Gate;

class FechaduraController extends Controller
{
    # https://www.controlid.com.br/docs/access-api-pt/primeiros-passos/realizar-login/

    public function __construct()
    {
        $this->middleware('auth');
    }

    # Métodos CRUD
    // Mostra fechaduras cadastradas
    public function index() {
        Gate::authorize('admin');

        $fechaduras = Fechadura::all();
        return view('fechaduras.index', [
            'fechaduras' => $fechaduras
        ]);
    }

    // Mostra formulário de criação
    public function create() {
        Gate::authorize('admin');

        return view('fechaduras.create');
    }

    // Cadastra novas fechaduras
    public function store(FechaduraRequest $request) {
        Gate::authorize('admin');

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
        Gate::authorize('admin');

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
            'usuarios' => $usuarios,
            'programas' => ReplicadoService::programasPosUnidade()
        ]);
    }

    // Mostra formulário de edição
    public function edit(Fechadura $fechadura) {
        Gate::authorize('admin');

        return view('fechaduras.edit', [
            'fechadura' => $fechadura
        ]);
    }

    // Atualiza fechadura
    public function update(FechaduraRequest $request, Fechadura $fechadura) {
        Gate::authorize('admin');

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
        Gate::authorize('admin');

        $fechadura->delete();
        return redirect('/fechaduras');
    }

    public function createFechaduraUser(Fechadura $fechadura, Request $request){
        Gate::authorize('admin');

        if(!$request->codpes){
            request()->session()->flash('alert-danger', 'Informe número USP!');
            return back();
        }
        $codpes = UsuarioService::verifyAndCreateUsers($request->codpes, $fechadura);
        if (count($codpes) > 0) {
            $request->session()->flash('alert-danger', "Número(s) USP " . implode(', ', $codpes) . " não cadastrado(s).");
        }
        else {
            $request->session()->flash('alert-success', "Usuário(s) cadastrado(s) com sucesso!");
        }

        return back()->withInput();
    }

    public function createFechaduraSetor(Fechadura $fechadura, Request $request){
        Gate::authorize('admin');

        CreateSetorAction::execute($request->setores, $fechadura);
        request()->session()->flash('alert-success', 'Setores atualizados com sucesso!');
        return back();
    }

    public function createFechaduraPos(Request $request, Fechadura $fechadura){
        Gate::authorize('admin');

        CreateAreasAction::execute($request->areas, $fechadura);
        $request->session()->flash('alert-success', "Setor(es) inserido(s)");
        return back();
    }

    public function deleteUser(Fechadura $fechadura, User $user){
        Gate::authorize('admin');

        $fechadura->usuarios()->detach($user->id);
        request()->session()->flash('alert-warning', "{$user->name} removido");
        return back();
    }

    //https://documenter.getpostman.com/view/7260734/S1LvX9b1?version=latest#76b4c5d7-e776-4569-bb19-341fdc1ccb7f
    //Sincroniza replicado com fechadura
    public function sincronizar(Fechadura $fechadura)
    {
        Gate::authorize('admin');

        SyncUsersAction::execute($fechadura);
        request()->session()->flash('alert-success','Dados sincronizados!');
        return back();
    }

    //mostra view para cadastrar foto na fechadura
    public function showCadastrarFoto(Fechadura $fechadura, $userId)
    {
        Gate::authorize('admin');

        return view('fechaduras.cadastrar_foto', [
            'fechadura' => $fechadura,
            'userId' => $userId
        ]);
    }

    //mostra view para cadastrar senha na fechadura
    public function showCadastrarSenha(Fechadura $fechadura, $userId)
    {
        Gate::authorize('admin');

        return view('fechaduras.cadastrar_senha', [
            'fechadura' => $fechadura,
            'userId' => $userId
        ]);
    }

    public function cadastrarFoto(CadastrarFotoRequest $request, Fechadura $fechadura, $userId)
    {
        Gate::authorize('admin');

        $apiService = new ApiControlIdService($fechadura);
        $apiService->uploadFoto($userId, $request->file('foto'));

        return redirect("/fechaduras/{$fechadura->id}");
    }

    public function cadastrarSenha(CadastrarSenhaRequest $request, Fechadura $fechadura, $userId)
    {
        Gate::authorize('admin');

        $apiService = new ApiControlIdService($fechadura);
        $apiService->cadastrarSenha($userId, $request->input('senha'));

        return redirect("/fechaduras/{$fechadura->id}");
    }
}
