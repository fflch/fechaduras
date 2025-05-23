<?php

namespace App\Actions;

use App\Models\Setor;
use Uspdev\Replicado\Estrutura;

class CreateSetorAction
{
    /**
     * Create a new class instance.
     */
    
    public $codset;
    public $fechadura;

    public function __construct($codset, $fechadura)
    {
        $this->codset = $codset;
        $this->fechadura = $fechadura;
    }

    public function execute()
    {
        $setor = Setor::firstWhere('codset', $this->codset);
        $estrutura = Estrutura::dump($this->codset);

        if(!$setor){
            $setor = new Setor();
            $setor->codset = $this->codset;
            $setor->nome = $estrutura['nomset'];
            $setor->fechadura_id = $this->fechadura->id;
            $setor->save();
        }
        $this->fechadura->setores()->attach($setor->id);

        return $setor;
    }

}
