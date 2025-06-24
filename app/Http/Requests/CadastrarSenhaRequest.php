<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CadastrarSenhaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'senha' => 'required|digits:4|numeric'
        ];
    }

    public function messages()
    {
        return [
            'senha.required' => 'A senha é obrigatória',
            'senha.digits' => 'A senha deve ter exatamente 4 dígitos',
            'senha.numeric' => 'A senha deve conter apenas números'
        ];
    }
}