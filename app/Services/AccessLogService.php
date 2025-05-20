<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AccessLogService
{
    public static function getAccessLogs($ip, $session, $limit = 100, $lastTimestamp = null)
    {
        $route = "http://{$ip}/load_objects.fcgi?session={$session}";
        
        $params = [
            "object" => "access_logs",
            "limit" => $limit,
            "order" => ["descending", "time"]
        ];
        
        if ($lastTimestamp) {
            $params["where"] = [
                "access_logs" => [
                    "time" => [">", $lastTimestamp]
                ]
            ];
        }
        
        $response = Http::post($route, $params);
        
        return $response->successful() ? $response->json()['access_logs'] ?? [] : [];
    }
    
    public static function getLatestLogTimestamp($fechaduraId)
    {
        return Acesso::where('fechadura_id', $fechaduraId)
            ->max('datahora');
    }
}