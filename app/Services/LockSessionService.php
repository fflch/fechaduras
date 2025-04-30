<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LockSessionService
{
    // Obtém ou cria uma sessão ativa com a fechadura
    public static function conexao($ip, $usuario, $senha){
            if (session()->has('lock:session')) {
                return self::validade($ip, $usuario, $senha) 
                    ? session()->get('lock:session') : self::login($ip, $usuario, $senha);
            }

            return self::login($ip, $usuario, $senha);
    }

    // Realiza o login na API da fechadura
    private static function login($ip, $usuario, $senha){
        $response = Http::post('http://' . $ip . '/login.fcgi', [
            'login' => $usuario,
            'password' => $senha
        ]);
            session()->put('lock:session', $response->json('session'));
            
            return $response->json('session');
    }

    // Verifica se a sessão atual ainda é válida
    private static function validade($ip, $usuario, $senha){
        $session = session()->get('lock:session');
        $route = 'http://' . $ip . '/session_is_valid.fcgi?session=' . $session;
        $response = Http::post($route, ['session' => $session]);

        return $response->json('session_is_valid');
    }
}