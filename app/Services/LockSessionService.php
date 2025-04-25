<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LockSessionService
{
    public static function conexao($ip){
        if (session()->has('lock:session')) {
           return self::validade($ip) ?  session()->get('lock:session') : self::login($ip);
        }   
 
        return self::login($ip);
    }

    private static function login($ip){
        $response = Http::post('http://' . $ip . '/login.fcgi', [
            'login' => env('FECHADURAS_LOGIN'),
            'password' => env('FECHADURAS_PASSWORD'),
        ]);
        session()->put('lock:session', $response->json('session'));

        return $response->json('session');
    }

    private static function validade($ip){
        $session = session()->get('lock:session');
        $route = 'http://' . $ip . '/session_is_valid.fcgi?session=' . $session;
        $response = Http::post($route, ['session' => $session]);

        return $response->json('session_is_valid');
            
    }

}

