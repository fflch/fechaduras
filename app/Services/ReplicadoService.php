<?php

namespace App\Services;

use App\Models\Fechadura;
use Illuminate\Support\Facades\Http;
use App\Services\LockSessionService;
use Uspdev\Replicado\DB;

class ReplicadoService{
    public static function pessoa(){
        $query = "SELECT p.nompes, p.codpes, a.nompesttd
        FROM LOCALIZAPESSOA p
        INNER JOIN PESSOA a
        ON a.codpes = p.codpes
        WHERE p.codset = 606
        AND p.sitatl = 'A'"; //pegar so quem esta ativo
    
        $pessoas = DB::fetchAll($query);

        $replicadoId = [];
        foreach($pessoas as $pessoa){
            $replicadoId[$pessoa['codpes']] = $pessoa;
        }

        return $replicadoId;
    }
}