<?php

namespace App\Services;

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

    public static function pessoa($codsets){
        $query = "SELECT p.codpes, a.nompesttd
            FROM LOCALIZAPESSOA p
            INNER JOIN PESSOA a
            ON p.codpes = a.codpes
            WHERE p.codset IN ($codsets)
            AND p.sitatl = 'A'";
            
        return collect( DB::fetchAll($query) )
        ->keyBy('codpes');
    }
}