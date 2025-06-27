<?php

namespace App\Services;

use Uspdev\Replicado\DB;
use Uspdev\Replicado\Uteis;

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

    public static function programasPosUnidade(){
        $codundclgi = getenv('REPLICADO_CODUNDCLG');
        //obtém programas
        $query = "SELECT DISTINCT (n.nomare), (a.codare) FROM AREA a inner join CURSO c
            ON a.codcur = c.codcur INNER JOIN NOMEAREA n on n.codare = a.codare INNER JOIN CREDAREA ca
            ON a.codare = ca.codare where c.codclg = convert(int, :codundclgi) and n.dtafimare = NULL";
        $param = [
            'codundclgi' => $codundclgi,
        ];
        $result = DB::fetchAll($query, $param);

        if(!empty($result)) {
            $result = Uteis::utf8_converter($result);
            $result = Uteis::trim_recursivo($result);
            return $result;
        }

        return false;
    }


    public static function retornaAlunosPos($codare){
        $query =  "SELECT DISTINCT l.codpes, l.nompes
        FROM LOCALIZAPESSOA l
        INNER JOIN AGPROGRAMA a on a.codpes = l.codpes
        WHERE l.sitatl = 'A' AND l.tipvin = 'ALUNOPOS'
        AND a.codare IN ($codare)";

        return collect(DB::fetchAll($query));

    }

}
