<?php

namespace App\Http\Controllers;

use App\Models\Fechadura;

class IndexController extends Controller
{
    public function __construct()
    {
        // A página mostra e-mails dos administradores: só para usuários logados
        $this->middleware('auth');
    }
 
    public function index()
    {
        return view('index', [
            'fechaduras' => Fechadura::with('admins.user')->orderBy('local')->get()
        ]);
    }
}
