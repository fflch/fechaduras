<?php

namespace App\Services;

use App\Models\User;
use Uspdev\Replicado\Pessoa;
use App\Models\Fechadura;
use App\Models\UsuarioExterno;
use Illuminate\Support\Facades\Storage;

class UsuarioService
{
    /**
     * Create a new class instance.
     */

    public static function verifyAndCreateUsers($codpes, $fechadura) :array
    {
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

    public static function delete(Fechadura $fechadura, $userId) :void
    {
        // Verificar se é usuário USP
        $userUSP = User::where('codpes', $userId)->first();
        if ($userUSP) {
            $fechadura->usuarios()->detach($userUSP->id);
        }

        // Verificar se é usuário externo (IDs acima de 10000)
        if ($userId > 10000) {
            $usuarioExternoId = $userId - 10000;
            $usuarioExterno = UsuarioExterno::find($usuarioExternoId);
            if ($usuarioExterno) {
                // Apaga a foto antes de deletar o registro
                if ($usuarioExterno->foto) {
                    Storage::disk('fotos')->delete($usuarioExterno->foto);
                }
                $usuarioExterno->delete();
            }
        }
    }
}
