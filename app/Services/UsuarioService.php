<?php

namespace App\Services;

use App\Models\User;
use Uspdev\Replicado\Pessoa;

class UsuarioService
{
    /**
     * Create a new class instance.
     */

    public static function verifyAndCreateUsers($usuarioValido, $fechadura){
        $user = User::firstWhere('codpes',$usuarioValido);
        if(empty($user)){
            $dadosPessoa = Pessoa::dump($usuarioValido);
            $user = new User; //instanciando o model pq o metodo findOrCreate do replicado precisa da table "permissions"
            $user->name = $dadosPessoa['nompesttd'] ?? $dadosPessoa['nompes'];
            $user->codpes = $usuarioValido;
            $user->save();
        }
     
        $userRequest[trim($usuarioValido)] = trim($usuarioValido); //transforma o codpes em index
        
        $codpesValido = Pessoa::dump($usuarioValido);
        if(!$codpesValido){
            request()->session()->flash('alert-danger', 'Algum número USP pode ter sido digitado incorretamente! Verifique novamente.');
            return back();
        }
        
        if(!$fechadura->usuarios->contains($user)){
            $fechadura->usuarios()->attach($user->id);
        }else{
            request()->session()->flash('alert-danger', 'Usuário já está cadastrado!');
            return redirect()->back();
        }
        return $userRequest;
    }
}
