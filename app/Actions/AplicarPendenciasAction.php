<?php
 
namespace App\Actions;
 
use App\Models\FechaduraAcesso;
use App\Services\ApiControlIdService;
use App\Services\FotoUpdateService;
 
class AplicarPendenciasAction
{
    // Envia para a fechadura as fotos e senhas solicitadas em "Meu perfil"
    public static function execute($fechadura, ApiControlIdService $api, $usuarios): void
    {
        $pendentes = FechaduraAcesso::where('fechadura_id', $fechadura->id)
            ->where(function ($q) {
                $q->where('foto_pendente', true)->orWhereNotNull('senha_pendente');
            })
            ->get();
 
        foreach ($pendentes as $acesso) {
            // Usuário não está mais nesta fechadura (removido/bloqueado): descarta o pedido
            if (!$usuarios->has($acesso->codpes)) {
                $acesso->update(['foto_pendente' => false, 'senha_pendente' => null]);
                continue;
            }
 
            // Um erro em um usuário não pode interromper a sincronização dos demais
            try {
                if ($acesso->foto_pendente) {
                    // Usa a foto local do usuário (users.foto), já salva em "Meu perfil"
                    $resp = FotoUpdateService::updateFoto($fechadura, $acesso->codpes);
                    if ($resp->successful() && $resp->json('success')) {
                        $acesso->foto_pendente = false;
                    }
                }
 
                if (!is_null($acesso->senha_pendente)) {
                    if ($api->cadastrarSenha($acesso->codpes, $acesso->senha_pendente)) {
                        $acesso->senha_pendente = null;
                    }
                }
 
                $acesso->save();
            } catch (\Throwable $e) {
                report($e); // continua pendente e tenta de novo na próxima sincronização
            }
        }
    }
}
