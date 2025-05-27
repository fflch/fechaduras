<?php

namespace App\Services;

use App\Models\User;
use Uspdev\Replicado\Pessoa;

class UsuarioService
{
    /**
     * Create a new class instance.
     */

    public static function verifyAndCreateUsers($codpes, $fechadura, $request){
        $user = User::firstWhere('codpes',$codpes);
        if(empty($user)){
            $dadosPessoa = Pessoa::dump($codpes, ['nompesttd']);
            if($dadosPessoa){
                $user = new User;
                $user->name = $dadosPessoa['nompesttd'];
                $user->nompesttd = $dadosPessoa['nompesttd'];
                $user->codpes = $codpes;
                $user->save();
            }else{
                request()->session()->flash('alert-danger', 'Algum número USP pode ter sido digitado incorretamente! Verifique novamente o campo.');
                return back()->withInput();
            }
        }
     
        $userRequest[trim($codpes)] = trim($codpes); //transforma o codpes em index

        if(!$fechadura->usuarios->contains($user)){
            $fechadura->usuarios()->attach($user->id);
        }else{
            request()->session()->flash('alert-danger', 'Algum usuário já está cadastrado! Verifique novamente o campo.');
            return redirect()->back()->withInput();
        }
        return $userRequest;
    }
}
