<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\Fechadura;
use App\Models\UsuarioBloqueado;
use App\Models\User;
use App\Http\Requests\UsuarioBloqueadoRequest;

class UsuarioBloqueadoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Adiciona usuário à lista de bloqueados
    public function store(UsuarioBloqueadoRequest $request, Fechadura $fechadura)
    {
        Gate::authorize('adminFechadura', $fechadura);

        // Verifica se usuário existe
        if ( User::where('codpes', $request->codpes)->doesntExist() ) {
            return back()->with('alert-danger', 'Usuário não encontrado.');
        }

        // Cria bloqueio
        UsuarioBloqueado::firstOrCreate(
            [
                'codpes' => $request->codpes,
                'fechadura_id' => $fechadura->id
            ],
                $request->validated() + [
                'fechadura_id' => $fechadura->id,
                'user_id' => auth()->id()
            ]
        );

        return back()->with('alert-success', 'Usuário bloqueado com sucesso.');
    }

    // Remove usuário da lista de bloqueados
    public function destroy(Fechadura $fechadura, UsuarioBloqueado $usuarioBloqueado)
    {
        Gate::authorize('adminFechadura', $fechadura);

        $usuarioBloqueado->delete();

        return back()->with('alert-success', 'Usuário desbloqueado com sucesso.');
    }
}
