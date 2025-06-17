<?php

namespace App\Services;

use App\Models\User;
use Uspdev\Replicado\Pessoa;

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
                }
            } else {
                $pessoa = Pessoa::dump($codpes, ['nompesttd']);
                if ($pessoa){
                    $user = new User;
                    $user->name = $pessoa['nompesttd'];
                    $user->codpes = $codpes;
                    $user->save();
                    $fechadura->usuarios()->attach($user->id);
                } else{
                    array_push($naoEncontrado, $codpes);
                }
            }
        }

        return $naoEncontrado;
    }
}
