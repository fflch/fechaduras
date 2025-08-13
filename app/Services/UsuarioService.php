<?php

namespace App\Services;

use App\Models\User;
use Uspdev\Replicado\Pessoa;
use App\Services\ApiControlIdService;

class UsuarioService
{
    /**
     * Create a new class instance.
     */

    public static function verifyAndCreateUsers($codpes, $fechadura) :array {
        $numerosUsp = explode(',', $codpes);
        $numerosUsp = array_filter($numerosUsp, 'is_numeric');

        $naoEncontrado = [];
        foreach($numerosUsp as $codpes) {
            $user = User::firstWhere('codpes', $codpes);
            if ($user){
                if (! $fechadura->usuarios->contains($user->id)) {
                    $fechadura->usuarios()->attach($user->id);

                    self::cadastrarNaFechadura($fechadura, $codpes, $user->name);
                }
            } else {
                $pessoa = Pessoa::dump($codpes, ['nompesttd']);
                if ($pessoa){
                    $user = new User;
                    $user->name = $pessoa['nompesttd'];
                    $user->codpes = $codpes;
                    $user->email = $codpes . '@usp.br';
                    $user->save();
                    $fechadura->usuarios()->attach($user->id);

                    self::cadastrarNaFechadura($fechadura, $codpes, $pessoa['nompesttd']);
                } else{
                    array_push($naoEncontrado, $codpes);
                }
            }
        }

        return $naoEncontrado;
    }

    private static function cadastrarNaFechadura($fechadura, $codpes, $nome) 
    {
        $apiService = new ApiControlIdService($fechadura);
        $apiService->createUsers([$codpes => ['name' => $nome]]);
    }
}
