<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Fechadura;
use App\Models\UsuarioBloqueado;
use App\Services\ReplicadoService;
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
        $usuarioInfo = ReplicadoService::retornaCodpes($request->codpes);
        if (!$usuarioInfo) {
            return back()->with('alert-danger', 'Usuário não encontrado');
        }

        // Verifica se já está bloqueado
        $jaBloqueado = UsuarioBloqueado::where('codpes', $request->codpes)
            ->where('fechadura_id', $fechadura->id)
            ->exists();

        if ($jaBloqueado) {
            return back()->with('alert-warning', 'Usuário já está bloqueado');
        }

        // Cria bloqueio
        UsuarioBloqueado::create([
            'codpes' => $request->codpes,
            'fechadura_id' => $fechadura->id,
            'motivo' => $request->motivo,
            'user_id' => auth()->id()
        ]);

        return back()->with('alert-success', 'Usuário bloqueado com sucesso');
    }

    // Remove usuário da lista de bloqueados
    public function destroy(Fechadura $fechadura, UsuarioBloqueado $usuarioBloqueado)
    {
        Gate::authorize('adminFechadura', $fechadura);

        // Verifica se o bloqueio pertence à fechadura
        if ($usuarioBloqueado->fechadura_id != $fechadura->id) {
            return back()->with('alert-danger', 'Acesso não autorizado');
        }

        $usuarioBloqueado->delete();

        return back()->with('alert-success', 'Usuário desbloqueado com sucesso');
    }
}