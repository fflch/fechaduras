<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\Fechadura;
use App\Models\User;
use App\Http\Requests\AdminRequest;
use App\Services\ReplicadoService;
use Uspdev\Replicado\Pessoa;

class AdminController extends Controller
{
    public function store(AdminRequest $request, Fechadura $fechadura)
    {
        Gate::authorize('admin');

        // Verifica se já é admin da fechadura
        $jaEAdmin = Admin::where('codpes', $request->codpes)
                        ->where('fechadura_id', $fechadura->id)
                        ->exists();

        if ($jaEAdmin) {
            return back()->with('alert-warning', 'Esta pessoa já é administradora da fechadura!');
        }

        // Verificar se o usuário é da usp
        $pessoa = ReplicadoService::dump($request->codpes);

        if ( !$pessoa ) {
           return back()->with('alert-danger', 'Número USP não encontrado!');
        }

        User::firstOrCreate([
                'codpes' => $request->codpes
            ],
            [
                'name' => $pessoa['nompesttd'] ?? 'Nome não encontrado'
            ]
        );

        $admin = new Admin();
        $admin->codpes = $request->codpes;
        $admin->fechadura_id = $fechadura->id;
        $admin->user_id = auth()->id();
        $admin->save();

        return back()->with('alert-success', 'Administrador cadastrado com sucesso!');
    }

    public function destroy(Admin $admin)
    {
        Gate::authorize('admin');
        $admin->delete();
        return back()->with('alert-success', 'Administrador removido com sucesso!');
    }
}
