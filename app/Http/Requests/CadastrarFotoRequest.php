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
                'required',
            ]
        ];
    }

    public function messages()
    {
        return [
            'foto.required' => 'Selecione uma foto para upload ou use a webcam',
        ];
    }
}
