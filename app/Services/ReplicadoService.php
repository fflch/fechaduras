<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use Uspdev\Replicado\DB;

class ReplicadoService{
    
    public static function retornaSetores(){
        $query = "SELECT s.codset, s.nomabvset, s.nomset
        FROM SETOR s
        WHERE s.codund = 8
        ORDER BY s.nomabvset DESC";

        $setores = DB::fetchAll($query);

        return $setores;
    }

    public static function pessoa(array $codsets){
        $codset = implode(',', $codsets);
        
        $query = "SELECT p.codpes, a.nompesttd
            FROM LOCALIZAPESSOA p
            INNER JOIN PESSOA a
            ON p.codpes = a.codpes
            WHERE p.codset IN ($codset)
            AND p.sitatl = 'A'"; //pegar so quem esta ativo
            
        $pessoas = DB::fetchAll($query);

        $replicadoId = [];
        foreach($pessoas as $pessoa){
            $replicadoId[$pessoa['codpes']] = $pessoa;
        }
        return $replicadoId;
    }
}