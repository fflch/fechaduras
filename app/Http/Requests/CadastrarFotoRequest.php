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
            'foto' => [
                'required_without:foto_base64', 
                'image',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ],
            'foto_base64' => [
                'required_without:foto', 
                'string'
            ]
        ];
    }

    public function messages()
    {
        return [
            'foto.required_without' => 'Selecione uma foto para upload ou use a webcam',
            'foto_base64.required_without' => 'Tire uma foto com a webcam ou selecione um arquivo',
            'foto.image' => 'O arquivo deve ser uma imagem',
            'foto.mimes' => 'Formato não suportado. Use apenas JPG ou PNG.',
            'foto.max' => 'A foto não pode ser maior que 2MB',
            'foto.dimensions' => 'A foto deve ser um retrato com rosto visível (entre 100x100 e 2000x2000 pixels)'
        ];
    }
}