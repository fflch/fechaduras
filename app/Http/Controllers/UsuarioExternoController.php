<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioExternoRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\UsuarioExterno;
use App\Models\Fechadura;
use App\Services\ApiControlIdService;

class UsuarioExternoController extends Controller
{
    public function create(UsuarioExternoRequest $request, Fechadura $fechadura)
    {
        Gate::authorize('adminFechadura', $fechadura);

        // Testa a foto antes de criar o usuário
        if ($request->hasFile('foto')) {
            $apiService = new ApiControlIdService($fechadura);
            $resultado = $apiService->testarFoto($fechadura, $request->file('foto'));

            if (!$resultado['success']) {
                return back()
                    ->with('alert-danger', 'Foto não aprovada: ' . $resultado['message'])
                    ->withInput();
            }

            $fotoPath = $request->file('foto')->store('usuarios_externos', 'public');
        }

        // Cria o usuário externo
        $usuarioExterno = new UsuarioExterno();
        $usuarioExterno->nome = $request->nome;
        $usuarioExterno->fechadura_id = $fechadura->id;
        $usuarioExterno->user_id = auth()->id();
        $usuarioExterno->vinculo = $request->vinculo;
        $usuarioExterno->observacao = $request->observacao;
        // Upload da foto (já testada e aprovada)
        $usuarioExterno->foto = $fotoPath ?? null;

        $usuarioExterno->save();

        return back()->with('alert-success', 'Usuário externo cadastrado com sucesso!');
    }

    // Remover usuário externo
    public function delete(Fechadura $fechadura, UsuarioExterno $usuarioExterno)
    {
        Gate::authorize('adminFechadura', $fechadura);
        $usuarioExterno->delete();
        return back()->with('alert-success', 'Usuário externo removido do sistema!');
    }
}
