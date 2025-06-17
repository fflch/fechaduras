<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fechadura;
use App\Actions\SyncUsersAction;

class SincronizarFechaduras extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fechaduras:sync-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza usuários com as fechaduras';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechaduras = Fechadura::all();
        foreach($fechaduras as $fechadura){
            SyncUsersAction::execute($fechadura);
            $this->info('Fechadura ' . $fechadura->local . ' sincronizada com sucesso.');
        }
    }
}
