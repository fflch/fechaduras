<?php

namespace App\Actions;

use App\Models\Fechadura;
use App\Models\Acesso;
use App\Actions\SessionAction;
use Illuminate\Support\Facades\Http;

class LogsAction
{
    public static function update(Fechadura $fechadura)
    {
        $session = SessionAction::conexao($fechadura->ip, $fechadura->usuario, $fechadura->senha);
        
        if (!$session) {
            return false;
        }

        $route = 'http://' . $fechadura->ip . '/load_objects.fcgi?session=' . $session;
        $response = Http::post($route, [
            "object" => "access_logs",
            "limit" => 300,
            "order" => ["descending", "time"]
        ]);

        $logs = $response->json()['access_logs'] ?? [];
        
        $count = 0;
        foreach ($logs as $log) {
            $codpes = $log['user_id'] ?? 0;
            
            Acesso::updateOrCreate(
                ['log_id_externo' => $log['id']],
                [
                    'event' => $log['event'],
                    'fechadura_id' => $fechadura->id,
                    'codpes' => $codpes,
                    'datahora' => date('Y-m-d H:i:s', $log['time'])
                ]
            );
            $count++;
        }

        return $count;
    }
}