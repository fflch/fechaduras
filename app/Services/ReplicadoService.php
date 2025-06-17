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
        $query = "SELECT l.codpes, l.nompes
            FROM LOCALIZAPESSOA l
            WHERE l.codset IN ($codsets)
            AND l.sitatl = 'A'";

        return collect( DB::fetchAll($query) )
            ->keyBy('codpes');
    }
}
