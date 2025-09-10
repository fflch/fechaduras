<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Fechadura; 
use App\Services\FotoUpdateService; 

class ForcarAtualizacaoFoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'foto:forcar {fechadura_id} {codpes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Força atualização de foto específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fechadura = Fechadura::find($this->argument('fechadura_id'));
        $codpes = $this->argument('codpes');
        
        $result = FotoUpdateService::updateFoto($fechadura, $codpes, true);
        $this->info(" Foto forçada para $codpes");
    }
}
