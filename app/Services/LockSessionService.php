<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Exceptions\ConnectionFailureException;
use Illuminate\Support\Str;

class LockSessionService
{
    private const LOCK_SESSION = 'lock:session:';

    // Obtém ou cria uma sessão ativa com a fechadura
    public static function conexao($ip, $usuario, $senha){
        $lock_session = self::getLockSession($ip);
        try {
            if (session()->has($lock_session)) {
                $session = session()->get($lock_session);
                return self::validade($ip, $session)
                    ? $session : self::login($ip, $usuario, $senha);
            }
            return self::login($ip, $usuario, $senha);
        } catch (\Exception $e) {
            throw new ConnectionFailureException($ip);
        }
    }

    // Realiza o login na API da fechadura
    private static function login($ip, $usuario, $senha){
        $response = Http::post('http://' . $ip . '/login.fcgi', [
            'login' => $usuario,
            'password' => $senha
        ]);
        session()->put(self::getLockSession($ip), $response->json('session'));

        return $response->json('session');
    }

    // Verifica se a sessão atual ainda é válida
    private static function validade($ip, $session){
        $route = 'http://' . $ip . '/session_is_valid.fcgi?session=' . $session;
        $response = Http::post($route, ['session' => $session]);

        return $response->json('session_is_valid');
    }

    private static function getLockSession($ip) {
        return self::LOCK_SESSION . STR::replace('.', '_', $ip);
    }
}
