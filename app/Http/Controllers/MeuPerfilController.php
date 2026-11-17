<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Actions\ObterFotoLocalRemota;
use App\Http\Requests\CadastrarFotoRequest;
use App\Http\Requests\CadastrarSenhaRequest;
use App\Models\Fechadura;
use App\Models\FechaduraAcesso;
use App\Services\ApiControlIdService;

class MeuPerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
 
    // Página principal do perfil (lê só do banco local, sem tocar nas fechaduras)
    public function index()
    {
        $user = auth()->user();
 
        $fechadurasStatus = FechaduraAcesso::with('fechadura')
            ->where('codpes', $user->codpes)
            ->get()
            ->map(function ($acesso) {
                return (object) [
                    'fechadura'      => $acesso->fechadura,
                    'tem_foto'       => $acesso->tem_foto,
                    'tem_senha'      => $acesso->tem_senha,
                    'foto_pendente'  => $acesso->foto_pendente,
                    'senha_pendente' => !is_null($acesso->senha_pendente),
                ];
            })
            ->values();
 
        $foto = ObterFotoLocalRemota::handle(Auth::user());

        return view('perfil.index', [
            'user' => $user,
            'fechadurasStatus' => $fechadurasStatus,
            'foto' => $foto
        ]);
    }

    public function FotoGeral()
    {
        $foto = ObterFotoLocalRemota::handle(Auth::user());

        return view('perfil.editar_foto_geral', [
            'foto' => $foto
        ]);
    }

    // Salva a foto localmente e marca como pendente; o envio às fechaduras é feito na sincronização noturna
    public function updateFotoGeral(CadastrarFotoRequest $request)
    {
        $user = auth()->user();
 
        $acessos = FechaduraAcesso::with('fechadura')
            ->where('codpes', $user->codpes)
            ->get();
 
        if ($acessos->isEmpty()) {
            return back()->with('alert-info', 'O usuário não possui cadastro em nenhuma fechadura!');
        }
 
        $foto = (string) $request->safe()->string('foto');
        if (strpos($foto, 'base64,') !== false) {
            $foto = base64_decode(explode('base64,', $foto)[1]);
        }
 
        $fotoName = Str::uuid() . '.jpg';
        Storage::disk('fotos')->put($fotoName, $foto);
 
        // Teste de qualidade em UMA fechadura, só quando o usuário envia uma foto nova.
        // Se quiser zero requisição em tempo real, remova este bloco.
        try {
            $fechaduraTeste = $acessos->first()->fechadura;
            $teste = (new ApiControlIdService($fechaduraTeste))
                ->testarFoto($fechaduraTeste, Storage::disk('fotos')->path($fotoName));
 
            if (!$teste['success']) {
                Storage::disk('fotos')->delete($fotoName);
                return back()->with('alert-danger', $teste['message']);
            }
        } catch (\Exception) {
            // Fechadura indisponível: segue e deixa a sincronização noturna tentar
        }
 
        if (!is_null($user->foto)) {
            Storage::disk('fotos')->delete($user->foto);
        }
 
        $user->foto = $fotoName;
        $user->save();
 
        FechaduraAcesso::where('codpes', $user->codpes)->update(['foto_pendente' => true]);
 
        return redirect('/meu-perfil')
            ->with('alert-success', 'Foto salva! Ela será enviada às fechaduras durante a madrugada.');
    }

    // Formulário para trocar senha em uma fechadura específica
    public function formSenhaFechadura(Fechadura $fechadura)
    {
        $this->acessoDoUsuario($fechadura);

        return view('perfil.editar_senha_fechadura', ['fechadura' => $fechadura]);
    }

    // Guarda a senha (criptografada) como pendente; a sincronização noturna aplica na fechadura
    public function updateSenhaFechadura(CadastrarSenhaRequest $request, Fechadura $fechadura)
    {
        $acesso = $this->acessoDoUsuario($fechadura);

        $acesso->update(['senha_pendente' => $request->senha]);

        return redirect('/meu-perfil')
            ->with('alert-success', "Senha salva! Ela será aplicada na fechadura {$fechadura->local} durante a madrugada.");
    }
 
    // Verifica no banco local se o usuário logado está cadastrado na fechadura
    private function acessoDoUsuario(Fechadura $fechadura): FechaduraAcesso
    {
        $acesso = FechaduraAcesso::where('fechadura_id', $fechadura->id)
            ->where('codpes', auth()->user()->codpes)
            ->first();
 
        abort_if(!$acesso, 403, 'Você não está cadastrado nesta fechadura.');
 
        return $acesso;
    }
}
