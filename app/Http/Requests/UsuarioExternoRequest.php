<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioExternoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => 'required|string|max:255',
            'foto' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
            ],
            'vinculo' => 'required|string|max:100',
            'observacao' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'nome.required' => 'O nome é obrigatório',
            'nome.max' => 'O nome não pode ter mais de 255 caracteres',
            'vinculo.required' => 'O vínculo é obrigatório',
            'vinculo.max' => 'O vínculo não pode ter mais de 100 caracteres',
            'foto.image' => 'O arquivo deve ser uma imagem',
            'foto.mimes' => 'Formatos suportados: JPG, PNG',
            'foto.max' => 'A foto não pode ser maior que 2MB',
        ];
    }
}
