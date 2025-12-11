<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioBloqueadoRequest extends FormRequest
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
            'codpes' => 'required|integer',
            'motivo' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'codpes.required' => 'O número USP é obrigatório',
            'codpes.integer' => 'O número USP deve ser um número',
            'motivo.max' => 'O motivo não pode ter mais de 500 caracteres'
        ];
    }
}
