<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CadastrarFotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'foto' => 'required|image|max:2048'
        ];
    }

    public function messages()
    {
        return [
            'foto.required' => 'A foto é obrigatória',
            'foto.image' => 'O arquivo deve ser uma imagem',
            'foto.max' => 'A foto não pode ser maior que 2MB'
        ];
    }
}