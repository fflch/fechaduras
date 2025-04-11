<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FechaduraController extends Controller
{
    # https://www.controlid.com.br/docs/access-api-pt/primeiros-passos/realizar-login/

    public function index(){

        // 1 - requisição para login: rota: login.fcgi
        $ip = '10.84.0.62';
        $response = Http::post('http://' . $ip . '/login.fcgi', [
            'login' => env('FECHADURAS_LOGIN'),
            'password' => env('FECHADURAS_PASSWORD'),
        ]);
        $response = $response->json();
        $session = $response['session'];

        // 2 - listas usuários /load_objects.fcgi
        $route = 'http://' . $ip . '/load_objects.fcgi?session=' . $session;

        $response = Http::post($route, [
            "object" => "users",
        ]);
        
        $usuarios = $response->json()['users'];

        // 3 - passar os dados para a view
        return view('fechadura.index', ['usuarios' => $usuarios]);
    }
}
