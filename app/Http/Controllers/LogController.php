<?php

namespace App\Http\Controllers;

use App\Models\Fechadura;
use App\Models\Log;
use App\Services\ApiControlIdService;
use Illuminate\Support\Facades\Gate;


class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Método para logs
    public function logs(Fechadura $fechadura)
    {
        Gate::authorize('admin');

        // Busca os logs do banco local, ordenados pelos mais recentes
        $logs = Log::where('fechadura_id', $fechadura->id)
                    ->orderBy('datahora', 'desc')
                    ->paginate(20); // Paginação para muitos registros

        return view('fechaduras.logs', [
            'fechadura' => $fechadura,
            'logs' => $logs
        ]);
    }

    //Atualiza logs
    public function updateLogs(Fechadura $fechadura)
    {
        Gate::authorize('admin');

        $apiService = new ApiControlIdService($fechadura);
        $count = $apiService->updateLogs();

        if($count === false) {
            return back()->with('error', 'Erro ao atualizar os logs na base local!');
        }

        return back()->with('success', "{$count} logs atualizados");
    }
}
