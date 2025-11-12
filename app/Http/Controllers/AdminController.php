<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\Fechadura;
use App\Http\Requests\AdminRequest;
use App\Services\ReplicadoService;

class AdminController extends Controller
{
    public function store(AdminRequest $request, Fechadura $fechadura) 
    {
        Gate::authorize('admin');

        // Verificar se o usuário é da usp
        $pessoa = ReplicadoService::retornaCodpes($request->codpes);

        if (count($pessoa) == 0 ) {
           return back()->with('alert-danger', 'Número USP não encontrado!');
        }

        // Verifica se já é admin da fechadura
        $jaEAdmin = Admin::where('codpes', $request->codpes)
                        ->where('fechadura_id', $fechadura->id)
                        ->exists();

        if ($jaEAdmin) {
            return back()->with('alert-warning', 'Esta pessoa já é administradora da fechadura!');
        }

        $admin = new Admin();
        $admin->codpes = $request->codpes;
        $admin->fechadura_id = $fechadura->id;
        $admin->user_id = auth()->id();
        $admin->save();

        return back()->with('alert-success', 'Administrador cadastrado com sucesso!');
    }

    public function destroy(Fechadura $fechadura, Admin $admin)
    {
        Gate::authorize('admin');
        $admin->delete();
        return back()->with('alert-success', 'Administrador removido com sucesso!');
    }
}