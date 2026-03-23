<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UsuarioExterno;
use Illuminate\Support\Facades\Storage;

class MigrarFotosExternos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrar:fotos-externos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move as fotos dos usuários externos para o disco privado fotos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $usuarios = UsuarioExterno::whereNotNull('foto')->get();

        foreach ($usuarios as $usuario) {
            $caminhoAntigo = $usuario->foto; // ex: "usuarios_externos/xxxx.png"
            
            // Verifica se o arquivo existe no disco público
            if (!Storage::disk('public')->exists($caminhoAntigo)) {
                $this->warn("Arquivo não encontrado: {$caminhoAntigo}");
                continue;
            }

            // Lê o conteúdo
            $conteudo = Storage::disk('public')->get($caminhoAntigo);
            
            // Gera novo nome (apenas o nome do arquivo, sem pastas)
            $nomeArquivo = basename($caminhoAntigo);
            
            // Salva no novo disco
            Storage::disk('fotos')->put($nomeArquivo, $conteudo);
            
            // Atualiza o banco de dados com o novo caminho
            $usuario->foto = $nomeArquivo;
            $usuario->save();

            // Remove o arquivo antigo
            Storage::disk('public')->delete($caminhoAntigo);

            $this->info("Migrado: {$usuario->nome} -> {$nomeArquivo}");
        }

        $this->info('Migração concluída!');
    }
}
