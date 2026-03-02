<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Models\Fechadura;
use App\Models\User;
use App\Services\ApiControlIdService;
use App\Services\FotoUpdateService;

class MeuPerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Busca fechaduras onde o usuário tem acesso
        $fechaduras = $this->getFechadurasDoUsuario($user);

        return view('meu-perfil.index', [
            'fechaduras' => $fechaduras,
            'user' => $user
        ]);
    }

    public function editSenha(Fechadura $fechadura)
    {
        Gate::authorize('logado', $fechadura);

        return view('meu-perfil.cadastrar-senha', [
            'fechadura' => $fechadura,
            'userId' => auth()->user()->codpes,
            'user' => auth()->user()
        ]);
    }

    public function updateSenha(Request $request, Fechadura $fechadura)
    {
        Gate::authorize('logado', $fechadura);

        $request->validate([
            'senha' => 'required|digits:4'
        ]);

        $apiService = new ApiControlIdService($fechadura);
        $success = $apiService->cadastrarSenha(auth()->user()->codpes, $request->senha);

        if ($success) {
            return redirect()->to('/meu-perfil')
                ->with('alert-success', 'Senha atualizada com sucesso!');
        }

        return back()->with('alert-danger', 'Erro ao atualizar senha.');
    }

    private function getFechadurasDoUsuario($user)
    {
        // 1. Por setor
        $fechadurasPorSetor = collect();
        if ($user->setor) {
            $fechadurasPorSetor = $user->setor->fechaduras;
        }
        
        // 2. Por área 
        $fechadurasPorArea = collect();
        if ($user->area) {
            $fechadurasPorSetor = $user->area->fechaduras;
        }
        
        // 3. Por vínculo direto
        $fechadurasDiretas = $user->fechadurasComoUsuario()->get();
        
        // 4. Como admin
        $fechadurasComoAdmin = $user->fechadurasComoAdmin()->get();

        return $fechadurasPorSetor
            ->merge($fechadurasPorArea)
            ->merge($fechadurasDiretas)
            ->merge($fechadurasComoAdmin)
            ->unique('id');
    }

    private function salvarFotoLocal($user, $conteudoFoto, $userId)
    {
        // Remove foto antiga se existir
        if ($user->foto && Storage::disk('public')->exists($user->foto)) {
            Storage::disk('public')->delete($user->foto);
        }

        // Salva nova foto
        $fotoPath = 'fotos_usuario/' . $userId . '_' . time() . '.jpg';
        Storage::disk('public')->put($fotoPath, $conteudoFoto);
        
        $user->foto = $fotoPath;
        $user->foto_atualizada_em = now();
        $user->save();

        return $fotoPath; 
    }

    public function editFotoLocal()
    {
        return view('meu-perfil.atualizar-foto-local', [
            'user' => auth()->user()
        ]);
    }

    public function updateFotoLocal(Request $request)
    {
        $request->validate(['foto' => 'required']);

        $user = auth()->user();
        $userId = $user->codpes;

        // Processa a foto
        $foto = $request->foto;
        if (strpos($foto, 'base64,') !== false) {
            $foto = base64_decode(explode('base64,', $foto)[1]);
        }

        // Salva no banco local
        $this->salvarFotoLocal($user, $foto, $userId);

        // Salva em todas as fechaduras que o usuario tem acesso
        $this->atualizarFotoEmTodasFechaduras($user, $foto);

        return redirect()->to('/meu-perfil')
            ->with('alert-success', 'Foto atualizada com sucesso!');
    }

    // Atualiza a foto em todas as fechaduras que o usuário tem acesso
    private function atualizarFotoEmTodasFechaduras($user, $conteudoFoto)
    {
        // Busca todas as fechaduras que o usuário tem acesso
        $fechaduras = $this->getFechadurasDoUsuario($user);
        
        $sucessos = 0;
        $erros = 0;
        
        foreach ($fechaduras as $fechadura) {
            // Verifica se o usuário existe nesta fechadura
            $apiService = new ApiControlIdService($fechadura);
            $usuarios = $apiService->loadUsers();
            $usuarioFechadura = collect($usuarios)->firstWhere('id', (int)$user->codpes);
            
            // Se o usuário existe na fechadura, atualiza a foto
            if ($usuarioFechadura) {
                $tempFile = tempnam(sys_get_temp_dir(), 'bulk_') . '.jpg';
                file_put_contents($tempFile, $conteudoFoto);
                $file = new UploadedFile($tempFile, 'foto.jpg', 'image/jpeg', null, true);
                
                $result = $apiService->uploadFoto($user->codpes, $file);
                unlink($tempFile);
                
                if ($result['success']) {
                    $sucessos++;
                } else {
                    $erros++;
                }
            }
        }
    }
}