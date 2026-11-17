<?php
 
namespace App\Actions;
 
use App\Models\FechaduraAcesso;
use App\Services\ApiControlIdService;
 
class AtualizarEspelhoFechaduraAction
{
    // Grava no banco local quem está em cada fechadura e se tem foto/senha
    public static function execute($fechadura, ApiControlIdService $api): void
    {
        $naFechadura = collect($api->loadUsers());
 
        // Se a fechadura não respondeu, não apaga o espelho
        if ($naFechadura->isEmpty()) {
            return;
        }
 
        $linhas = $naFechadura->map(function ($u) use ($fechadura) {
            return [
                'fechadura_id' => $fechadura->id,
                'codpes'       => (int) $u['id'],
                'tem_foto'     => ($u['image_timestamp'] ?? 0) > 0,
                'tem_senha'    => !empty($u['password']),
            ];
        })->all();
 
        foreach (array_chunk($linhas, 500) as $lote) {
            FechaduraAcesso::upsert($lote, ['fechadura_id', 'codpes'], ['tem_foto', 'tem_senha']);
        }
 
        // Remove quem já não está na fechadura
        FechaduraAcesso::where('fechadura_id', $fechadura->id)
            ->whereNotIn('codpes', $naFechadura->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->delete();
    }
}